<?php
use QuidNovi\Finder\CategoryFinder;

/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 27/03/2015
 * Time: 15:16
 */

class CategoryFinderTest extends \PHPUnit_Framework_TestCase{

    public function testFindAllMethod()
    {
        // Given
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $finder = new CategoryFinder($pdo);

        // When
        $categories = $finder->findAll();
        $count = $finder->countCategories();
        $arraySize = count($categories);

        // Then
        $this->assertNotNull($categories);
        $this->assertEquals($count, $arraySize);
    }

}