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

namespace QuidNovi\DataSource;

use PDO;
use QuidNovi\Exception\QueryExecutionFailure;
use QuidNovi\Model\Category;

class DataSource extends PDO
{
    public function __construct($dsn, $username = null, $password = null, array $options = array())
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->initializeDatabase();
    }

    private function initializeDatabase()
    {
        $this->initializeComponentTable();
        $this->initializeCategoryTable();
        $this->initializeFeedTable();
        $this->initializeEntryTable();
    }

    private function initializeComponentTable()
    {
        $createComponentTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS Component
(
  id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  containerId INTEGER,
  name        TEXT                              NOT NULL,
  FOREIGN KEY (containerId) REFERENCES Component (id)
)
SQL;
        $this->prepare($createComponentTableQuery)->execute();

        $createRootComponentQuery = <<<SQL
INSERT OR REPLACE INTO Component (id, name)
VALUES (1, 'Root')
SQL;
        $this->prepare($createRootComponentQuery)->execute();
    }

    private function initializeCategoryTable()
    {
        $createCategoryTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS Category
(
  id INTEGER PRIMARY KEY NOT NULL,
  FOREIGN KEY (id) REFERENCES Component (id)
)
SQL;
        $this->prepare($createCategoryTableQuery)->execute();

        $createRootCategoryQuery = <<<SQL
INSERT OR REPLACE INTO Category (id)
VALUES (1)
SQL;
        $this->prepare($createRootCategoryQuery)->execute();
        Category::$rootCategory = new Category('Root');
        Category::$rootCategory->id = 1;
    }

    private function initializeFeedTable()
    {
        $createFeedTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS Feed
(
  id         INTEGER PRIMARY KEY NOT NULL,
  source     TEXT                NOT NULL,
  lastUpdate DATETIME            NOT NULL,
  FOREIGN KEY (id) REFERENCES Component (id)
)
SQL;
        $this->prepare($createFeedTableQuery)->execute();
    }

    private function initializeEntryTable()
    {
        $createEntryTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS Entry
(
  id              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  feedId          INTEGER                           NOT NULL,
  title           TEXT                              NOT NULL,
  summary         TEXT                              NOT NULL,
  location        TEXT                              NOT NULL,
  publicationDate DATETIME                          NOT NULL,
  read            BOOLEAN                           NOT NULL,
  saved           BOOLEAN                           NOT NULL,
  FOREIGN KEY (feedId) REFERENCES Feed (id)
)
SQL;
        $this->prepare($createEntryTableQuery)->execute();
    }

    public function executeQuery($query, array $parameters = [])
    {
        $stmt = $this->prepare($query);
        foreach ($parameters as $name => $value) {
            $stmt->bindValue(':'.$name, $value);
        }
        $success = $stmt->execute();

        if (!$success) {
            throw new QueryExecutionFailure();
        }

        return $stmt;
    }
}
