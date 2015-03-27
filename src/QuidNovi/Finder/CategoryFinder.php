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

use QuidNovi\Exception\ResearchFaillure;
use QuidNovi\Model\Category;
use PDO;

class CategoryFinder
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        $category = null;
        $componentFinder = new ComponentFinder($this->pdo);
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
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute(['id' => $id]);

        if (!$success)
            throw new ResearchFaillure("An error occurred during the category research. More info: "
                . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    private function reconstructCategory($componentRow, $categoryRow)
    {
        $category = new Category($componentRow['name']);
        $category->id = $componentRow['id'];
        //TODO add a lazy initialisation system for the collection "$components" in a Category object
        return $category;
    }

    public function findAll()
    {
        $componentFinder = new ComponentFinder($this->pdo);
        $componentRows = $componentFinder->getAllComponentRows();
        $categories = array();

        foreach($componentRows as $componentRow)
        {
            $categoryRow = $this->getCategoryRow($componentRow['id']);
            if($categoryRow)
            {
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
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute();

        if (!$success)
            throw new ResearchFaillure("An error occurred during the categories' count. More info: "
                . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $count = $row['COUNT(id)'];

        return $count;
    }
}
