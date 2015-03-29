<?php

/**
 * Created by PhpStorm.
 * User: colmard
 * Date: 29/03/2015
 * Time: 13:29.
 */

namespace QuidNovi\Specification;

abstract class CompositeSpecification implements Specification
{
    abstract public function isSatisfiedBy($object);

    public function intersect(Specification $specification)
    {
        return new AndSpecification($this, $specification);
    }

    public function union(Specification $specification)
    {
        return new OrSpecification($this, $specification);
    }

    public function invert()
    {
        return new NotSpecification($this);
    }
}
