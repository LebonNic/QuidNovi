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

namespace QuidNovi\Repository;

use PDO;
use QuidNovi\Model\Entry;
use QuidNovi\Specification\EntrySpecification;

class EntryRepository
{
    private $entries = array();
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        return $this->entries[$id];
    }

    public function findAll()
    {
        return $this->entries;
    }

    public function findSatisfying(EntrySpecification $specification)
    {
        $satisfyingEntries = array();

        foreach ($this->entries as $entry) {
            if ($specification->isSatisfiedBy($entry)) {
                array_push($satisfyingEntries, $entry);
            }
        }
    }

    public function add(Entry $entry)
    {
        if (null !== $entry) {
            $id = $entry->id;
            $this->entries[$id] = $entry;
        }
    }

    public function remove(Entry $entry)
    {
        if (null !== $entry) {
            $id = $entry->id;
            unset($this->entries[$id]);
        }
    }
}