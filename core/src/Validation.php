<?php

namespace Trax\Core;

class Validation
{
    /**
     * Validate a data against the given rules.
     *
     * @return void
     */
    public static function check($data, $rules)
    {
        return app('validator')->make(['data' => $data], ['data' => $rules])->passes();
    }
}
