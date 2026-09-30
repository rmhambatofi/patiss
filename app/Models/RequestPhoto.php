<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A photo sent with a request, stored on the private `local` disk under
 * requests/{reference} (KTD7). Never served publicly.
 *
 * @property string $kind 'a_reproduire' or 'inspiration'
 * @property string $path
 */
class RequestPhoto extends Model
{
    public const KIND_TO_REPRODUCE = 'a_reproduire';

    public const KIND_INSPIRATION = 'inspiration';

    /** The private disk request photos live on. */
    public const DISK = 'local';

    protected $fillable = ['kind', 'path', 'original_name'];

    /**
     * @return BelongsTo<CakeRequest, $this>
     */
    public function cakeRequest(): BelongsTo
    {
        return $this->belongsTo(CakeRequest::class);
    }

    /** Where the photos of one request are stored on DISK. */
    public static function directoryFor(string $reference): string
    {
        return 'requests/'.$reference;
    }
}
