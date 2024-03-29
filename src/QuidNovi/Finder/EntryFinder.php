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
use QuidNovi\DataSource\DataSource;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Exception\ResearchFaillure;
use QuidNovi\Model\Entry;
use QuidNovi\Specification\Specification;

class EntryFinder
{
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
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
        try {
            $result = $this->DataSource->executeQuery($selectQuery, ['id' => $id]);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure('An error occurred during the entry research. More info: '
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);

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
        $feedFinder = new FeedFinder($this->DataSource);
        $entry->feed = $feedFinder->find($entryRow['feedId']);

        return $entry;
    }

    private function getAllEntryRows()
    {
        $selectQuery = <<<SQL
SELECT * FROM Entry
ORDER BY publicationDate DESC
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure('An error occurred during the entries research. More info: '
                .print_r($this->DataSource->errorInfo()));
        }

        return $result->fetchAll();
    }

    public function findAll()
    {
        $entryRows = $this->getAllEntryRows();
        $entries = array();

        foreach ($entryRows as $entryRow) {
            $entry = $this->reconstructEntry($entryRow);
            array_push($entries, $entry);
        }

        return $entries;
    }

    public function findEntriesAssociatedToFeed($feedId)
    {
        $selectQuery = <<<SQL
SELECT * FROM Entry
WHERE feedId = :feedId
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery, ['feedId' => $feedId]);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure('An error occurred during the entries research. More info: '
                .print_r($this->DataSource->errorInfo()));
        }

        $entryRows = $result->fetchAll();
        $entries = array();
        foreach ($entryRows as $entryRow) {
            $entry = $this->reconstructEntry($entryRow);
            array_push($entries, $entry);
        }

        return $entries;
    }

    public function findSatisfying(Specification $specification)
    {
        $entries = $this->findAll();
        $satisfying = [];

        foreach ($entries as $entry) {
            if ($specification->isSatisfiedBy($entry)) {
                array_push($satisfying, $entry);
            }
        }

        return $satisfying;
    }

    public function countEntries()
    {
        $selectQuery = <<<SQL
SELECT COUNT(id) FROM Entry
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure("An error occurred during the entries' count. More info: "
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        $count = array_shift($row);

        return $count;
    }
}
