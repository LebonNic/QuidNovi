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
use QuidNovi\Mapper\CategoryMapper;
use QuidNovi\Model\Category;
use QuidNovi\Model\Feed;

class CategoryMapperTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    private function getComponentRowsWithContainerId(DataSource $dataSource, $containerId)
    {
        $selectComponentQuery = <<<SQL
SELECT * FROM Component
WHERE containerId = :containerId
SQL;
        $componentRow = $dataSource->executeQuery($selectComponentQuery, ['containerId' => $containerId]);

        return $componentRow->fetchAll();
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

    private function getComponentRow(DataSource $dataSource, $id)
    {
        $selectComponentQuery = <<<SQL
SELECT * FROM Component
WHERE id = :id
SQL;
        $componentRow = $dataSource->executeQuery($selectComponentQuery, ['id' => $id]);

        return $componentRow->fetch(PDO::FETCH_ASSOC);
    }

    private function getCategoryRow(DataSource $dataSource, $id)
    {
        $selectComponentQuery = <<<SQL
SELECT * FROM Category
WHERE id = :id
SQL;
        $componentRow = $dataSource->executeQuery($selectComponentQuery, ['id' => $id]);

        return $componentRow->fetch(PDO::FETCH_ASSOC);
    }

    public function testCategoryInsertion()
    {
        // Given
        $category = new Category('Foo');
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($dataSource);

        // When
        $mapper->persist($category);
        $id = $dataSource->lastInsertId('Component');

        // Then
        $componentRow = $this->getComponentRow($dataSource, $id);
        $categoryRow = $this->getCategoryRow($dataSource, $id);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($categoryRow);
        $this->assertEquals($category->id, $id);
        $this->assertEquals($category->name, $componentRow['name']);
        $this->assertEquals($category->getContainer()->id, $componentRow['containerId']);
    }

    public function testCategoryUpdate()
    {
        // Given
        $category = new Category('Foo');
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($dataSource);

        // When
        $mapper->persist($category);
        $id = $category->id;
        $category->name = 'Bar';
        $mapper->persist($category);

        // Then
        $componentRow = $this->getComponentRow($dataSource, $id);
        $categoryRow = $this->getCategoryRow($dataSource, $id);
        $this->assertEquals($id, $category->id);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($categoryRow);
        $this->assertEquals($category->name, $componentRow['name']);
        $this->assertEquals($category->getContainer()->id, $componentRow['containerId']);
    }

    public function testCategoryDeletion()
    {
        // Given
        $category = new Category('Foo');
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($dataSource);

        // When
        $mapper->persist($category);
        $id = $category->id;
        $mapper->remove($category);

        // Then
        $componentRow = $this->getComponentRow($dataSource, $id);
        $categoryRow = $this->getCategoryRow($dataSource, $id);
        $this->assertEquals(null, $componentRow);
        $this->assertEquals(null, $categoryRow);
    }

    public function testContainedComponentsSaveInCategory()
    {
        // Given
        $category = new Category('Foo');
        $anOtherCategory = new Category('Bar');
        $feed = new Feed('FooFeed', 'www.foofeed.com', new \DateTime());
        $dataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($dataSource);

        // When
        $category->addComponent($anOtherCategory);
        $category->addComponent($feed);
        $mapper->persist($category);

        // Then
        $componentRows = $this->getComponentRowsWithContainerId($dataSource, $category->id);
        $componentRow = array_shift($componentRows);
        $categoryRow = $this->getCategoryRow($dataSource, $componentRow['id']);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($categoryRow);
        $this->assertEquals($anOtherCategory->name, $componentRow['name']);
        $this->assertEquals($category->id, $componentRow['containerId']);

        $componentRow = array_shift($componentRows);
        $feedRow = $this->getFeedRow($dataSource, $componentRow['id']);
        $this->assertNotNull($componentRow);
        $this->assertNotNull($feedRow);
        $this->assertEquals($feed->name, $componentRow['name']);
        $this->assertEquals($category->id, $componentRow['containerId']);
        $this->assertEquals($feed->getSource(), $feedRow['source']);
        $this->assertEquals($feed->lastUpdate->format('Y-m-d H:i:s'), $feedRow['lastUpdate']);
    }
}
