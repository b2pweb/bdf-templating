<?php

namespace Bdf\Templating\Bundle\_files\helpers;

use Bdf\Templating\Helpers\HelperInterface;

class MyHelper implements HelperInterface
{
    public function getName(): string
    {
        return 'myHelper';
    }

    public function test(): string
    {
        return 'test';
    }
}
