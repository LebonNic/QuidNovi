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
use QuidNovi\Finder\EntryFinder;
use QuidNovi\Mapper\EntryMapper;
use QuidNovi\Model\Entry;

class EntryMapperTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    public function testComponentInsertion()
    {
        // Given
        $entry = new Entry('foo', 'bar', 'www.foo.bar/1234', new \DateTime());
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new EntryMapper($pdo);
        $finder = new EntryFinder($pdo);

        // When
        $mapper->persist($entry);

        // Then
        $this->assertEquals($entry->id, $pdo->lastInsertId('Component'));
        $this->assertEquals($entry, $finder->find($entry->id));
    }

    public function testComponentUpdate()
    {
        // Given
        $entry = new Entry('foo', 'bar', 'www.foo.bar/1234', new \DateTime());
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new EntryMapper($pdo);
        $finder = new EntryFinder($pdo);

        // When
        $mapper->persist($entry);
        $id = $entry->id;
        $entry->title = 'baz';
        $entry->summary = 'quux';
        $entry->markAsRead();
        $entry->markAsSaved();
        $mapper->persist($entry);

        // Then
        $this->assertEquals($id, $entry->id);
        $this->assertEquals($entry, $finder->find($entry->id));
    }

    public function testComponentDeletion()
    {
        // Given
        $entry = new Entry('foo', 'bar', 'www.foo.bar/1234', new \DateTime());
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new EntryMapper($pdo);
        $finder = new EntryFinder($pdo);

        // When
        $mapper->persist($entry);
        $id = $entry->id;
        $mapper->remove($entry);

        // Then
        $this->assertEquals(null, $entry->id);
        $this->assertEquals(null, $finder->find($id));
    }
}