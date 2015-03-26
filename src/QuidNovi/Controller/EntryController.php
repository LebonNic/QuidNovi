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
use QuidNovi\QuidNovi;

class EntryController extends AbstractController
{
    /**
     * @var EntryMapper
     */
    private $mapper;
    /**
     * @var EntryFinder
     */
    private $finder;

    function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $this->app->getDataSource();
        $this->mapper = new EntryMapper($dataSource);
        $this->finder = new EntryFinder($dataSource);
    }


    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/entries', function () use ($app) {
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
        $entry = $this->finder->find($id);

        if (null === $entry) {
            $this->app->halt(404, 'Entry does not exist.');
        }
        $this->buildResponse(200, $entry);
    }

    public function findAll()
    {
        $entries = $this->finder->findAll();
        $this->buildResponse(200, $entries);
    }

    public function markAsRead($id, $read)
    {
        $entry = $this->finder->find($id);
        // TODO: Mark entry as read/unread
        $this->mapper->persist($entry);
        $this->response->setStatus(204);
    }

    public function markAsSaved($id, $saved)
    {
        $entry = $this->finder->find($id);
        // TODO: Mark entry as saved/unsaved
        $this->mapper->persist($entry);
        $this->response->setStatus(204);
    }
}
