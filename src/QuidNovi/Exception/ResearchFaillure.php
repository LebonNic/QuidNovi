<?php
/**
 * Created by PhpStorm.
 * User: LebonNic
 * Date: 27/03/2015
 * Time: 14:33
 */

namespace QuidNovi\Exception;


use Exception;

class ResearchFaillure extends Exception{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}