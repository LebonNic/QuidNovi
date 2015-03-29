<?php
use QuidNovi\DataSource\DataSource;
use QuidNovi\Finder\CategoryFinder;
use QuidNovi\Mapper\CategoryMapper;
use QuidNovi\Model\Category;

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

class CategoryFinderTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    public function testFindAllMethod()
    {
        // Given
        $DataSource = new \QuidNovi\DataSource\DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $finder = new CategoryFinder($DataSource);

        // When
        $categories = $finder->findAll();
        $count = $finder->countCategories();
        $arraySize = count($categories);

        // Then
        $this->assertNotNull($categories);
        $this->assertEquals($count, $arraySize);
    }

    public function testFindMethod()
    {
        // Given
        $category = new Category('Foo');
        $DataSource = new DataSource('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($DataSource);
        $finder = new CategoryFinder($DataSource);

        // When
        $mapper->persist($category);

        // Then
        $this->assertEquals($category->id, $DataSource->lastInsertId('Component'));
        $retrievedCategory = $finder->find($category->id);
        $this->assertEquals($category->getComponents(), $retrievedCategory->getComponents());
        $this->assertEquals($category->id, $retrievedCategory->id);
        $this->assertEquals($category->name, $retrievedCategory->name);
        $this->assertEquals($category->getContainer(), $category->getContainer());
    }

}