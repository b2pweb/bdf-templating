<?php

namespace Bdf\Templating\Filters;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Filters
 */
class StripNewLinesFilterTest extends TestCase
{
    /**
     *
     */
    public function test_filter()
    {
        $filter = new StripNewLinesFilter();

        $this->assertEquals("string with char", $filter->filter("string \nwith char\n\r"));
    }
}
