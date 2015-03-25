<?php
/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 25/03/2015
 * Time: 17:00
 */

namespace QuidNovi\Finder;

use PDO;

class ComponentFinder {

    private $pdo;

    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
    }

    public function getComponentRow($id)
    {
        $selectQuery = <<<SQL
SELECT * FROM Component
WHERE id=(:id)
SQL;
        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute(['id' => $id]);

        if(!$success){
            //TODO Throw an exception
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function findAll()
    {
    }

}