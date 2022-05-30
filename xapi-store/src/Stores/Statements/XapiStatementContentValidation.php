<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Http\Request;
use Trax\XapiStore\Traits\AcceptJsonRequests;
use Trax\XapiStore\Traits\AcceptMultipartRequests;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiValidation\Statement;

trait XapiStatementContentValidation
{
    use AcceptJsonRequests, AcceptMultipartRequests;

    /**
     * Validate a POST request content.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validatePostRequestContent(Request $request)
    {
        list($statements, $attachments) = $this->validateRequestContent($request);
        if (!config('trax-xapi-store.processing.disable_validation', false)) {
            Statement::validateStatementsAndAttachments($statements, $attachments);
        }
        return [$statements, $attachments];
    }

    /**
     * Validate a PUT request content.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validatePutRequestContent(Request $request)
    {
        list($statements, $attachments) = $this->validatePostRequestContent($request);

        if (is_array($statements)) {
            throw new XapiBadRequestException('Can not PUT a batch of statements. The POST method should be used.');
        }
        
        if (isset($statements->id) && $statements->id != $request->input('statementId')) {
            throw new XapiBadRequestException('The id of the statement to PUT does not match with the given statementId param.');
        }
        
        return [$statements, $attachments];
    }

    /**
     * Validate a request content and return the statements and attachments in an array.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validateRequestContent(Request $request): array
    {
        if ($parts = $this->validateMultipartRequest($request)) {
            return $this->validateStatementMultiparts($parts);
        } else {
            return [$this->validateJsonRequest($request), []];
        }
    }

    /**
     * Validate statement multiparts and return the statements and attachments in an array.
     *
     * @param  array  $parts
     * @return  array
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateStatementMultiparts(array $parts): array
    {
        // Content-Type.
        $statements = array_shift($parts);
        if (!isset($statements->contentType) || $statements->contentType != 'application/json') {
            throw new XapiBadRequestException('Invalid Content-Type in multipart request.');
        }
        
        // JSON validity.
        if (!$statements = json_decode($statements->content)) {
            throw new XapiBadRequestException('Invalid JSON content in multipart request.');
        }
        
        // Check attachments.
        foreach ($parts as $attachment) {
            //
            // Attachment hash.
            if (!isset($attachment->sha2)) {
                throw new XapiBadRequestException('Missing X-Experience-API-Hash in multipart request.');
            }
            
            // Attachment encoding.
            if (!isset($attachment->encoding)) {
                throw new XapiBadRequestException('Missing Content-Transfer-Encoding in multipart request.');
            }
            
            // Attachment binary encoding.
            if ($attachment->encoding != 'binary') {
                throw new XapiBadRequestException('None binary Content-Transfer-Encoding in multipart request.');
            }
        }

        return [$statements, $parts];
    }
}
