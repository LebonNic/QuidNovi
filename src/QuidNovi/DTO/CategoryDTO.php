<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 28/03/2015
 * Time: 09:37
 */

namespace QuidNovi\DTO;


use QuidNovi\Model\Category;
use QuidNovi\Model\Feed;

class CategoryDTO {
    public $id;
    public $name;
    public $feeds;
    public $categories;

    function __construct(Category $category)
    {
        $this->id = $category->id;
        $this->name = $category->name;
        $this->feeds = [];
        $this->categories = [];
        $components = $category->getComponents();
        if ($components !== null) {
            foreach ($components as $component) {
                if ($component instanceof Category) {
                    array_push($this->categories, new CategoryDTO($component));
                } else if ($component instanceof Feed) {
                    array_push($this->feeds, new FeedDTO($component));
                }
            }
        }
    }
}