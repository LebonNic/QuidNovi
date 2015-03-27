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

use PDO;
use QuidNovi\Exception\ResearchFaillure;
use QuidNovi\Model\Entry;

class EntryFinder
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        $entry = null;
        $entryRow = $this->getEntryRow($id);

        if ($entryRow) {
            $entry = $this->reconstructEntry($entryRow);
        }

        return $entry;
    }

    private function getEntryRow($id)
    {
        $selectQuery = <<<SQL
SELECT * FROM Entry
WHERE id=(:id)
SQL;
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute(['id' => $id]);

        if (!$success)
            throw new ResearchFaillure("An error occurred during the entry research. More info: "
                . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    private function reconstructEntry($entryRow)
    {
        $entry = null;

        $publicationDate = new \DateTime($entryRow['publicationDate']);

        $isRead = false;
        $isSaved = false;
        if (1 == $entryRow['read']) {
            $isRead = true;
        }
        if (1 == $entryRow['saved']) {
            $isSaved = true;
        }

        $entry = new Entry($entryRow['title'],
            $entryRow['summary'],
            $entryRow['location'],
            $publicationDate,
            $isRead,
            $isSaved
        );

        $entry->id = $entryRow['id'];
        $feedFinder = new FeedFinder($this->pdo);
        $entry->feed = $feedFinder->find($entryRow['feedId']);

        return $entry;
    }

    private function getAllEntryRows()
    {
        $selectQuery = <<<SQL
SELECT * FROM Entry
SQL;
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute();

        if (!$success)
            throw new ResearchFaillure("An error occurred during the entries research. More info: "
                . print_r($this->pdo->errorInfo()));

        return $statement->fetchAll();
    }

    public function findAll()
    {
        $entryRows = $this->getAllEntryRows();
        $entries = array();

        foreach($entryRows as $entryRow)
        {
            $entry = $this->reconstructEntry($entryRow);
            array_push($entries, $entry);
        }

        return $entries;
    }

    public function countEntries()
    {
        $selectQuery = <<<SQL
SELECT COUNT(id) FROM Entry
SQL;
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute();

        if (!$success)
            throw new ResearchFaillure("An error occurred during the entries' count. More info: "
                . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $count = $row['COUNT(id)'];

        return $count;
    }
}
