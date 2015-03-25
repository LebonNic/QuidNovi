<?php

namespace QuidNovi\Controller;

use QuidNovi\Finder\EntryFinder;
use QuidNovi\QuidNovi;

class EntryController
{
    /**
     * @var QuidNovi
     */
    private $app;

    public function __construct(QuidNovi $app)
    {
        $this->app = $app;
        $this->createRoutes();
    }

    private function createRoutes()
    {
        $app = $this->app;

        $app->get('/entries', function () {
            $this->findAll();
        });

        $app->get('/entries/:id', function ($id) {
            $this->find($id);
        });

        $app->patch('/entries/:id', function ($id) use ($app) {
            $read = $app->request->params('read');
            $saved = $app->request->params('saved');
            if (null !== $read) {
                $this->markAsRead($id, $read);
            }
            if (null !== $saved) {
                $this->markAsSaved($id, $saved);
            }
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

    }

    public function markAsSaved($id, $saved)
    {

    }
}