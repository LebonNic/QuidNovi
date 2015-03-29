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
use QuidNovi\DataSource\DataSource;
use QuidNovi\Exception\DeletionFailure;
use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Model\Category;
use QuidNovi\Model\Feed;

class CategoryMapper
{
    /**
     * @var \PDO
     */
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
    }

    public function persist(Category $category)
    {
        $needUpdate = false;
        if ($category->id) {
            $needUpdate = true;
        }

        $componentMapper = new ComponentMapper($this->DataSource);
        $componentMapper->persist($category);

        if ($needUpdate) {
            $this->update($category);
        } else {
            $this->insert($category);
        }
    }

    private function insert(Category $category)
    {
        $insertQuery = <<<SQL
INSERT INTO Category (id)
VALUES (:id)
SQL;
        try {
            $this->DataSource->executeQuery($insertQuery, ['id' => $category->id]);
        } catch (QueryExecutionFailure $e) {
            throw new InsertionFailure($category);
        }

        $this->persistContainedComponents($category);
    }

    private function persistContainedComponents(Category $category)
    {
        $feedMapper = new FeedMapper($this->DataSource);
        foreach($category->getComponents() as $component)
        {
            if($component instanceof Category)
            {
                $this->persist($component);
            }
            else if($component instanceof Feed)
            {
                $feedMapper->persist($component);
            }
        }
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
        try {
            $this->DataSource->executeQuery($deleteQuery, ['id' => $category->id]);
        } catch (QueryExecutionFailure $e) {
            throw new DeletionFailure($category);
        }
        $componentMapper = new ComponentMapper($this->DataSource);
        $componentMapper->remove($category);
    }
}
