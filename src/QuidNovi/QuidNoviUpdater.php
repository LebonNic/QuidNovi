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

use QuidNovi\DataSource\DataSource;
use QuidNovi\Finder\FeedFinder;
use QuidNovi\Mapper\FeedMapper;
use QuidNovi\Updater\AtomFeedUpdater;
use QuidNovi\Updater\FeedUpdater;
use QuidNovi\Updater\RSSFeedUpdater;
use QuidNovi\Util\FeedType;
use QuidNovi\Util\FeedTypeDetector;

class QuidNoviUpdater
{
    private $databasePath;

    public function __construct()
    {
        date_default_timezone_set('Zulu');
        $this->databasePath = __DIR__.'\..\..\database.sqlite3';
    }

    /**
     * Update subscribed feeds.
     */
    public function update()
    {
        $dataSource = $this->getDataSource();
        $mapper = new FeedMapper($dataSource);
        $finder = new FeedFinder($dataSource);
        $feeds = $finder->findAll();
        $updaters = $this->getFeedUpdaters();

        foreach ($feeds as $feed) {
            $feedType = FeedTypeDetector::getFeedType($feed);
            /* @var $updater FeedUpdater */
            $updater = $updaters[$feedType];

            if (null !== $updater) {
                $updater->updateFeed($feed);
            } else {
                error_log('Feed '.$feed.' has unknown type. Could not be updated.');
            }
            $feed->lastUpdate = new \DateTime();
            $mapper->persist($feed);
        }
    }

    /**
     * Get Map of FeedUpdaters indexed by FeedType.
     *
     * @return array
     */
    private function getFeedUpdaters()
    {
        $dataSource = $this->getDataSource();

        return [
            FeedType::ATOM => new AtomFeedUpdater($dataSource),
            FeedType::RSS => new RSSFeedUpdater($dataSource),
        ];
    }

    public function getDataSource()
    {
        $dataSource = new DataSource('sqlite:'.$this->databasePath);

        return $dataSource;
    }
}
