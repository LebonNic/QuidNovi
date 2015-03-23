<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 23/03/2015
 * Time: 15:17
 */

namespace QuidNovi\Specification;


use QuidNovi\Entry;

class NotSpecification implements EntrySpecification
{
    private $aSpecification;

    public function __construct(EntrySpecification $aSpecification)
    {
        $this->aSpecification = $aSpecification;
    }

    public function isSatisfiedBy(Entry $entry)
    {
        return !$this->aSpecification->isSatisfiedBy($entry);
    }
}