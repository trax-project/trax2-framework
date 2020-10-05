<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Trax\XapiStore\Exceptions\XapiBadRequestException;

trait PreventUnkownInputs
{
    /**
     * Prevent the use of unknown inputs in the request.
     * Return the known inputs.
     *
     * @param \Illuminate\Http\Request  $request;
     * @param array  $knownInputs;
     * @return  array
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function preventUnkownInputs(Request $request, array $knownInputs): array
    {
        // Get inputs.
        if ($request->isJson()) {
            $inputs = $request->query();
        } else {
            $inputs = $request->all();
        }

        // Check them.
        foreach ($inputs as $key => $value) {
            if (!in_array($key, $knownInputs)) {
                throw new XapiBadRequestException("A request input is not allowed: [$key].");
            }
        }
        return $inputs;
    }
}
