<?php
use QuidNovi\Finder\FeedFinder;

/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 27/03/2015
 * Time: 16:23
 */

class FeedFinderTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        date_default_timezone_set('Zulu');
    }

    public function testFindAllMethod()
    {
        // Given
        $pdo = new PDO('sqlite:'.__DIR__.'/../database.sqlite3');
        $finder = new FeedFinder($pdo);

        // When
        $entries = $finder->findAll();
        $count = $finder->countFeeds();
        $arraySize = count($entries);

        // Then
        $this->assertNotNull($entries);
        $this->assertEquals($count, $arraySize);
    }

}