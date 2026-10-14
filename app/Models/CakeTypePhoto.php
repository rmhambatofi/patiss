<?php

namespace App\Models;

use Database\Factories\CakeTypePhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CakeTypePhoto extends Model
{
    /** @use HasFactory<CakeTypePhotoFactory> */
    use HasFactory;

    protected $fillable = ['cake_type_id', 'path', 'alt', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return BelongsTo<CakeType, $this>
     */
    public function cakeType(): BelongsTo
    {
        return $this->belongsTo(CakeType::class);
    }

    /**
     * Absolute URL on the current host (APP_URL outside a request, e.g. in a queued e-mail).
     * The public disk URL itself is relative, see config/filesystems.php.
     */
    public function url(): string
    {
        return url(Storage::disk('public')->url($this->path));
    }
}
