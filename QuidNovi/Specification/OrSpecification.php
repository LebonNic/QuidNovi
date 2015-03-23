<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 23/03/2015
 * Time: 14:27
 */

namespace QuidNovi\Specification;

use QuidNovi\Entry;

class OrSpecification implements EntrySpecification
{
    private $oneSpecification;
    private $anotherSpecification;

    public function __construct(EntrySpecification $oneSpecification, EntrySpecification $anotherSpecification)
    {
        $this->oneSpecification = $oneSpecification;
        $this->anotherSpecification = $anotherSpecification;
    }

    public function isSatisfiedBy(Entry $entry)
    {
        return $this->oneSpecification->isSatisfiedBy($entry) || $this->anotherSpecification->isSatisfiedBy($entry);
    }
}