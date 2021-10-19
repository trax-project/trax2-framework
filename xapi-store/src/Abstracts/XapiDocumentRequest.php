<?php

namespace Trax\XapiStore\Abstracts;

use Trax\XapiStore\XapiRequest;

abstract class XapiDocumentRequest extends XapiRequest
{
    /**
     * Return the property name used to identify a document.
     *
     * @return string
     */
    abstract public static function identifier(): string;
}
