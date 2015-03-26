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

namespace QuidNovi\Mapper;

use PDO;
use QuidNovi\Exception\DeletionFailure;
use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Model\Category;

class CategoryMapper
{
    private $categories = array();
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function persist(Category $category)
    {
        $needUpdate = false;
        if($category->id)
            $needUpdate = true;

        $componentMapper = new ComponentMapper($this->pdo);
        $componentMapper->persist($category);

        if ($needUpdate)
            $this->update($category);
        else
            $this->insert($category);
    }

    private function insert(Category $category)
    {
        $insertQuery = <<<SQL
INSERT INTO Category (id)
VALUES (:id)
SQL;
        $statement =  $this->pdo->prepare($insertQuery);
        $success = $statement->execute(['id' => $category->id]);

        if (!$success)
            throw new InsertionFailure($category);

        $this->categories[$category->id] = $category;
    }

    private function update(Category $category)
    {
    }

    public function remove(Category $category)
    {
        $deleteQuery = <<<SQL
DELETE FROM Category
WHERE id = :id
SQL;
        $statement = $this->pdo->prepare($deleteQuery);
        $success = $statement->execute(['id' => $category->id]);

        if (!$success)
            throw new DeletionFailure($category);

        $componentMapper = new ComponentMapper($this->pdo);
        $componentMapper->remove($category);
    }
}
