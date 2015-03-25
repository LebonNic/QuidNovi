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
use QuidNovi\Finder\CategoryFinder;
use QuidNovi\Mapper\CategoryMapper;
use QuidNovi\Model\Category;

class CategoryMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testComponentInsertion()
    {
        // Given
        $category = new Category('Foo');
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($pdo);
        $finder = new CategoryFinder($pdo);

        // When
        $mapper->persist($category);

        // Then
        $this->assertEquals($category->id, $pdo->lastInsertId('Component'));
        $this->assertEquals($category, $finder->find($category->id));
    }

    public function testComponentUpdate()
    {
        // Given
        $category = new Category('Foo');
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($pdo);
        $finder = new CategoryFinder($pdo);

        // When
        $mapper->persist($category);
        $id = $category->id;
        $category->name = 'Bar';
        $mapper->persist($category);

        // Then
        $this->assertEquals($id, $category->id);
        $this->assertEquals($category, $finder->find($category->id));
    }

    public function testComponentDeletion()
    {
        // Given
        $category = new Category('Foo');
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $mapper = new CategoryMapper($pdo);
        $finder = new CategoryFinder($pdo);

        // When
        $mapper->persist($category);
        $id = $category->id;
        $mapper->remove($category);

        // Then
        $this->assertEquals(null, $category->id);
        $this->assertEquals(null, $finder->find($id));
    }
}
