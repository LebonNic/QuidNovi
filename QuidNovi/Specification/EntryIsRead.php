<?php
/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 23/03/2015
 * Time: 14:03.
 */
namespace QuidNovi\Specification;

use QuidNovi\Entry;

class EntryIsRead implements EntrySpecification
{
    public function isSatisfiedBy(Entry $entry)
    {
        return $entry->isRead();
    }
}
