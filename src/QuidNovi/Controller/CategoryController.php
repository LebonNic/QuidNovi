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

use QuidNovi\DTO\CategoryDTO;
use QuidNovi\Finder\CategoryFinder;
use QuidNovi\Mapper\CategoryMapper;
use QuidNovi\Model\Category;
use QuidNovi\QuidNovi;

/**
 * Class CategoryController retrieves categories as full hierarchical representation.
 * It also allows to create, rename and delete categories.
 */
class CategoryController extends AbstractController
{
    /**
     * @var CategoryMapper
     */
    private $mapper;

    /**
     * @var CategoryFinder
     */
    private $finder;

    /**
     * Create a new category controller for given application.
     *
     * @param QuidNovi $app
     */
    public function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $this->app->getDataSource();
        $this->mapper = new CategoryMapper($dataSource);
        $this->finder = new CategoryFinder($dataSource);
    }

    /**
     * Create routes for category controller :
     * - POST   /categories     => create a new category
     * - GET    /categories     => get a hierarchical representation of categories and feeds
     * - GET    /categories/:id => get a hierarchical representation of given category
     * - PATCH  /categories/:id => rename category or move category to container
     * - DELETE /categories/:id => delete category and any sub categories and feeds.
     */
    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/categories', function () use ($app) {
            $app->post('/', function () {
                $json = json_decode($this->request->getBody(), true);
                $name = isset($json['name']) ? $json['name'] : null;
                $containerId = isset($json['containerId']) ? $json['containerId'] : null;
                $this->create($name, $containerId);
            });

            $app->get('/:id', function ($id) {
                $this->find($id);
            });

            $app->get('/', function () {
                $this->findAll();
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
                $this->delete($id);
            });
        });
    }

    /**
     * Create a new category with given name contained by specified container.
     * If containerId does not match any category, application halts and returns
     * a 404 status code. If name is not specified, halts and returns 400.
     * Otherwise, returns 201.
     *
     * @param $name int category name.
     * @param $containerId int containing category id.
     */
    public function create($name, $containerId)
    {
        if (null === $name) {
            $this->app->halt(400, 'Category name is required.');
        }
        $category = new Category($name);

        // If a containing category is specified, the new category is added to this container
        if (null !== $containerId) {
            $container = $this->getCategory($containerId);
            $category->setContainer($container);
            $container->addComponent($category);
        }

        $this->mapper->persist($category);
        $this->buildResponse(201, [
            'uri' => '/categories/'.$category->id,
        ]);
    }

    /**
     * Get all categories and feeds as a hierarchical representation.
     * Returns 201.
     */
    public function findAll()
    {
        $category = $this->finder->find(1);
        $this->buildResponse(200, new CategoryDTO($category));
    }

    /**
     * Get a subtree of categories and feed structure starting at specified category.
     * If id does not match any category, application halts and returns a 404 status
     * code. Otherwise, returns 200.
     *
     * @param $id int category id.
     */
    public function find($id)
    {
        $category = $this->getCategory($id);
        $this->buildResponse(200, new CategoryDTO($category));
    }

    /**
     * Rename category with given id. If id does not match any category, application
     * halts and returns a 404 status code. If name is not specified, returns 400.
     * Otherwise, returns 204.
     *
     * @param $id int category id.
     * @param $name string category name.
     */
    public function rename($id, $name)
    {
        if (null === $name) {
            $this->app->halt(400, 'Category name is required.');
        }
        $category = $this->getCategory($id);
        $category->name = $name;
        $this->mapper->persist($category);
        $this->response->setStatus(204);
    }

    public function move($id, $containerId) {
        if (null === $containerId) {
            $this->app->halt(400, 'Category container id is required.');
        }
        $category = $this->getCategory($id);
        $container = $this->getCategory($containerId);
        $category->setContainer($container);
        $this->mapper->persist($category);
        $this->response->setStatus(204);
    }

    /**
     * Delete category with given id. If id does not match any category, application
     * halts and returns a 404 status code. Otherwise, returns 204.
     *
     * @param $id int category id.
     */
    public function delete($id)
    {
        $category = $this->getCategory($id);
        $this->mapper->remove($category);
        $this->response->setStatus(204);
    }

    /**
     * Get category with given id. If id does not match any category, application halts
     * and returns a 404 status code.
     *
     * @param $id int category id.
     *
     * @return Category category with given id.
     */
    private function getCategory($id)
    {
        $category = $this->finder->find($id);
        if (null === $category) {
            $this->app->halt(404, 'Category '.$id.' does not exist.');
        }

        return $category;
    }
}
