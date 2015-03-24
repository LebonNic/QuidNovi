<?php

namespace QuidNovi\Specification;

use QuidNovi\Entry;

interface EntrySpecification
{
    public function isSatisfiedBy(Entry $entry);
}
