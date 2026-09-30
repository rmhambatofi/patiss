<?php

namespace App\Models;

use App\Enums\Allergen;
use Database\Factories\CakeTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CakeType extends Model
{
    /** @use HasFactory<CakeTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'from_price_cents',
        'portion_formats',
        'customizable_options',
        'min_lead_days',
        'base_allergens',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'from_price_cents' => 'integer',
            'portion_formats' => 'array',
            'customizable_options' => 'array',
            'min_lead_days' => 'integer',
            'base_allergens' => AsEnumCollection::of(Allergen::class),
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CakeTypePhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CakeTypePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Types shown to the public: published and with at least one photo.
     *
     * @param  Builder<CakeType>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereHas('photos')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function isVisible(): bool
    {
        return $this->is_published && $this->photos()->exists();
    }

    public function fromPriceLabel(): string
    {
        return 'à partir de '.self::formatEuros($this->from_price_cents);
    }

    /**
     * "6, 8 ou 12 parts".
     */
    public function portionFormatsLabel(): string
    {
        $formats = array_map('intval', $this->portion_formats ?? []);

        if ($formats === []) {
            return '';
        }

        $last = array_pop($formats);

        return ($formats === [] ? $last : implode(', ', $formats).' ou '.$last).' parts';
    }

    public static function formatEuros(int $cents): string
    {
        $decimals = $cents % 100 === 0 ? 0 : 2;

        return number_format($cents / 100, $decimals, ',', ' ').' €';
    }
}
