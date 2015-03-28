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

    public function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $this->app->getDataSource();
        $this->mapper = new CategoryMapper($dataSource);
        $this->finder = new CategoryFinder($dataSource);
    }

    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/categories', function () use ($app) {
            $app->post('/', function () {
                $name = $this->request->params('name');
                $containerId = $this->request->params('containerId');
                $this->create($name, $containerId);
            });

            $app->get('/:id', function ($id) {
                $this->find($id);
            });

            $app->get('/', function () {
                $this->findAll();
            });

            $app->patch('/:id', function ($id) {
                $name = $this->request->params('name');
                $this->rename($id, $name);
            });

            $app->delete('/:id', function ($id) {
                $this->delete($id);
            });
        });
    }

    public function create($name, $containerId)
    {
        if (null === $name) {
            $this->app->halt(400, 'Category name is required.');
        }
        $category = new Category($name);

        // If a containing category is specified, the new category is added to this container
        if (null !== $containerId) {
            $container = $this->getCategory($containerId);
            $container->addComponent($category);
        }

        $this->mapper->persist($category);
        $this->buildResponse(201, [
            'uri' => '/categories/'.$category->id,
        ]);
    }

    public function find($id)
    {
        $category = $this->getCategory($id);
        $this->buildResponse(200, new CategoryDTO($category));
    }

    public function findAll()
    {
        $categories = $this->finder->findAll();
        $categoriesDTO = [];
        foreach($categories as $category) {
            array_push($categoriesDTO, new CategoryDTO($category));
        }
        $this->buildResponse(200, $categoriesDTO);
    }

    public function rename($id, $name)
    {
        if (null === $name) {
            $this->app->halt(400, 'Category name is required.');
        }
        $category = $this->getCategory($id);
        $category->name = $name;
        $this->mapper->persist($category);
        $this->response->setStatus(406);
    }

    public function delete($id)
    {
        $category = $this->getCategory($id);
        $this->mapper->remove($category);
        $this->response->setStatus(204);
    }

    private function getCategory($id)
    {
        $category = $this->finder->find($id);
        if (null === $category) {
            $this->app->halt(404, 'Category '.$id.' does not exist.');
        }

        return $category;
    }
}
