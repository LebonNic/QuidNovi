<?php

namespace QuidNovi\Specification;

use QuidNovi\Entry;

class AndSpecification implements EntrySpecification
{
    private $aSpecification;
    private $anotherSpecification;

    public function __construct(EntrySpecification $aSpecification, EntrySpecification $anotherSpecification)
    {
        $this->aSpecification = $aSpecification;
        $this->anotherSpecification = $anotherSpecification;
    }

    public function isSatisfiedBy(Entry $entry)
    {
        return $this->aSpecification->isSatisfiedBy($entry) && $this->anotherSpecification->isSatisfiedBy($entry);
    }
}