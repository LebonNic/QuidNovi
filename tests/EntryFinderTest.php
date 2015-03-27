<?php
use QuidNovi\DataSource\DataSource;
use QuidNovi\Finder\EntryFinder;

/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 27/03/2015
 * Time: 16:23
 */

class EntryFinderTest extends \PHPUnit_Framework_TestCase{

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

}