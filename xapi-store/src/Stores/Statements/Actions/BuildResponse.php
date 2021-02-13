<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\XapiDate;
use Trax\XapiStore\Traits\ProvideMultipartResponse;

trait BuildResponse
{
    use ProvideMultipartResponse;
    
    /**
     * Get the consistent through value.
     *
     * @return string
     */
    public function consistentThrough()
    {
        return XapiDate::now();
    }
    
    /**
     * Get the 'more' URL.
     *
     * @param  string  $apiUrl
     * @param  \Trax\XapiStore\Stores\Statements\XapiStatementRequest  $xapiRequest
     * @param  \Illuminate\Support\Collection  $resources
     * @return string|false
     */
    public function moreUrl(string $apiUrl, $xapiRequest, Collection $resources)
    {
        $limit = $xapiRequest->param('limit') ?: 0;
        $ascending = $xapiRequest->param('ascending') == 'true';
        $nav = $ascending ? 'after' : 'before';
        
        if ($resources->isEmpty()) {
            return false;
        }

        if (!$this->addFilter(['voided' => false])->$nav($resources->last()->id)) {
            return false;
        }
        return $apiUrl . '?' . http_build_query([
            $nav.'[id]' => $resources->last()->id,
            'limit' => $limit,
            'ascending' => $ascending ? 'true' : 'false',
        ]);
    }

    /**
     * Return a GET statement(s) response.
     *
     * @param  mixed  $content
     * @param  bool  $joinAttachments
     * @return \Illuminate\Http\Response
     */
    public function responseWithContent($content, $joinAttachments = false)
    {
        if ($joinAttachments) {
            // Multipart...
            
            // Statements.
            $statements = isset($content->statements) ? $content->statements : [$content];
            
            // Attachments.
            $sha2s = $this->statementsAttachmentShas($statements);
            $attachments = $this->attachments->addFilter(['data->sha2' => ['$in' => $sha2s]])
                ->get()->pluck('data')->all();

            array_unshift($attachments, (object)array('content' => json_encode($content)));
            $response = $this->multipartResponse($attachments);
        } else {
            // JSON.
            $response = response()->json($content);
        }
        return $response->header('X-Experience-API-Consistent-Through', $this->consistentThrough());
    }

    /**
     * Extract attachments sha2 from a list of statements.
     *
     * @param  array  $statements
     * @return array
     */
    protected function statementsAttachmentShas(array $statements): array
    {
        return collect($statements)->reduce(function (array $sha2s, $statement) {
            if (isset($statement->attachments)) {
                $newSha2s = collect($statement->attachments)->pluck('sha2')->all();
                return array_merge($sha2s, $newSha2s);
            }
            return $sha2s;
        }, []);
    }
}
