<?php

namespace Trax\Auth\Controllers;

use Illuminate\Http\Request;

trait ControllerIncludesData
{
    /**
     * Validate the include data settings of a request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validateIncludeRequest(Request $request)
    {
        return $request->validate([
            'include' => 'nullable|array',
        ]);
    }

    /**
     * Include data and get the JSON response.
     *
     * @param  array  $responseData
     * @param  array  $include
     * @return \Illuminate\Http\Response
     */
    protected function responseWithIncludedData($responseData, array $include = ['include' => []])
    {
        $includedData = [];
        if (isset($include['include'])) {
            foreach ($include['include'] as $name) {
                $included = $this->includeData($name);
                if (!is_null($included)) {
                    $includedData[$name] = $included;
                }
            }
        }
        if (!empty($includedData)) {
            $responseData['included'] = $includedData;
        }
        return response()->json($responseData);
    }

    /**
     * Get response complementary data.
     *
     * @param string  $name
     * @return mixed
     */
    protected function includeData(string $name)
    {
        return null;
    }
}
