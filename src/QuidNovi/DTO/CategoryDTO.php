<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 28/03/2015
 * Time: 09:37
 */

namespace QuidNovi\DTO;


use QuidNovi\Model\Category;

class CategoryDTO {
    public $id;
    public $name;
    public $categories;
    public $feeds;

    function __construct(Category $category)
    {
        $this->id = $category->id;
        $this->name = $category->name;
        $this->feeds = [];
        $this->categories = [];
        foreach($category->getComponents() as $component) {
            if (get_class($component) === 'QuidNovi\\Model\\Feed') {
                array_push($this->feeds, new FeedDTO($component));
            } else {
                array_push($this->categories, new FeedDTO($component));
            }
        }
    }
}