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

namespace QuidNovi;

use Exception;
use QuidNovi\Controller\CategoryController;
use QuidNovi\Controller\EntryController;
use QuidNovi\Controller\FeedController;
use QuidNovi\DataSource\DataSource;
use Slim\Slim;

/**
 * Application front controller and error handler.
 */
class QuidNovi extends Slim
{
    public function __construct()
    {
        parent::__construct();

        $this->setupConfiguration();
        $this->setupErrorHandling();
        $this->setupRoutes();
    }

    /**
     * Setup application global configuration.
     */
    private function setupConfiguration()
    {
        date_default_timezone_set('Zulu');
        $this->config('templates.path', __DIR__.'/../../web');
        $this->config('database.path', __DIR__.'/../../database.sqlite3');
        $this->config('debug', true);
    }

    /**
     * Setup logging on error handling.
     */
    private function setupErrorHandling()
    {
        $this->error(function (Exception $ex) {
            $this->getLog()->alert($ex);
        });
    }

    /**
     * Setup application routes.
     */
    private function setupRoutes()
    {
        // 404 redirection
        $this->notFound(function () {
            $this->render('404.html');
        });

        // Redirect root to index.html (angular application)
        $this->get('/', function () {
            $this->render('index.html');
        });

        $this->setupAPIRoutes();
    }

    /**
     * Setup routes for application's API.
     */
    private function setupAPIRoutes()
    {
        new EntryController($this);
        new CategoryController($this);
        new FeedController($this);
    }

    /**
     * Get DataSource.
     *
     * @return DataSource
     */
    public function getDataSource()
    {
        $databasePath = $this->config('database.path');
        $dataSource = new DataSource('sqlite:'.$databasePath);

        return $dataSource;
    }
}
