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

use QuidNovi\DTO\EntryDTO;
use QuidNovi\Finder\EntryFinder;
use QuidNovi\Mapper\EntryMapper;
use QuidNovi\Model\Entry;
use QuidNovi\QuidNovi;
use QuidNovi\Specification\IsInCategory;
use QuidNovi\Specification\IsInFeed;
use QuidNovi\Specification\IsRead;
use QuidNovi\Specification\IsSaved;
use QuidNovi\Specification\TrueSpecification;

/**
 * Class EntryController retrieves news entries and edits user specific
 * attributes (read/saved).
 */
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

    /**
     * Create a new entry controller for given application.
     *
     * @param QuidNovi $app
     */
    public function __construct(QuidNovi $app)
    {
        parent::__construct($app);
        $dataSource = $this->app->getDataSource();
        $this->mapper = new EntryMapper($dataSource);
        $this->finder = new EntryFinder($dataSource);
    }

    /**
     * Create routes for entry controller :
     * - GET    /entries       => get all entries
     * - GET    /entries/:id   => get entry with id
     * - PATCH  /entries/:id   => mark entry as read/unread or saved/unsaved.
     */
    public function createRoutes()
    {
        $app = $this->app;

        $app->group('/entries', function () use ($app) {
            $app->get('/', function () {
                $read = $this->request->params('read');
                $saved = $this->request->params('saved');
                $feed = $this->request->params('feed');
                $category = $this->request->params('category');

                $this->findAll($read, $saved, $feed, $category);
            });

            $app->get('/:id', function ($id) {
                $this->find($id);
            });

            $app->patch('/:id', function ($id) {
                $json = json_decode($this->request->getBody(), true);
                $read = isset($json['read']) ? $json['read'] : null;
                $saved = isset($json['saved']) ? $json['saved'] : null;

                if (null !== $read) {
                    $this->markAsRead($id, $read);
                }
                if (null !== $saved) {
                    $this->markAsSaved($id, $saved);
                }
            });
        });
    }

    /**
     * Get entry with given id. If id does not match any entry, application
     * halts and returns a 404 status code. Otherwise returns 200.
     *
     * @param $id int entry id.
     */
    public function find($id)
    {
        $entry = $this->getEntry($id);
        $this->buildResponse(200, new EntryDTO($entry));
    }

    /**
     * Get all entries. Filter can be defined on result set.
     * Returns 200.
     *
     * @param $read bool filter on entries read attribute.
     * @param $saved bool filter on entries saved attribute.
     * @param $feed int filter on entries feed.
     * @param $category bool filter on entries category.
     */
    public function findAll($read, $saved, $feed, $category)
    {
        $specification = $this->getEntrySpecification($read, $saved, $feed, $category);
        $entries = $this->finder->findSatisfying($specification);

        $entriesDTO = [];
        foreach ($entries as $entry) {
            array_push($entriesDTO, new EntryDTO($entry));
        }

        $this->buildResponse(200, $entriesDTO);
    }

    private function getEntrySpecification($read, $saved, $feed, $category)
    {
        $specification = new TrueSpecification();
        $readSpecification = $this->getReadSpecification($read);
        $savedSpecification = $this->getSavedSpecification($saved);
        $feedSpecification = $this->getFeedSpecification($feed);
        $categorySpecification = $this->getCategorySpecification($category);

        return $specification
            ->intersect($readSpecification)
            ->intersect($savedSpecification)
            ->intersect($feedSpecification)
            ->intersect($categorySpecification);
    }

    private function getReadSpecification($read)
    {
        if (null === $read) {
            return new TrueSpecification();
        }
        if ('true' === $read) {
            return new IsRead();
        }
        if ('false' === $read) {
            return (new IsRead())->invert();
        }

        return new TrueSpecification();
    }

    private function getSavedSpecification($saved)
    {
        if (null === $saved) {
            return new TrueSpecification();
        }
        if ('true' === $saved) {
            return new IsSaved();
        }
        if ('false' === $saved) {
            return (new IsSaved())->invert();
        }

        return new TrueSpecification();
    }

    private function getFeedSpecification($feedId)
    {
        if (null === $feedId) {
            return new TrueSpecification();
        }

        return new IsInFeed(intval($feedId));
    }

    private function getCategorySpecification($categoryId)
    {
        if (null === $categoryId) {
            return new TrueSpecification();
        }

        return new IsInCategory(intval($categoryId));
    }

    /**
     * Mark entry with given id as read. If id does not match any entry, application
     * halts and returns a 404 status code. Otherwise returns 204.
     *
     * @param $id int entry id.
     * @param $read bool indicates id the entry must be marked as read or not.
     */
    public function markAsRead($id, $read)
    {
        $entry = $this->getEntry($id);
        if (true === $read) {
            $entry->markAsRead();
        } elseif (false === $read) {
            $entry->markAsUnread();
        }
        $this->mapper->persist($entry);
        $this->response->setStatus(204);
    }

    /**
     * Mark entry with given id as saved. If id does not match any entry, application
     * halts and returns a 404 status code. Otherwise returns 204.
     *
     * @param $id int entry id.
     * @param $saved bool indicates id the entry must be marked as saved or not.
     */
    public function markAsSaved($id, $saved)
    {
        $entry = $this->getEntry($id);
        if (true === $saved) {
            $entry->markAsSaved();
        } elseif (false === $saved) {
            $entry->markAsUnsaved();
        }
        $this->mapper->persist($entry);
        $this->response->setStatus(204);
    }

    /**
     * Get entry with given id. If id does not match any entry, application halts
     * and returns a 404 status code.
     *
     * @param $id int entry id.
     *
     * @return Entry entry with given id.
     */
    private function getEntry($id)
    {
        $entry = $this->finder->find($id);
        if (null === $entry) {
            $this->app->halt(404, 'Entry '.$id.' does not exist.');
        }

        return $entry;
    }
}
