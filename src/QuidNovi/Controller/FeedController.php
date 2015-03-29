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

use QuidNovi\Finder\CategoryFinder;
use QuidNovi\Finder\FeedFinder;
use QuidNovi\Mapper\FeedMapper;
use QuidNovi\Model\Feed;
use QuidNovi\QuidNovi;
use QuidNovi\DTO\FeedDTO;

/**
 * Class CategoryController retrieves feeds and allows user to subscribe, unsubscribe
 * and rename feeds.
 */
class FeedController extends AbstractController
{
    /**
     * @var FeedMapper
     */
    private $mapper;
    /**
     * @var FeedFinder
     */
    private $finder;

    /**
     * Create a new feed controller for given application.
     *
     * @param QuidNovi $app
     */
    public function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $app->getDataSource();
        $this->mapper = new FeedMapper($dataSource);
        $this->finder = new FeedFinder($dataSource);
    }

    /**
     * Create routes for feed controller :
     * - POST   /feeds      => subscribe a new feed
     * - GET    /feeds      => get all feeds
     * - GET    /feeds/:id  => get specified feed
     * - PATCH  /feeds/:id  => rename feed or move feed to container
     * - DELETE /feeds/:id  => unsubscribe a feed.
     */
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
                $containerId = isset($json['containerId']) ? $json['containerId'] : 1;
                $this->subscribe($name, $source, $containerId);
            });

            $app->patch('/:id', function ($id) {
                $json = json_decode($this->request->getBody(), true);
                $name = isset($json['name']) ? $json['name'] : null;
                $containerId = isset($json['containerId']) ? $json['containerId'] : null;
                if (null !== $containerId) {
                    $this->move($id, $containerId);
                }
                if (null !== $name) {
                    $this->rename($id, $name);
                }
            });

            $app->delete('/:id', function ($id) {
                $this->unsubscribe($id);
            });
        });
    }

    /**
     * Get all feeds.
     */
    public function findAll()
    {
        $feeds = $this->finder->findAll();
        $feedsDTO = [];
        foreach ($feeds as $feed) {
            array_push($feedsDTO, new FeedDTO($feed));
        }
        $this->buildResponse(200, $feedsDTO);
    }

    /**
     * Get feed with given id. If id does not match any feed, application
     * halts and returns a 404 status code.
     *
     * @param $id int feed id.
     */
    public function find($id)
    {
        $feed = $this->getFeed($id);
        $this->buildResponse(200, new FeedDTO($feed));
    }

    /**
     * Subscribe to a new feed. If containerId does not match any category,
     * application halts and returns a 404 status code. If name or source are not
     * specified, returns 400. If source is not a valid url, returns 400.
     * Otherwise, returns 201.
     *
     * @param $name Feed name.
     * @param $source Feed source url.
     * @param $containerId Feed containing category id.
     */
    public function subscribe($name, $source, $containerId)
    {
        if (null === $name) {
            $this->app->halt(400, 'Feed name is required.');
        }
        if (null === $source) {
            $this->app->halt(400, 'Feed source is required.');
        }
        $source = filter_var($source, FILTER_SANITIZE_URL);
        if (!filter_var($source, FILTER_VALIDATE_URL) === true) {
            $this->app->halt(400, 'Feed source is not a valid url.');
        }

        $categoryFinder = new CategoryFinder($this->app->getDataSource());
        $container = $categoryFinder->find($containerId);

        if (null === $container) {
            $this->app->halt(404, 'Container category does not exist.');
        }

        $this->app->getLog()->info('Subscribing to feed '.$source);

        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $feed = new Feed($name, $source, $yesterday);
        $feed->setContainer($container);
        $container->addComponent($feed);
        $this->mapper->persist($feed);
        $this->buildResponse(201, [
            'uri' => '/feeds/'.$feed->id,
        ]);
    }

    /**
     * Delete feed with given id. If id does not match any feed, application
     * halts and returns a 404 status code. Otherwise, returns 204.
     *
     * @param $id int feed id.
     */
    public function unsubscribe($id)
    {
        $feed = $this->getFeed($id);
        $this->mapper->remove($feed);
        $this->response->setStatus(204);
    }

    /**
     * Rename feed with given id. If id does not match any feed, application
     * halts and returns a 404 status code. If name is not specified, returns 400.
     * Otherwise, returns 204.
     *
     * @param $id int feed id.
     * @param $name string feed name.
     */
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

    /**
     * Move feed to given container. If id does not match any feed, application halts and
     * returns a 404 status code. If containerId is not specified, returns 400. If container
     * id does not match any category, returns 404. Otherwise, returns 204.
     *
     * @param $id int category id.
     * @param $containerId int container id.
     */
    public function move($id, $containerId)
    {
        if (null === $containerId) {
            $this->app->halt(400, 'Category container id is required.');
        }
        $feed = $this->getFeed($id);
        $categoryFinder = new CategoryFinder($this->app->getDataSource());
        $container = $categoryFinder->find($containerId);
        if (null === $container) {
            $this->app->halt(400, 'Category container does not exist.');
        }
        $feed->setContainer($container);
        $this->mapper->persist($feed);
        $this->response->setStatus(204);
    }

    /**
     * Get feed with given id. If id does not match any feed, application halts
     * and returns a 404 status code.
     *
     * @param $id int feed id.
     *
     * @return Feed feed with given id.
     */
    private function getFeed($id)
    {
        $feed = $this->finder->find($id);
        if (null === $feed) {
            $this->app->halt(404, 'Feed '.$id.' does not exist.');
        }

        return $feed;
    }
}
