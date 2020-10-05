<?php

namespace Trax\XapiStore\Stores\Attachments;

use Trax\Repo\CrudRepository;

class AttachmentRepository extends CrudRepository
{
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Attachments\AttachmentFactory
     */
    public function factory()
    {
        return AttachmentFactory::class;
    }
}
