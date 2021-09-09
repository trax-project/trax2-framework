<?php

namespace Trax\XapiStore\Abstracts;

use Trax\XapiStore\XapiRequest;

abstract class XapiDocumentRequest extends XapiRequest
{
    /**
     * Make a request.
     *
     * @param  array  $params
     * @param  object|array  $content
     * @param  string  $contentType
     * @return void
     */
    public function __construct(array $params, $content = null, $contentType = 'application/json')
    {
        parent::__construct($params, ['content' => $content, 'type' => $contentType]);
    }

    /**
     * Return the property name used to identify a document.
     *
     * @return string
     */
    abstract public static function identifier(): string;
}
