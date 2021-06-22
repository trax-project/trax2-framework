<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Auth\TraxAuth;

trait RecordAttachments
{
    /**
     * Save the attachments.
     *
     * @param  array  $attachments
     * @return void
     */
    public function recordAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                $attachment = (object)$attachment;
            }
            if (!$this->attachments->addFilter(['data->sha2' => $attachment->sha2])->get()->last()) {
                $this->attachments->create(array_merge([
                    'data' => $attachment
                ], TraxAuth::context()));
            };
        }
    }
}
