<?php

namespace App\Models;

use App\Enums\CreationCategory;
use Database\Factories\CreationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Une pièce de la galerie de la vitrine.
 */
class Creation extends Model
{
    /** @use HasFactory<CreationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'image_path',
        'image_url',
        'sort_order',
        'is_featured',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'category' => CreationCategory::class,
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Pièces montrées au public : publiées et illustrées, dans l'ordre d'affichage.
     *
     * @param  Builder<Creation>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNotNull('image_path')->orWhereNotNull('image_url'))
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Absolute URL of the photo: the uploaded file on the public disk wins over the external URL.
     */
    public function imageUrl(): ?string
    {
        if ($this->image_path) {
            return url(Storage::disk('public')->url($this->image_path));
        }

        return $this->image_url;
    }
}
