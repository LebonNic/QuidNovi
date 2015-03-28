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

use QuidNovi\Finder\FeedFinder;
use QuidNovi\Mapper\FeedMapper;
use QuidNovi\Model\Feed;
use QuidNovi\QuidNovi;
use QuidNovi\DTO\FeedDTO;

class FeedController extends AbstractController
{
    private $mapper;
    private $finder;

    public function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $app->getDataSource();
        $this->mapper = new FeedMapper($dataSource);
        $this->finder = new FeedFinder($dataSource);
    }

    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/feeds', function () use ($app) {
            $app->get('/', function () {
                $this->findAll();
            });

            $app->get('/:id', function ($id) {
                $this->find($id);
            });

            $app->post('/', function () {
                $json = json_decode($this->request->getBody(), true);
                $name = isset($json['name']) ? $json['name'] : null;
                $source = isset($json['source']) ? $json['source'] : null;
                $this->subscribe($name, $source);
            });

            $app->patch('/:id', function ($id) {
                $json = json_decode($this->request->getBody(), true);
                $name = isset($json['name']) ? $json['name'] : null;
                if (null !== $name) {
                    $this->rename($id, $name);
                }
            });

            $app->delete('/:id', function ($id) {
                $this->unsubscribe($id);
            });
        });
    }

    public function findAll()
    {
        $feeds = $this->finder->findAll();
        $feedsDTO = [];
        foreach ($feeds as $feed) {
            array_push($feedsDTO, new FeedDTO($feed));
        }
        $this->buildResponse(200, $feedsDTO);
    }

    public function find($id)
    {
        $feed = $this->getFeed($id);
        $this->buildResponse(200, new FeedDTO($feed));
    }

    public function subscribe($name, $source)
    {
        if (null === $name) {
            $this->app->halt(400, 'Feed name is required.');
        }
        if (null === $source) {
            $this->app->halt(400, 'Feed source is required.');
        }
        // TODO: check that source does not already exist.
        $source = filter_var($source, FILTER_SANITIZE_URL);
        if (!filter_var($source, FILTER_VALIDATE_URL) === true) {
            $this->app->halt(400, 'Feed source is not a valid url.');
        }

        $this->app->getLog()->info('Subscribing to feed ' . $source);

        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $feed = new Feed($source, $source, $yesterday);
        $this->mapper->persist($feed);
        $this->buildResponse(201, [
            'uri' => '/feeds/' . $feed->id,
        ]);
    }

    public function unsubscribe($id)
    {
        $feed = $this->getFeed($id);
        $this->mapper->remove($feed);
        $this->response->setStatus(204);
    }

    public function rename($id, $name)
    {
        if (null === $name) {
            $this->app->halt(400, 'Feed name is required.');
        }
        $feed = $this->getFeed($id);
        $feed->name = $name;
        $this->mapper->persist($feed);
        $this->response->setStatus(204);
    }

    private function getFeed($id)
    {
        $feed = $this->finder->find($id);
        if (null === $feed) {
            $this->app->halt(404, 'Feed ' . $id . ' does not exist.');
        }

        return $feed;
    }
}
