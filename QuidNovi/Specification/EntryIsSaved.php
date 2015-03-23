<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 23/03/2015
 * Time: 14:06.
 */
namespace QuidNovi\Specification;

use QuidNovi\Entry;

class EntryIsSaved implements EntrySpecification
{
    public function isSatisfiedBy(Entry $entry)
    {
        return $entry->isSaved();
    }
}
