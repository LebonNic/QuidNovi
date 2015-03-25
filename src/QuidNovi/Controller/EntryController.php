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
namespace QuidNovi\Controller;

use QuidNovi\Finder\EntryFinder;
use QuidNovi\Mapper\EntryMapper;

class EntryController extends AbstractController
{
    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/entries', function() use ($app) {
            $app->get('/', function () {
                $this->findAll();
            });

            $app->get('/:id', function ($id) {
                $this->find($id);
            });

            $app->patch('/:id', function ($id) use ($app) {
                $read = $app->request->params('read');
                $saved = $app->request->params('saved');
                if (null !== $read) {
                    $this->markAsRead($id, $read);
                }
                if (null !== $saved) {
                    $this->markAsSaved($id, $saved);
                }
            });
        });
    }

    public function find($id)
    {
        $connection = $this->app->getConnection();
        $finder = new EntryFinder($connection);
        $entry = $finder->find($id);
        $connection = null;
        $response = $this->app->response;

        if ($entry) {
            $response->setStatus(200);
            $response->setBody(json_encode($entry));
        } else {
            $response->setStatus(404);
        }
    }

    public function findAll()
    {
        $connection = $this->app->getConnection();
        $finder = new EntryFinder($connection);
        $entries = $finder->findAll();
        $connection = null;
        $response = $this->app->response;

        $response->setStatus(200);
        $response->setBody(json_encode($entries));
    }

    public function markAsRead($id, $read)
    {
        $connection = $this->app->getConnection();
        $finder = new EntryFinder($connection);
        $entry = $finder->find($id);
        if ('true' === $read) {
            $this->markEntryAsRead($entry);
        } else if ('false' === $read) {
            $this->markEntryAsUnread($entry);
        }
        $mapper = new EntryMapper($connection);
        $mapper->persist($entry);
    }

    public function markAsSaved($id, $saved)
    {

    }
}