<?php

namespace RestBundle\Utils;

class Random
{

    function randomAlphaNumeric($length)
    {
        $pool = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $key = "";
        for ($i = 0; $i < $length; $i++)
        {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }

}
