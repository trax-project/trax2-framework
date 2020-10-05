<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentController;
use Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository;
use Trax\XapiStore\Traits\ManageConcurrency;
use Trax\XapiStore\Traits\ValidateDocument;

class XapiAgentProfileController extends XapiDocumentController
{
    use ValidateDocument, ManageConcurrency;
    
    /**
     * The xAPI request class to be used..
     *
     * @var string
     */
    protected $xapiRequestClass = XapiAgentProfileRequest::class;
    
    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'agent_profile';
    
    /**
     * PUT rules.
     */
    protected $putRules = [
        'agent' => 'required|xapi_agent',
        'profileId' => 'required|string|forbidden_with:since',
    ];

    /**
     * GET rules.
     */
    protected $getRules = [
        'agent' => 'required|xapi_agent',
        'profileId' => 'string|forbidden_with:since',
        'since' => 'iso_date|forbidden_with:profileId'
    ];

    /**
     * FIND rules.
     */
    protected $deleteRules = [
        'agent' => 'required|xapi_agent',
        'profileId' => 'required|string',
    ];

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository  $repository
     * @return void
     */
    public function __construct(AgentProfileRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
}
