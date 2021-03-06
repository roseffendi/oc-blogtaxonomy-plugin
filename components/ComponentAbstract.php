<?php

namespace GinoPane\BlogTaxonomy\Components;

use ArrayAccess;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use RainLab\Blog\Models\Post;
use Cms\Classes\ComponentBase;
use RainLab\Blog\Models\Category;
use October\Rain\Database\Collection;

/**
 * Class ComponentAbstract
 *
 * @package GinoPane\BlogTaxonomy\Components
 */
abstract class ComponentAbstract extends ComponentBase
{
    /**
     * Reference to the page name for linking to posts
     *
     * @var string
     */
    protected $postPage;

    /**
     * Reference to the page name for linking to categories
     *
     * @var string
     */
    protected $categoryPage;

    /**
     * @param Collection $items
     * @param string $urlPage
     * @param Controller $controller
     * @param array $modelUrlParams
     */
    public function setUrls(
        Collection $items,
        string $urlPage,
        Controller $controller,
        array $modelUrlParams = array()
    ) {
        if ($items) {
            foreach ($items as $item) {
                $item->setUrl($urlPage, $controller, $modelUrlParams);
            }
        }
    }

    /**
     * Set Urls to posts
     *
     * @param ArrayAccess $posts
     */
    public function setPostUrls(ArrayAccess $posts)
    {
        // Add a "url" helper attribute for linking to each post and category
        if ($posts && $posts->count() && !empty($this->postPage)) {
            $blogPostComponent = $this->getComponent('blogPost', $this->postPage);
            $blogCategoriesComponent = $this->getComponent('blogCategories', $this->categoryPage ?? '');

            $posts->each(function($post) use ($blogPostComponent, $blogCategoriesComponent) {
                /** @var Post $post */
                $post->setUrl(
                    $this->postPage,
                    $this->controller,
                    [
                        'slug' => $this->urlProperty($blogPostComponent, 'slug')
                    ]
                );

                if (!empty($this->categoryPage) && $post->categories->count()) {
                    $post->categories->each(function ($category) use ($blogCategoriesComponent) {
                        /** @var Category $category */
                        $category->setUrl(
                            $this->categoryPage,
                            $this->controller,
                            [
                                'slug' => $this->urlProperty($blogCategoriesComponent, 'slug')
                            ]
                        );
                    });
                }
            });
        }
    }

    /**
     * A helper function to return property value
     *
     * @param ComponentBase|null $component
     * @param string $name
     *
     * @return string|null
     */
    protected function urlProperty(ComponentBase $component = null, string $name = '')
    {
        return $component ? $component->propertyName($name, $name) : null;
    }

    /**
     * Returns page property defaulting to the value from defineProperties() array with fallback
     * to explicitly passed default value
     *
     * @param string $property
     * @param $default
     *
     * @return mixed
     */
    public function getProperty(string $property, $default = null)
    {
        return $this->property($property, $this->defineProperties()[$property]['default'] ?? $default);
    }

    /**
     * @param string $componentName
     * @param string $page
     * @return ComponentBase|null
     */
    protected function getComponent(string $componentName, string $page)
    {
        $component = null;

        $page = Page::load(Theme::getActiveTheme(), $page);

        if ($page !== null) {
            $component = $page->getComponent($componentName);
        }

        if (!empty($component) && \is_callable([$this->controller, 'setComponentPropertiesFromParams'])) {
            $this->controller->setComponentPropertiesFromParams($component);
        }

        return $component;
    }
}
