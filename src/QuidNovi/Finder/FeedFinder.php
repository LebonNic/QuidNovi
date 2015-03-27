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
use QuidNovi\Model\Feed;
use PDO;

class FeedFinder
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        $feed = null;
        $componentFinder = new ComponentFinder($this->pdo);
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
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute(['id' => $id]);

        if (!$success)
            throw new ResearchFaillure("An error occurred during the feed research. More info: "
        . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    private function reconstructFeed($componentRow, $feedRow)
    {
        $lastUpdate = new \DateTime($feedRow['lastUpdate']);
        $feed = new Feed($componentRow['name'], $feedRow['source'], $lastUpdate);
        $feed->id = $componentRow['id'];
        //TODO add a lazy initialisation system for the collection "$entries" in a Feed object

        return $feed;
    }

    public function findAll()
    {
        $componentFinder = new ComponentFinder($this->pdo);
        $componentRows = $componentFinder->getAllComponentRows();
        $feeds = array();

        foreach($componentRows as $componentRow)
        {
            $feedRow = $this->getFeedRow($componentRow['id']);
            if($feedRow)
            {
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
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute();

        if (!$success)
            throw new ResearchFaillure("An error occurred during the feeds' count. More info: "
                . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $count = $row['COUNT(id)'];

        return $count;
    }
}
