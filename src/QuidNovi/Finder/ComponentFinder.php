<?php

/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 25/03/2015
 * Time: 17:00.
 */

namespace QuidNovi\Finder;

use PDO;
use QuidNovi\DataSource\DataSource;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Exception\ResearchFaillure;

class ComponentFinder
{
    private $DataSource;

    public function __construct(DataSource $DataSource)
    {
        $this->DataSource = $DataSource;
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
        try
        {
            $result = $this->DataSource->executeQuery($selectQuery, ['id' => $id]);
        }
        catch(QueryExecutionFailure $e)
        {
            throw new ResearchFaillure("An error occurred during the component research. More info: "
                . print_r($this->DataSource->errorInfo()));
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function getAllComponentRows()
    {
        $selectQuery = <<<SQL
SELECT * FROM Component
SQL;

        try
        {
            $result = $this->DataSource->executeQuery($selectQuery);
        }
        catch(QueryExecutionFailure $e)
        {
            throw new ResearchFaillure("An error occurred during the components research. More info: "
                . print_r($this->DataSource->errorInfo()));
        }

        return $result->fetchAll();
    }

    public function findAll()
    {
    }
}
