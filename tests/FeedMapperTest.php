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

namespace tests;

use PDO;
use QuidNovi\DataSource\DataSource;
use QuidNovi\Mapper\FeedMapper;
use QuidNovi\Model\Feed;

class FeedMapperTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    private function getComponentRow(DataSource $dataSource, $id)
    {
        $selectComponentQuery = <<<SQL
SELECT * FROM Component
WHERE id = :id
SQL;
        $componentRow = $dataSource->executeQuery($selectComponentQuery, ['id' => $id]);
        return $componentRow->fetch(PDO::FETCH_ASSOC);
    }

    private function getFeedRow(DataSource $dataSource, $id)
    {
        $selectComponentQuery = <<<SQL
SELECT * FROM Feed
WHERE id = :id
SQL;
        $componentRow = $dataSource->executeQuery($selectComponentQuery, ['id' => $id]);
        return $componentRow->fetch(PDO::FETCH_ASSOC);
    }

    public function testFeedInsertion()
    {
        // Given
        $feed = new Feed('foo', 'www.foo.bar', new \DateTime());
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new FeedMapper($dataSource);

        // When
        $mapper->persist($feed);
        $id = $dataSource->lastInsertId('Component');

        $componentRow = $this->getComponentRow($dataSource, $id);
        $feedRow = $this->getFeedRow($dataSource, $id);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($feedRow);
        $this->assertEquals($feed->id, $id);
        $this->assertEquals($feed->name, $componentRow['name']);
        $this->assertEquals($feed->getContainer()->id, $componentRow['containerId']);
        $this->assertEquals($feed->getSource(), $feedRow['source']);
        $this->assertEquals($feed->lastUpdate->format('Y-m-d H:i:s'), $feedRow['lastUpdate']);
    }

    public function testFeedUpdate()
    {
        // Given
        $feed = new Feed('foo', 'www.foo.bar', new \DateTime());
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new FeedMapper($dataSource);

        // When
        $mapper->persist($feed);
        $id = $feed->id;
        $feed->name = 'bar';
        $mapper->persist($feed);

        // Then
        $this->assertEquals($id, $feed->id);
        $componentRow = $this->getComponentRow($dataSource, $id);
        $feedRow = $this->getFeedRow($dataSource, $id);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($feedRow);
        $this->assertEquals($feed->id, $id);
        $this->assertEquals($feed->name, $componentRow['name']);
        $this->assertEquals($feed->getContainer()->id, $componentRow['containerId']);
        $this->assertEquals($feed->getSource(), $feedRow['source']);
        $this->assertEquals($feed->lastUpdate->format('Y-m-d H:i:s'), $feedRow['lastUpdate']);
    }

    public function testFeedDeletion()
    {
        // Given
        $feed = new Feed('foo', 'www.foo.bar', new \DateTime());
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new FeedMapper($dataSource);

        // When
        $mapper->persist($feed);
        $id = $feed->id;
        $mapper->remove($feed);

        // Then
        $componentRow = $this->getComponentRow($dataSource, $id);
        $feedRow = $this->getFeedRow($dataSource, $id);
        $this->assertEquals(null, $componentRow);
        $this->assertEquals(null, $feedRow);
    }
}
