<?php

namespace Trax\XapiStore\Stores\About;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class XapiAboutController extends Controller
{
    /**
     * Get resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        return response()->json([
            'version' => ['1.0.3'],
        ]);
    }
}
