<?php

/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 27/03/2015
 * Time: 18:42.
 */

namespace QuidNovi\DTO;

use QuidNovi\Model\Feed;

class FeedDTO
{
    public $id;
    public $name;
    public $source;
    public $lastUpdate;

    public function __construct(Feed $feed)
    {
        $this->id = $feed->id;
        $this->name = $feed->name;
        $this->source = $feed->getSource();
        $this->lastUpdate = $feed->lastUpdate->format('Y-m-dTH:m:s');
    }
}
