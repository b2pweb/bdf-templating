<?php

namespace Bdf\Templating\Helpers;

class TestHelper extends AbstractHelper
{
    private $foo;
    private $isPrepared = false;

    public function getName()
    {
        return "testHelper";
    }

    public function foo()
    {
        return $this->foo;
    }

    public function isPrepared()
    {
        return $this->isPrepared;
    }

    public function initialize()
    {
        $this->foo = "bar";
    }

    public function prepare()
    {
        $this->isPrepared = true;
    }
}