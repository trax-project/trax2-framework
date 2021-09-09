<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

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
        $repository = app(\Trax\XapiStore\Stores\Attachments\AttachmentRepository::class);

        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                $attachment = (object)$attachment;
            }
            if (!$repository->addFilter(['data->sha2' => $attachment->sha2])->get()->last()) {
                $repository->create(array_merge([
                    'data' => $attachment
                ], TraxAuth::context()));
            };
        }
    }
}
