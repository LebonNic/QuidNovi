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

namespace QuidNovi\Loader;

use PDO;
use QuidNovi\Mapper\EntryMapper;
use QuidNovi\Model\Entry;
use QuidNovi\Model\Feed;
use SimpleXMLElement;

class AtomFeedUpdater implements FeedUpdater
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateFeed(Feed $feed)
    {
        $source = $feed->getSource();
        $feedXML = new SimpleXmlElement(file_get_contents($source));
        foreach ($feedXML->channel->item as $entryXML) {
            $date = new \DateTime($entryXML->updated);
            if ($date > $feed->lastUpdate) {
                $this->insertEntryInFeed($entryXML, $feed);
            }
        }
        $feed->lastUpdate = new \DateTime();
    }

    private function insertEntryInFeed(SimpleXMLElement $entryXML, Feed $feed)
    {
        $entry = new Entry($entryXML->title,
            $entryXML->summary,
            $entryXML->link,
            new \DateTime($entryXML->updated));
        $mapper = new EntryMapper($this->pdo);
        $feed->addEntry($entry);
        $mapper->persist($entry);
    }
}
