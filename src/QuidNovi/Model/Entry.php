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

namespace QuidNovi\Model;

class Entry
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $summary;
    /**
     * @var string
     */
    private $location;
    /**
     * @var \DateTime
     */
    private $publicationDate;
    /**
     * @var Feed
     */
    public $feed;
    /**
     * @var bool
     */
    private $read;
    /**
     * @var bool
     */
    private $saved;

    public function __construct($title, $summary, $location, $publicationDate, $read = false, $saved = false)
    {
        $this->id = null;
        $this->title = $title;
        $this->summary = $summary;
        $this->location = $location;
        $this->publicationDate = $publicationDate;
        $this->feed = null;
        $this->read = $read;
        $this->saved = $saved;
    }

    public function markAsSaved()
    {
        $this->saved = true;
    }

    public function markAsUnsaved()
    {
        $this->saved = false;
    }

    public function markAsRead()
    {
        $this->read = true;
    }

    public function markAsUnread()
    {
        $this->read = false;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    public function isRead()
    {
        return $this->read;
    }

    public function isSaved()
    {
        return $this->saved;
    }
}
