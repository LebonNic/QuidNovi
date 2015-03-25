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
use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Exception\DeletionFailure;
use QuidNovi\Exception\UpdateFailure;
use QuidNovi\Model\Component;

class ComponentMapper
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function persist(Component $component)
    {
        if ($component->id) {
            $this->update($component);
        } else {
            $this->insert($component);
        }
    }

    private function insert(Component $component)
    {
        $insertQuery = <<<SQL
INSERT INTO Component (name)
VALUES (:name)
SQL;
        $statement = $this->pdo->prepare($insertQuery);
        $success = $statement->execute(['name' => $component->name]);

        if (!$success) {
            throw new InsertionFailure($component);
        }

        $id = $this->pdo->lastInsertId('Component');
        $component->id = $id;
    }

    private function update(Component $component)
    {
        $updateQuery = <<<SQL
UPDATE Component
SET name = :name
WHERE id = :id
SQL;
        $statement = $this->pdo->prepare($updateQuery);
        $success = $statement->execute(['name' => $component->name, 'id' => $component->id]);

        if (!$success) {
            throw new UpdateFailure($component);
        }
    }

    public function remove(Component $component)
    {
        $deleteQuery = <<<SQL
DELETE FROM Component
WHERE id = :id
SQL;
        $statement = $this->pdo->prepare($deleteQuery);
        $success = $statement->execute(['id' => $component->id]);

        if (!$success) {
            throw new DeletionFailure($component);
        }
    }
}
