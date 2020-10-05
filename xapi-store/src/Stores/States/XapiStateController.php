<?php

namespace Trax\XapiStore\Stores\States;

use Trax\XapiStore\Abstracts\XapiDocumentController;
use Trax\XapiStore\Stores\States\StateRepository;
use Trax\XapiStore\Traits\ValidateDocument;

class XapiStateController extends XapiDocumentController
{
    use ValidateDocument;

    /**
     * The xAPI request class to be used..
     *
     * @var string
     */
    protected $xapiRequestClass = XapiStateRequest::class;
    
    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'state';
    
    /**
     * PUT rules.
     */
    protected $putRules = [
        'activityId' => 'required|url',
        'agent' => 'required|xapi_agent',
        'registration' => 'uuid',
        'stateId' => 'required|string|forbidden_with:since',
    ];

    /**
     * GET rules.
     */
    protected $getRules = [
        'activityId' => 'required|url',
        'agent' => 'required|xapi_agent',
        'registration' => 'uuid',
        'stateId' => 'string|forbidden_with:since',
        'since' => 'iso_date|forbidden_with:stateId'
    ];

    /**
     * FIND rules.
     */
    protected $deleteRules = [
        'activityId' => 'required|url',
        'agent' => 'required|xapi_agent',
        'registration' => 'uuid',
        'stateId' => 'string',
    ];

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\States\StateRepository  $repository
     * @return void
     */
    public function __construct(StateRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
}
