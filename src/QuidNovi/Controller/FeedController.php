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

namespace src\QuidNovi\Controller;

use QuidNovi\Controller\AbstractController;

class FeedController extends AbstractController
{
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
                $this->subscribe();
            });

            $app->patch('/:id', function ($id) use ($app) {
                $name = $app->request->params('name');
                if (null !== $name) {
                    $this->rename($id, $name);
                }
            });

            $app->delete('/:id', function ($id) use ($app) {
                $this->unsubscribe($id);
            });
        });
    }

    public function findAll()
    {
    }

    public function find($id)
    {
    }

    public function subscribe()
    {
    }

    public function unsubscribe($id)
    {
    }

    private function rename($id, $name)
    {
    }
}
