<?php

namespace QuidNovi\Repository;

use QuidNovi\Entry;
use QuidNovi\Specification\EntrySpecification;

class EntryRepository
{
    private $entries = array();

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
            $id = $entry->getId();
            $this->entries[$id] = $entry;
        }
    }

    public function remove(Entry $entry)
    {
        if (null !== $entry) {
            $id = $entry->getId();
            unset($this->entries[$id]);
        }
    }
}
