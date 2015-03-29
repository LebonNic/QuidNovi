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

use QuidNovi\DataSource\DataSource;
use QuidNovi\Exception\DeletionFailure;
use QuidNovi\Exception\InsertionFailure;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Exception\UpdateFailure;
use QuidNovi\Model\Entry;

class EntryMapper
{
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
    }

    public function persist(Entry $entry)
    {
        if ($entry->id) {
            $this->update($entry);
        } else {
            $this->insert($entry);
        }
    }

    private function update(Entry $entry)
    {
        $updateQuery = <<<SQL
UPDATE Entry
SET feedId = :feedId,
    title = :title,
    summary = :summary,
    location = :location,
    publicationDate = :publicationDate,
    read = :read,
    saved = :saved
WHERE id = :id
SQL;
        try {
            $this->DataSource->executeQuery($updateQuery,
                                            ['feedId' => $entry->feed->id,
                                            'title' => $entry->title,
                                            'summary' => $entry->summary,
                                            'location' => $entry->getLocation(),
                                            'publicationDate' => $entry->getPublicationDate()->format('Y-m-d H:i:s'),
                                            'read' => (($entry->isRead()) ? 1 : 0),
                                            'saved' => (($entry->isSaved()) ? 1 : 0),
                                            'id' => $entry->id, ]);
        } catch (QueryExecutionFailure $e) {
            throw new UpdateFailure($entry);
        }
    }

    private function insert(Entry $entry)
    {
        $insertQuery = <<<SQL
INSERT INTO Entry (feedId, title, summary, location, publicationDate, read, saved)
VALUES            (:feedId, :title, :summary, :location, :publicationDate, :read, :saved)
SQL;
        try {
            $this->DataSource->executeQuery($insertQuery,
                                            ['feedId' => $entry->feed->id,
                                            'title' => $entry->title,
                                            'summary' => $entry->summary,
                                            'location' => $entry->getLocation(),
                                            'publicationDate' => $entry->getPublicationDate()->format('Y-m-d H:i:s'),
                                            'read' => ($entry->isRead()) ? 1 : 0,
                                            'saved' => ($entry->isSaved()) ? 1 : 0, ]);
        } catch (QueryExecutionFailure $e) {
            throw new InsertionFailure($entry);
        }

        $id = $this->DataSource->lastInsertId('Entry');
        $entry->id = $id;
    }

    public function remove(Entry $entry)
    {
        $deleteQuery = <<<SQL
DELETE FROM Entry
WHERE id = :id
SQL;
        try {
            $this->DataSource->executeQuery($deleteQuery, ['id' => $entry->id]);
        } catch (QueryExecutionFailure $e) {
            throw new DeletionFailure($entry);
        }

        $entry->id = null;
    }
}
