<?php

namespace App\Actions;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Mail\NewRequestForArtisan;
use App\Mail\RequestReceived;
use App\Models\CakeRequest;
use App\Models\CakeType;
use App\Models\Commune;
use App\Models\RequestPhoto;
use App\Services\DeliveryZoneResolver;
use App\Services\ImageSanitizer;
use App\Settings\ShopSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Exceptions\NotSupportedException;
use RuntimeException;
use Throwable;

/**
 * Stores a validated request, its photos and queues both e-mails in one
 * transaction (U6, KTD7, KTD8). If anything fails, nothing is kept.
 */
class StoreCakeRequest
{
    private const DISK = 'local';

    public function __construct(
        private ImageSanitizer $sanitizer,
        private DeliveryZoneResolver $resolver,
        private ShopSettings $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Validated form data.
     * @param  list<UploadedFile>  $inspirations
     */
    public function handle(
        array $data,
        ?CakeType $type,
        ?Commune $commune,
        ?UploadedFile $toReproduce = null,
        array $inspirations = [],
    ): CakeRequest {
        $reference = CakeRequest::generateReference();
        $directory = 'requests/'.$reference;
        $fulfilment = Fulfilment::from($data['fulfilment']);
        $eventDate = Carbon::createFromFormat('Y-m-d', $data['event_date'])->startOfDay();
        $leadDays = $type ? $type->min_lead_days : $this->settings->free_request_lead_days;
        $delivery = $fulfilment === Fulfilment::Delivery;

        try {
            return DB::transaction(function () use ($data, $type, $commune, $toReproduce, $inspirations, $reference, $directory, $fulfilment, $eventDate, $leadDays, $delivery) {
                $request = CakeRequest::create([
                    'reference' => $reference,
                    'kind' => $type ? CakeRequest::KIND_TYPE : CakeRequest::KIND_LIBRE,
                    'cake_type_id' => $type?->id,
                    'portions' => (int) $data['portions'],
                    'customization' => $type ? ($data['customization'] ?? null) : $data['description'],
                    'event_date' => $eventDate,
                    'fulfilment' => $fulfilment,
                    'commune_id' => $delivery ? $commune?->id : null,
                    'delivery_fee_cents' => $delivery ? $this->resolver->resolve($commune)?->price_cents : null,
                    'allergies' => filled($data['allergies'] ?? null) ? $data['allergies'] : null,
                    'allergy_consent_at' => filled($data['allergies'] ?? null) ? now() : null,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'source' => $data['source'],
                    'out_of_lead_time' => $eventDate->lt(today()->addDays($leadDays)),
                    'status' => RequestStatus::Nouvelle,
                    'last_activity_at' => now(),
                ]);

                $files = array_filter([
                    [RequestPhoto::KIND_TO_REPRODUCE, $toReproduce],
                    ...array_map(fn ($file) => [RequestPhoto::KIND_INSPIRATION, $file], $inspirations),
                ], fn ($entry) => $entry[1] instanceof UploadedFile);

                foreach ($files as [$kind, $file]) {
                    $request->photos()->create([
                        'kind' => $kind,
                        'path' => $this->storePhoto($file, $directory),
                        'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                    ]);
                }

                Mail::to($request->email)->queue(new RequestReceived($request));

                if (filled($this->settings->notification_email)) {
                    Mail::to($this->settings->notification_email)->queue(new NewRequestForArtisan($request));
                }

                return $request;
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->deleteDirectory($directory);

            throw $e;
        }
    }

    /**
     * Re-encodes the photo when the server can decode it; otherwise (e.g. HEIC
     * without support) keeps the original file as-is, still private (KTD7).
     */
    private function storePhoto(UploadedFile $file, string $directory): string
    {
        $disk = Storage::disk(self::DISK);

        try {
            $path = $this->sanitizer->store($file, self::DISK, $directory);
        } catch (DecoderException|NotSupportedException) {
            $extension = strtolower($file->guessExtension() ?? $file->getClientOriginalExtension() ?: 'bin');
            $path = $disk->putFileAs($directory, $file, Str::random(40).'.'.$extension) ?: null;
        }

        if (! $path || ! $disk->exists($path)) {
            throw new RuntimeException("La photo {$file->getClientOriginalName()} n'a pas pu être enregistrée.");
        }

        return $path;
    }
}
