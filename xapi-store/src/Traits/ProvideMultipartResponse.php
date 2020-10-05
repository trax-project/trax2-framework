<?php

namespace Trax\XapiStore\Traits;

trait ProvideMultipartResponse
{
    /**
     * Return a multipart response.
     *
     * @param  array  $parts
     * @return \Illuminate\Http\Response
     */
    protected function multipartResponse(array $parts)
    {
        // Generate a boundary.
        $boundary = md5(rand());
    
        // Content.
        $crlf = "\r\n";
        $content = '';
        foreach ($parts as $part) {
             $content .= $this->multipart($part, $boundary);
        }
        $content .= $crlf.'--'.$boundary.'--'.$crlf;

        // Response.
        return response($content, 200)
            ->header('Content-Type', 'multipart/mixed; boundary="'.$boundary.'"')
            ->header('Content-Length', mb_strlen($content, '8bit'));
    }
    
    /**
     * Return a part of a multipart.
     *
     * @param  \stdClass  $part
     * @param  string  $boundary
     * @return  string
     */
    protected function multipart(\stdClass $part, string $boundary): string
    {
          $crlf = "\r\n";
          $content = $crlf.'--'.$boundary.$crlf;
  
          // Content type.
          $contentType =  isset($part->contentType) ? $part->contentType : 'application/json';
          $content .= 'Content-Type:'.$contentType.$crlf;
          
          // Content length.
          $contentLength =  isset($part->length) ? $part->length : mb_strlen($part->content, '8bit');
          $content .= 'Content-Length:'.$contentLength.$crlf;
          
          // Encoding.
          $encoding =  isset($part->encoding) ? $part->encoding : 'binary';
          $content .= 'Content-Transfer-Encoding:'.$encoding.$crlf;
          
          // Hash.
          $hash =  isset($part->sha2) ? $part->sha2 : hash('sha256', $part->content);
          $content .= 'X-Experience-API-Hash:'.$hash.$crlf;
          
          // Content.
          $content .= $crlf.$part->content;
          return $content;
    }
    
    
}

