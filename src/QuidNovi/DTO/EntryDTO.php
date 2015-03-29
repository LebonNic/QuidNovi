<?php

/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 27/03/2015
 * Time: 18:23.
 */

namespace QuidNovi\DTO;

use QuidNovi\Model\Entry;

class EntryDTO
{
    public $id;
    public $title;
    public $summary;
    public $location;
    public $feedId;
    public $publicationDate;
    public $read;
    public $saved;

    public function __construct(Entry $entry)
    {
        $this->id = $entry->id;
        $this->title = $entry->title;
        $this->summary = $entry->summary;
        $this->location = $entry->getLocation();
        $this->feedId = $entry->feed->id;
        $this->publicationDate = $entry->getPublicationDate()->format('Y-m-dTH:i:s');
        $this->read = $entry->isRead();
        $this->saved = $entry->isSaved();
    }
}
