<?php

namespace Bdf\Templating\Filters;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Filters
 */
class CallableFilterTest extends TestCase
{
    /**
     *
     */
    public function test_filter()
    {
        $filter = new CallableFilter(function($content) {return $content."bar";});

        $this->assertEquals("foobar", $filter->filter("foo"));
    }
}
