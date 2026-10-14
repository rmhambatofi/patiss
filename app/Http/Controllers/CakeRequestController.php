<?php

namespace App\Http\Controllers;

use App\Actions\StoreCakeRequest as StoreCakeRequestAction;
use App\Http\Requests\StoreCakeRequest;
use App\Models\CakeType;
use App\Models\DeliveryTier;
use App\Settings\ShopSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CakeRequestController extends Controller
{
    public function __construct(private ShopSettings $settings) {}

    public function createForType(CakeType $cakeType): View
    {
        abort_unless($cakeType->isVisible(), 404);

        return view('demande.type', $this->formData() + [
            'type' => $cakeType,
            'leadDays' => $cakeType->min_lead_days,
        ]);
    }

    /** The request 404s on a hidden type before validating (see StoreCakeRequest). */
    public function storeForType(CakeType $cakeType, StoreCakeRequest $request, StoreCakeRequestAction $action): RedirectResponse
    {
        return $this->store($request, $action, $cakeType);
    }

    public function createFree(): View
    {
        return view('demande.libre', $this->formData() + [
            'leadDays' => $this->settings->free_request_lead_days,
        ]);
    }

    public function storeFree(StoreCakeRequest $request, StoreCakeRequestAction $action): RedirectResponse
    {
        return $this->store($request, $action, null);
    }

    public function thanks(): View
    {
        return view('demande.merci', ['reference' => session('reference')]);
    }

    private function store(StoreCakeRequest $request, StoreCakeRequestAction $action, ?CakeType $type): RedirectResponse
    {
        try {
            $cakeRequest = $action->handle(
                $request->validated(),
                $type,
                $request->commune(),
                $request->file('photo_a_reproduire'),
                array_values($request->file('inspirations', [])),
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'photos' => 'Votre demande n’a pas pu être enregistrée (problème lors de l’enregistrement des photos). Merci de réessayer.',
            ]);
        }

        return redirect()->route('request.thanks')->with('reference', $cakeRequest->reference);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'depositRate' => $this->settings->deposit_rate_percent,
            'cancellationTerms' => $this->settings->cancellation_terms,
            'hasDeliveryTiers' => DeliveryTier::exists(),
        ];
    }
}
