<?php

/**
 * The MIT License (MIT).
 *
 * Copyright (c) 2015 Antoine Colmard
 *               2015 Nicolas Prugne
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace QuidNovi\Model;

class Category extends Component
{
    /**
     * @var array
     */
    private $components;

    private $componentsClosure;

    public static $rootCategory = null;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->id = null;
    }

    public function addComponent(Component $component)
    {
        $component->setContainer($this);
        array_push($this->getComponents(), $component);
    }

    public function removeComponent(Component $component)
    {
        $components = $this->getComponents();
        $key = array_search($component, $components, true);
        if ($key !== false) {
            unset($components[$key]);
        }
    }

    public function setComponentsClosure($componentsClosure)
    {
        $this->componentsClosure = $componentsClosure;
    }

    public function &getComponents()
    {
        if (!isset($this->components)) {
            $closure = $this->componentsClosure;
            if (is_callable($closure)) {
                $this->components = $closure();
            } else {
                $this->components = array();
            }
        }

        return $this->components;
    }
}
