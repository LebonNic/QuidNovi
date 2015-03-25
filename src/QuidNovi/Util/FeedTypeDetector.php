<?php

/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 25/03/2015
 * Time: 20:04.
 */

namespace QuidNovi\Util;

use QuidNovi\Model\Feed;

class FeedTypeDetector
{
    public static function getFeedType(Feed $feed)
    {
        $feedXML = new \SimpleXMLElement(file_get_contents($feed->getSource()));
        if (null !== $feedXML->rss) {
            return FeedType::$RSS;
        }
        if (null !== $feedXML->feed) {
            return FeedType::$ATOM;
        }
    }
}
