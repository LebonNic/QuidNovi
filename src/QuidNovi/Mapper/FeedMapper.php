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

use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Exception\UpdateFailure;
use QuidNovi\Model\Feed;
use PDO;

class FeedMapper
{
    private $pdo;

    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function persist(Feed $feed)
    {
        $needUpdate = false;
        if($feed->id)
            $needUpdate = true;

        $componentMapper = new ComponentMapper($this->pdo);
        $componentMapper->persist($feed);

        if ($needUpdate) {
            $this->update($feed);
        } else {
            $this->insert($feed);
        }
    }

    public function remove(Feed $feed)
    {
        $deleteQuery = <<<SQL
DELETE FROM Feed
WHERE id = :id
SQL;
        $statement = $this->pdo->prepare($deleteQuery);
        $success = $statement->execute(['id' => $feed->id]);

        if (!$success) {
            throw new DeletionFailure($feed);
        }

        $componentMapper = new ComponentMapper($this->pdo);
        $componentMapper->remove($feed);
        //delete the data stored in the category
        $feed->id = null;
    }

    private function update(Feed $feed)
    {
        $updateQuery = <<<SQL
UPDATE Feed
SET source = :source, lastUpdate = :lastUpdate
WHERE id = :id
SQL;
        $statement = $this->pdo->prepare($updateQuery);
        $success = $statement->execute(['source' => $feed->getSource(),
                                        'lastUpdate' => $feed->lastUpdate->format('Y-m-d H:i:s'),
                                        'id' => $feed->id]);

        if (!$success) {
            throw new UpdateFailure($feed);
        }
    }

    private function insert(Feed $feed)
    {
        $insertQuery = <<<SQL
INSERT INTO Feed (id, source, lastUpdate)
VALUES (:id, :source, :lastUpdate)
SQL;
        $statement =  $this->pdo->prepare($insertQuery);
        $success = $statement->execute(['id' => $feed->id,
                                        'source' => $feed->getSource(),
                                        'lastUpdate' => $feed->lastUpdate->format('Y-m-d H:i:s')]);

        if (!$success) {
            throw new InsertionFailure($feed);
        }
    }
}
