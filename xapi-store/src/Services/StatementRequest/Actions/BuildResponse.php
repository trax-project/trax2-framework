<?php

namespace Trax\XapiStore\Services\StatementRequest\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Traits\ProvideMultipartResponse;

trait BuildResponse
{
    use ProvideMultipartResponse;
    
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
        if ($resources->isEmpty()) {
            return false;
        }

        $nav = $xapiRequest->param('ascending') == 'true' ? 'after' : 'before';
        $params = [
            $nav.'[id]' => $resources->last()->id,
        ];

        foreach (['agent', 'verb', 'activity', 'registration', 'related_activities', 'related_agents', 'since', 'until', 'limit', 'format', 'attachments', 'ascending'] as $name) {
            if ($xapiRequest->hasParam($name)) {
                $params[$name] = $xapiRequest->param($name);
            }
        }

        return $apiUrl . '?' . http_build_query($params);
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
            $attachments = app(\Trax\XapiStore\Stores\Attachments\AttachmentRepository::class)->addFilter([
                'data->sha2' => ['$in' => $this->statementsAttachmentShas($statements)]
            ])->get()->pluck('data')->all();

            array_unshift($attachments, (object)array('content' => json_encode($content)));
            $response = $this->multipartResponse($attachments);
        } else {
            // JSON.
            $response = response()->json($content);
        }
        return $response->header('X-Experience-API-Consistent-Through', $this->repository->consistentThrough());
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
