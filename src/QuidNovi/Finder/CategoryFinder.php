<?php

/**
 * The MIT License (MIT).
 *
 * Copyright (c) 2015 Antoine Colmard
 *               2015 Nicolas Prugne
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace QuidNovi\Finder;

use QuidNovi\DataSource\DataSource;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Exception\ResearchFaillure;
use QuidNovi\Model\Category;
use PDO;
use QuidNovi\Model\Feed;

class CategoryFinder
{
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
    }

    public function find($id)
    {
        $category = null;
        $componentFinder = new ComponentFinder($this->DataSource);
        $componentRow = $componentFinder->getComponentRow($id);

        if ($componentRow) {
            $categoryRow = $this->getCategoryRow($id);
            if ($categoryRow) {
                $category = $this->reconstructCategory($componentRow, $categoryRow);
            }
        }

        return $category;
    }

    private function getCategoryRow($id)
    {
        $selectQuery = <<<SQL
SELECT * FROM Category
WHERE id=(:id)
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery, ['id' => $id]);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure('An error occurred during the category research. More info: '
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    private function reconstructCategory($componentRow, $categoryRow)
    {
        $category = new Category($componentRow['name']);
        $category->id = (int)$componentRow['id'];
        $containerId = $componentRow['containerId'];
        $categoryFinder = $this;
        $category->setContainerClosure(function () use ($categoryFinder, $containerId) {
            return $categoryFinder->find($containerId);
        });
        $feedFinder = new FeedFinder($this->DataSource);
        $category->setComponentsClosure(function () use ($category, $categoryFinder, $feedFinder) {
            $categories = $categoryFinder->findAll();
            $feeds = $feedFinder->findAll();

            $components = [];

            foreach ($categories as $childCategory) {
                /* @var $childCategory Category */
                if ($childCategory->getContainer() !== null &&
                    $childCategory->getContainer()->id === $category->id) {
                    array_push($components, $childCategory);
                }
            }
            foreach ($feeds as $childFeed) {
                /* @var $childFeed Feed */
                if ($childFeed->getContainer() !== null &&
                    $childFeed->getContainer()->id === $category->id) {
                    array_push($components, $childFeed);
                }
            }

            return $components;
        });
        return $category;
    }

    public function findAll()
    {
        $componentFinder = new ComponentFinder($this->DataSource);
        $componentRows = $componentFinder->getAllComponentRows();
        $categories = array();

        foreach ($componentRows as $componentRow) {
            $categoryRow = $this->getCategoryRow($componentRow['id']);
            if ($categoryRow) {
                $category = $this->reconstructCategory($componentRow, $categoryRow);
                array_push($categories, $category);
            }
        }

        return $categories;
    }

    public function countCategories()
    {
        $selectQuery = <<<SQL
SELECT COUNT(id) FROM Category
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure("An error occurred during the categories' count. More info: "
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        $count = array_shift($row);

        return $count;
    }
}
