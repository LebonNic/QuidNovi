<?php

/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 25/03/2015
 * Time: 17:00.
 */

namespace QuidNovi\Finder;

use PDO;
use QuidNovi\Exception\ResearchFaillure;

class ComponentFinder
{
    private $pdo;

    public function __construct(PDO $pdo)
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

        if (!$success)
            throw new ResearchFaillure("An error occurred during the component research. More info: "
            . print_r($this->pdo->errorInfo()));

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    public function getAllComponentRows()
    {
        $selectQuery = <<<SQL
SELECT * FROM Component
SQL;

        $statement = $this->pdo->prepare($selectQuery);
        $success = $statement->execute();

        if (!$success)
            throw new ResearchFaillure("An error occurred during the components research. More info: "
                . print_r($this->pdo->errorInfo()));

        return $statement->fetchAll();
    }

    public function findAll()
    {
    }
}
