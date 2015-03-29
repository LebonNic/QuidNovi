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
use QuidNovi\Model\Feed;
use PDO;

class FeedFinder
{
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
    }

    public function find($id)
    {
        $feed = null;
        $componentFinder = new ComponentFinder($this->DataSource);
        $componentRow = $componentFinder->getComponentRow($id);

        if ($componentRow) {
            $feedRow = $this->getFeedRow($id);
            if ($feedRow) {
                $feed = $this->reconstructFeed($componentRow, $feedRow);
            }
        }

        return $feed;
    }

    private function getFeedRow($id)
    {
        $selectQuery = <<<SQL
SELECT * FROM Feed
WHERE id=(:id)
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery, ['id' => $id]);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure('An error occurred during the feed research. More info: '
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    private function reconstructFeed($componentRow, $feedRow)
    {
        $lastUpdate = new \DateTime($feedRow['lastUpdate']);
        $feed = new Feed($componentRow['name'], $feedRow['source'], $lastUpdate);
        $feedId = (int) $componentRow['id'];
        $feed->id = $feedId;
        $containerId = $componentRow['containerId'];

        $categoryFinder = new CategoryFinder($this->DataSource);
        $feed->setContainerClosure(function () use ($categoryFinder, $containerId) {
            return $categoryFinder->find($containerId);
        });

        $entryFinder = new EntryFinder($this->DataSource);
        $feed->setEntriesLazyLoadingClosure(function () use ($entryFinder, $feedId) {
            return $entryFinder->findEntriesAssociatedToFeed($feedId);
        });

        return $feed;
    }

    public function findAll()
    {
        $componentFinder = new ComponentFinder($this->DataSource);
        $componentRows = $componentFinder->getAllComponentRows();
        $feeds = array();

        foreach ($componentRows as $componentRow) {
            $feedRow = $this->getFeedRow($componentRow['id']);
            if ($feedRow) {
                $feed = $this->reconstructFeed($componentRow, $feedRow);
                array_push($feeds, $feed);
            }
        }

        return $feeds;
    }

    public function countFeeds()
    {
        $selectQuery = <<<SQL
SELECT COUNT(id) FROM Feed
SQL;
        try {
            $result = $this->DataSource->executeQuery($selectQuery);
        } catch (QueryExecutionFailure $e) {
            throw new ResearchFaillure("An error occurred during the feeds' count. More info: "
                .print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        $count = array_shift($row);

        return $count;
    }
}
