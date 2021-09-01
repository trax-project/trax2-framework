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
        $this->validateStatements($statements);
        $this->validateStatementIds($statements);
        $this->validateAttachments($statements, $attachments);
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

    /**
     * Validate Statements.
     *
     * @param  object|array  $statements
     * @return void
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateStatements($statements): void
    {
        $statements = is_array($statements) ? collect($statements) : collect([$statements]);

        // Validate each statement individually.
        $statements->each(function ($statement) {
            Statement::validate($statement);
        });

        // Identify statements to be voided..
        $uuids = $statements->where('verb.id', 'http://adlnet.gov/expapi/verbs/voided')->pluck('object.id');
        if ($uuids->isEmpty()) {
            return;
        }

        // Check if voided statements are also voiding statements.
        $voidedVoiding = $this->repository->whereUuidIn($uuids->toArray())
            ->where('data.verb.id', 'http://adlnet.gov/expapi/verbs/voided');

        if (!$voidedVoiding->isEmpty()) {
            throw new XapiBadRequestException('Voiding statements can not be voided.');
        }
    }
    
    /**
     * Validate statements IDs.
     *
     * @param  object|array  $statements
     * @return void
     */
    protected function validateStatementIds($statements): void
    {
        // Get the ids.
        $statements = is_array($statements) ? collect($statements) : collect([$statements]);
        $ids = $statements->pluck('id')->filter();

        // Duplicates.
        if ($ids->unique()->count() < $ids->count()) {
            throw new XapiBadRequestException("Some statements have the same ID in the batch of statements.");
        }

        // DB check.
        $existing = $this->repository->whereStatementIdIn($ids->unique()->all());
        if (!$existing->isEmpty()) {
            throw new XapiBadRequestException("Statement(s) with similar ID already exist in the database.");
        }
    }
    
    /**
     * Validate attachments.
     *
     * @param  object|array  $statements
     * @param  array  $attachments
     * @param  bool  $globalCheck  Perform the global checks (on a batch or unique statement)
     * @return  array  The attachments that are referenced by the statements and only them.
     */
    protected function validateAttachments($statements, array $attachments = [], $globalCheck = true): array
    {
        $usedAttachments = [];
        if (is_array($statements)) {
            // Statements batch.
            foreach ($statements as $statement) {
                $justUsedAttachments = $this->validateAttachments($statement, $attachments, false);
                $usedAttachments = array_merge($usedAttachments, $justUsedAttachments);
            }
        } else {
            // Single statement.
            $statement = $statements;

            // No attachment.
            if (!isset($statement->attachments)) {
                return $usedAttachments;
            }

            foreach ($statement->attachments as $attachment) {
                // Location.
                $mustBeRaw = $attachment->usageType == 'http://adlnet.gov/expapi/attachments/signature'
                    || !isset($attachment->fileUrl);
                
                // Check that a matching raw attachment exists.
                if ($mustBeRaw && !isset($attachments[$attachment->sha2])) {
                    throw new XapiBadRequestException("Some raw attachments are missing.");
                }
                
                // This is a remote attachment, skip it.
                if (!isset($attachments[$attachment->sha2])) {
                    continue;
                }
                
                // Check content type.
                $rawAttachment = $attachments[$attachment->sha2];
                if (isset($rawAttachment->contentType) && $rawAttachment->contentType != $attachment->contentType) {
                    throw new XapiBadRequestException("The Content-Type of a raw attachment is incorrect.");
                }
                
                // Check content lenght.
                if (isset($rawAttachment->length) && $rawAttachment->length != $attachment->length) {
                    throw new XapiBadRequestException("The Content-Length of a raw attachment is incorrect.");
                }
                
                // Check signed statements
                $this->validateSignedAttachment($attachment, $rawAttachment, $statements);
                
                // Check that content length does not exceed the platform config.
                // TBD !!! (MongoDB/MySQL limit, server limit...)
                
                // Remember that this attachment as been used.
                $usedAttachments[$attachment->sha2] = true;
            }
        }
                
        // Some attachments have not been used by the statements.
        if ($globalCheck && count($usedAttachments) < count($attachments)) {
            throw new XapiBadRequestException("Some attachments are not referenced in the statements.");
        }
            
        return $usedAttachments;
    }

    /**
     * Validate a signed attachment.
     *
     * @param  object  $jsonAttachment
     * @param  object  $rawAttachment
     * @param  object|array  $statements
     * @return  void
     */
    protected function validateSignedAttachment(object $jsonAttachment, object $rawAttachment, $statements): void
    {
        // Not a signed statement.
        if ($jsonAttachment->usageType != 'http://adlnet.gov/expapi/attachments/signature') {
            return;
        }
        
        // Content type.
        if ($jsonAttachment->contentType != 'application/octet-stream') {
            throw new XapiBadRequestException("The Content-Type of a signed attachment is incorrect.");
        }
        
        // Decompose.
        $parts = explode('.', $rawAttachment->content);
        if (count($parts) != 3) {
            throw new XapiBadRequestException("The format of a signed attachment is incorrect.");
        }

        // Header.
        $header = json_decode(base64_decode($parts[0]));
        if (!$header) {
            throw new XapiBadRequestException("The header of a signed attachment is incorrect.");
        }
        
        // Encryption.
        if (!isset($header->alg) || !in_array($header->alg, ['RS256', 'RS384', 'RS512'])) {
            throw new XapiBadRequestException("The encryption algorythm of a signed attachment is incorrect.");
        }
        
        // Payload.
        $payload = json_decode(base64_decode($parts[1]));
        if (!$payload) {
            throw new XapiBadRequestException("The payload of a signed attachment is incorrect.");
        }
        
        // Remove JWT data on payload.
        // xAPI Launch adds an 'iat' prop. I don't know if it is normal. But it makes the comparison fail.
        unset($payload->iat);

        // Compare statements
        if (!Statement::compare($statements, $payload)) {
            throw new XapiBadRequestException("A signed attachment is incorrect.");
        }
    }
}
