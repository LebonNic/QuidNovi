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

}