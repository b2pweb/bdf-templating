<?php

namespace Bdf\Templating\Extensions;

/**
 * @deprecated use json_encode/json_decode directly
 */
trait Encoder
{
    /**
     * Encode string to json
     *
     * @param mixed $encode
     * @param int   $options
     *
     * @return string
     */
    public function json($encode, $options = 0)
    {
        return json_encode($encode, $options);
    }
    
    /**
     * Decode json string
     *
     * @param string  $json
     * @param boolean $type
     * @param int     $options
     * @param int     $depth
     *
     * @return false|string
     */
    public function jsonDecode($json, $type = false, $options = 0, $depth = 512)
    {
        return json_decode($json, $type, $depth, $options);
    }
}
