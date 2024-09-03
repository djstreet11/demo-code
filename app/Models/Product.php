<?php

namespace App\Models;

use App\Trait\HasReviewRating;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int    $id
 * @property string $title
 * @property int    $category_id
 * @property string $description
 * @property float  $price
 * @property string $type
 * @property bool   $show_help
 * @property bool   $block_price
 * @property int    $main_category
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable;
    use HasReviewRating;

    protected $fillable = [
        'title',
        'sku',
        'category_id',
        'description',
        'price',
        'price_old',
        'featured',
        'weight',
        'weight_type',
        'type',
        'show_help',
        'block_price',
		'in_stock',
        'main_category',
        'active',
        'sale',
        'seo_title',
        'seo_description',
        'seo_title_template',
        'seo_description_template',
    ];

    protected $appends = ['discount', 'avgRating'];

    protected $casts = [
        'featured'    => 'boolean',
        'show_help'   => 'boolean',
        'block_price' => 'boolean',
        'active'      => 'boolean',
        'sale'        => 'boolean',
		'in_stock'    => 'int',
    ];

    protected $with = ['categories', 'tags', 'media', 'reviews'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->performOnCollections('cover')
            ->width(191)->height(179)->keepOriginalImageFormat();
        //         ->fit(Manipulations::FIT_MAX, 191, 179)->keepOriginalImageFormat()->nonQueued();

        $this->addMediaConversion('small')->performOnCollections('cover')
            ->width(60)->height(60)->keepOriginalImageFormat();

        $this->addMediaConversion('slider')->performOnCollections('slider', 'cover')
            ->width(565)->height(478)->keepOriginalImageFormat();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class)->sorted();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Attribute::class);
    }

}
