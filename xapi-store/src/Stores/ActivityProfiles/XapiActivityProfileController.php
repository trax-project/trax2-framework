<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentController;
use Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository;
use Trax\XapiStore\Traits\ManageConcurrency;
use Trax\XapiStore\Traits\ValidateDocument;

class XapiActivityProfileController extends XapiDocumentController
{
    use ValidateDocument, ManageConcurrency;
    
    /**
     * The xAPI request class to be used..
     *
     * @var string
     */
    protected $xapiRequestClass = XapiActivityProfileRequest::class;
    
    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'activity_profile';
    
    /**
     * PUT rules.
     */
    protected $putRules = [
        'activityId' => 'required|url',
        'profileId' => 'required|string|forbidden_with:since',
    ];

    /**
     * GET rules.
     */
    protected $getRules = [
        'activityId' => 'required|url',
        'profileId' => 'string|forbidden_with:since',
        'since' => 'iso_date|forbidden_with:profileId'
    ];

    /**
     * FIND rules.
     */
    protected $deleteRules = [
        'activityId' => 'required|url',
        'profileId' => 'required|string',
    ];

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository  $repository
     * @return void
     */
    public function __construct(ActivityProfileRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
}
