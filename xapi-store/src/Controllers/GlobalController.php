<?php

namespace Trax\XapiStore\Controllers;

use App\Http\Controllers\Controller;
use Trax\Auth\Authorizer;
use Trax\XapiStore\Stores\Statements\StatementService;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Stores\Agents\AgentRepository;
use Trax\XapiStore\Stores\States\StateRepository;
use Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository;
use Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository;
use Trax\XapiStore\Stores\Attachments\AttachmentRepository;
use Trax\XapiStore\Stores\Persons\PersonRepository;
use Trax\XapiStore\Stores\Verbs\VerbRepository;

class GlobalController extends Controller
{
    use ClearStores;
    
    /**
     * @var \Trax\Auth\Authorizer
     */
    protected $authorizer;

    /**
     * @var \Trax\XapiStore\Stores\Statements\StatementService
     */
    protected $statements;

    /**
     * @var \Trax\XapiStore\Stores\Activities\ActivityRepository
     */
    protected $activities;

    /**
     * @var \Trax\XapiStore\Stores\Agents\AgentRepository
     */
    protected $agents;

    /**
     * @var \Trax\XapiStore\Stores\States\StateRepository
     */
    protected $states;

    /**
     * @var \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository
     */
    protected $activityProfiles;

    /**
     * @var \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository
     */
    protected $agentProfiles;

    /**
     * @var \Trax\XapiStore\Stores\Attachments\AttachmentRepository
     */
    protected $attachments;

    /**
     * @var \Trax\XapiStore\Stores\Persons\PersonRepository
     */
    protected $persons;

    /**
     * @var \Trax\XapiStore\Stores\Verbs\VerbRepository
     */
    protected $verbs;


    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Authorizer  $authorizer
     * @param  \Trax\XapiStore\Stores\Statements\StatementService  $statements
     * @param  \Trax\XapiStore\Stores\Activities\ActivityRepository  $activities
     * @param  \Trax\XapiStore\Stores\Agents\AgentRepository  $agents
     * @param  \Trax\XapiStore\Stores\States\StateRepository  $states
     * @param  \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository  $activityProfiles
     * @param  \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository  $agentProfiles
     * @param  \Trax\XapiStore\Stores\Attachments\AttachmentRepository  $attachments
     * @param  \Trax\XapiStore\Stores\Persons\PersonRepository  $persons
     * @param  \Trax\XapiStore\Stores\Verbs\VerbRepository  $verbs
     * @return void
     */
    public function __construct(
        Authorizer $authorizer,
        StatementService $statements,
        ActivityRepository $activities,
        AgentRepository $agents,
        StateRepository $states,
        ActivityProfileRepository $activityProfiles,
        AgentProfileRepository $agentProfiles,
        AttachmentRepository $attachments,
        PersonRepository $persons,
        VerbRepository $verbs
    ) {
        $this->authorizer = $authorizer;
        $this->statements = $statements;
        $this->activities = $activities;
        $this->agents = $agents;
        $this->states = $states;
        $this->activityProfiles = $activityProfiles;
        $this->agentProfiles = $agentProfiles;
        $this->attachments = $attachments;
        $this->persons = $persons;
        $this->verbs = $verbs;
    }
}
