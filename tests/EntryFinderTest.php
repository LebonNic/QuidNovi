<?php

use QuidNovi\DataSource\DataSource;
use QuidNovi\Finder\EntryFinder;
use QuidNovi\Mapper\FeedMapper;
use QuidNovi\Model\Entry;
use QuidNovi\Model\Feed;

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
class EntryFinderTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    public function testFindAllMethod()
    {
        // Given
        $DataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $finder = new EntryFinder($DataSource);

        // When
        $entries = $finder->findAll();
        $count = $finder->countEntries();
        $arraySize = count($entries);

        // Then
        $this->assertNotNull($entries);
        $this->assertEquals($count, $arraySize);
    }

    public function testFindMethod()
    {
        // Given
        $feed = new Feed('foo', 'www.foo.bar', new \DateTime());
        $entry = new Entry('foo', 'bar', 'www.foo.bar/1234', new \DateTime());
        $DataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $feed->addEntry($entry);
        $mapper = new FeedMapper($DataSource);
        $finder = new EntryFinder($DataSource);

        // When
        $mapper->persist($feed);

        // Then
        $retrievedEntry = $finder->find($entry->id);
        $this->assertEquals($entry->id, $retrievedEntry->id);
        $this->assertEquals($entry->title, $retrievedEntry->title);
        $this->assertEquals($entry->summary, $retrievedEntry->summary);
        $this->assertEquals($entry->getLocation(), $retrievedEntry->getLocation());
        $this->assertEquals($entry->getPublicationDate(), $retrievedEntry->getPublicationDate());

        $this->assertEquals($entry->feed->id, $retrievedEntry->feed->id);
        $this->assertEquals($entry->feed->name, $retrievedEntry->feed->name);
        $this->assertEquals($entry->feed->getSource(), $retrievedEntry->feed->getSource());
        $this->assertEquals($entry->feed->lastUpdate, $retrievedEntry->feed->lastUpdate);
        //$this->assertEquals($entry->feed->getEntries(), $retrievedEntry->feed->getEntries());

        $this->assertEquals($entry->isRead(), $retrievedEntry->isRead());
        $this->assertEquals($entry->isSaved(), $retrievedEntry->isSaved());
    }
}
