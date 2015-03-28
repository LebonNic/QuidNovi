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
use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Exception\DeletionFailure;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Exception\UpdateFailure;
use QuidNovi\Model\Component;

class ComponentMapper
{
    /**
     * @var PDO
     */
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
    }

    public function persist(Component $component)
    {
        if ($component->id)
            $this->update($component);
        else
            $this->insert($component);
    }

    private function insert(Component $component)
    {
        $insertQuery = <<<SQL
INSERT INTO Component (name)
VALUES (:name)
SQL;
        try
        {
            $this->DataSource->executeQuery($insertQuery, ['name' => $component->name]);
        }
        catch(QueryExecutionFailure $e)
        {
            throw new InsertionFailure($component);
        }

        $id = $this->DataSource->lastInsertId('Component');
        $component->id = $id;
    }

    private function update(Component $component)
    {
        $updateQuery = <<<SQL
UPDATE Component
SET name = :name
WHERE id = :id
SQL;
        try
        {
            $this->DataSource->executeQuery($updateQuery, ['name' => $component->name, 'id' => $component->id]);
        }
        catch(QueryExecutionFailure $e)
        {
            throw new UpdateFailure($component);
        }
    }

    public function remove(Component $component)
    {
        $deleteQuery = <<<SQL
DELETE FROM Component
WHERE id = :id
SQL;
        try
        {
            $this->DataSource->executeQuery($deleteQuery, ['id' => $component->id]);
        }
        catch(QueryExecutionFailure $e)
        {
            throw new DeletionFailure($component);
        }
        $component->id = null;

        //TODO recursive deletion
    }
}
