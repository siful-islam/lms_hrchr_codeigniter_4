<?php

namespace App\Models;

class LegacyOutput
{
    public function set_header($header)
    {
        $pos = strpos($header, ':');
        if ($pos !== false) {
            service('response')->setHeader(substr($header, 0, $pos), trim(substr($header, $pos + 1)));
        }
    }
    public function set_content_type($mime) { service('response')->setContentType($mime); return $this; }
    public function set_output($output) { service('response')->setBody($output); return $this; }
}


