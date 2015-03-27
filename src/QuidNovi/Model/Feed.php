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

class Feed extends Component
{
    /**
     * @var string
     */
    private $source;
    /**
     * @var \DateTime
     */
    public $lastUpdate;
    /**
     * @var array
     */
    private $entries;

    public function __construct($name, $source, $lastUpdate, $entries = array())
    {
        parent::__construct($name);
        $this->id = null;
        $this->source = $source;
        $this->lastUpdate = $lastUpdate;
        $this->entries = $entries;
    }

    public function addEntry($entry)
    {
        $entry->feed = $this;
        array_push($this->entries, $entry);
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getEntries()
    {
        return $this->entries;
    }
}
