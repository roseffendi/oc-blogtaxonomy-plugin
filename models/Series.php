<?php

namespace GinoPane\BlogTaxonomy\Models;

use System\Models\File;
use RainLab\Blog\Models\Post;
use GinoPane\BlogTaxonomy\Plugin;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;

/**
 * Class Series
 *
 * @property string title
 * @property string slug
 *
 * @package GinoPane\BlogTaxonomy\Models
 */
class Series extends ModelAbstract
{
    use Sluggable;
    use Validation;

    const TABLE_NAME = 'ginopane_blogtaxonomy_series';

    /**
     * The database table used by the model
     *
     * @var string
     */
    public $table = self::TABLE_NAME;

    /**
     * Specifying of implemented behaviours as strings is convenient when
     * the target behaviour could be missing due to disabled or not installed
     * plugin. You won't get an error, the plugin would simply work without model
     *
     * @var array
     */
    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    /**
     * Translatable properties, indexed property will be available in queries
     *
     * @var array
     */
    public $translatable = [
        'title',
        [
            'slug',
            'index' => true
        ],
        'description'
    ];

    /**
     * Relations
     *
     * @var array
     */
    public $hasMany = [
        'posts' => [
            Post::class,
            'key' => self::TABLE_NAME . "_id"
        ],
    ];

    /**
     * Relations
     *
     * @var array
     */
    public $attachMany = [
        'featured_images' => [
            File::class
        ]
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        'title' => "required|unique:" . self::TABLE_NAME . "|min:3|regex:/^[a-z0-9\-\?!,.\" ]+$/i",
        'slug'  => "required|unique:" . self::TABLE_NAME . "|min:3|regex:/^[a-z0-9\-]+$/i"
    ];

    /**
     * Validation messages
     *
     * @var array
     */
    public $customMessages = [
        'title.required' => Plugin::LOCALIZATION_KEY . 'form.series.title_required',
        'title.unique'   => Plugin::LOCALIZATION_KEY . 'form.series.title_unique',
        'title.regex'    => Plugin::LOCALIZATION_KEY . 'form.series.title_invalid',
        'title.min'      => Plugin::LOCALIZATION_KEY . 'form.series.title_too_short',

        'slug.required' => Plugin::LOCALIZATION_KEY . 'form.series.slug_required',
        'slug.unique'   => Plugin::LOCALIZATION_KEY . 'form.series.slug_unique',
        'slug.regex'    => Plugin::LOCALIZATION_KEY . 'form.series.slug_invalid',
        'slug.min'      => Plugin::LOCALIZATION_KEY . 'form.series.slug_too_short',
    ];

    /**
     * The attributes on which the post list can be ordered
     *
     * @var array
     */
    public static $sortingOptions = [
        'title asc' => Plugin::LOCALIZATION_KEY . 'order_options.title_asc',
        'title desc' => Plugin::LOCALIZATION_KEY . 'order_options.title_desc',
        'created_at asc' => Plugin::LOCALIZATION_KEY . 'order_options.created_at_asc',
        'created_at desc' => Plugin::LOCALIZATION_KEY . 'order_options.created_at_desc',
        'posts_count asc' => Plugin::LOCALIZATION_KEY . 'order_options.post_count_asc',
        'posts_count desc' => Plugin::LOCALIZATION_KEY . 'order_options.post_count_desc',
        'random' => Plugin::LOCALIZATION_KEY . 'order_options.random'
    ];

    /**
     * @var array
     */
    protected $slugs = ['slug' => 'title'];

    /**
     * @return mixed
     */
    public function getPostCountAttribute()
    {
        return $this->posts()->isPublished()->count();
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function getModelUrlParams(array $params): array
    {
        return [
            array_get($params, 'series', 'series') => $this->slug
        ];
    }
}
