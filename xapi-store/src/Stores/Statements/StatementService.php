<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Stores\Attachments\AttachmentRepository;
use Trax\XapiStore\Stores\Verbs\VerbRepository;
use Trax\XapiStore\Stores\Persons\PersonRepository;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\Stores\Statements\Actions\RecordStatements;
use Trax\XapiStore\Stores\Statements\Actions\RecordAgents;
use Trax\XapiStore\Stores\Statements\Actions\RecordVerbs;
use Trax\XapiStore\Stores\Statements\Actions\RecordActivities;
use Trax\XapiStore\Stores\Statements\Actions\RecordAttachments;
use Trax\XapiStore\Stores\Statements\Actions\RevealStatements;
use Trax\XapiStore\Stores\Statements\Actions\BuildResponse;
use Trax\XapiStore\Stores\Statements\Actions\GetAuthority;
use Trax\XapiStore\Stores\Statements\Actions\VoidStatement;
use Trax\XapiStore\Stores\Statements\Actions\RequestStatement;

class StatementService extends StatementRepository
{
    use RecordStatements, RecordAgents, RecordVerbs, RecordActivities, RecordAttachments,
        BuildResponse, GetAuthority, VoidStatement, RevealStatements, RequestStatement;
    
    /**
     * The activities repository.
     *
     * @var \Trax\XapiStore\Stores\Activities\ActivityRepository
     */
    protected $activities;

    /**
     * The attachments repository.
     *
     * @var \Trax\XapiStore\Stores\Attachments\AttachmentRepository
     */
    protected $attachments;

    /**
     * The agents service.
     *
     * @var \Trax\XapiStore\Stores\Agents\AgentService
     */
    protected $agents;

    /**
     * The persons repository.
     *
     * @var \Trax\XapiStore\Stores\Persons\PersonRepository
     */
    protected $persons;

    /**
     * The verbs repository.
     *
     * @var \Trax\XapiStore\Stores\Verbs\VerbRepository
     */
    protected $verbs;

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Activities\ActivityRepository  $activities
     * @param  \Trax\XapiStore\Stores\Attachments\AttachmentRepository  $attachments
     * @param  \Trax\XapiStore\Stores\Agents\AgentService  $agents
     * @param  \Trax\XapiStore\Services\Persons\PersonRepository  $persons
     * @param  \Trax\XapiStore\Services\Verbs\VerbRepository  $verbs
     * @return void
     */
    public function __construct(
        ActivityRepository $activities,
        AttachmentRepository $attachments,
        AgentService $agents,
        PersonRepository $persons,
        VerbRepository $verbs
    ) {
        $this->activities = $activities;
        $this->attachments = $attachments;
        $this->agents = $agents;
        $this->persons = $persons;
        $this->verbs = $verbs;

        // Deactivate Eloquent for GET requests when there is no relational need.
        if (!config('trax-xapi-store.requests.relational', false)) {
            $this->table = 'trax_xapi_statements';
        }
        
        parent::__construct();
    }

    /**
     * Get resources.
     * Try to use relations, but don't reveal the statements.
     * We are on the default CRUD repo GET method which works on raw data.
     * So we want to write and read raw data.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection
    {
        // We should use relations first.
        if (config('trax-xapi-store.requests.relational', false) && isset($query)) {
            $reveal = $query->option('reveal', false);
            return $this->getRelationalFirst($query, $reveal);
        }

        return parent::get($query);
    }

    /**
     * Try to use relational requests first on standard and magic filters.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param bool  $reveal
     * @return \Illuminate\Support\Collection
     */
    public function getRelationalFirst(Query $query = null, bool $reveal = true): Collection
    {
        // We can't use relations.
        if (!config('trax-xapi-store.requests.relational', false)) {
            return parent::get($query);
        }

        // No query.
        if (!isset($query)) {
            return $this->getStatements($query, $reveal);
        }

        // Get the owner.
        $consumer = TraxAuth::consumer();
        $ownerId = is_null($consumer) ? null : $consumer->owner_id;
        $ownerId = is_null($ownerId) ? $query->filter('owner_id') : $ownerId;

        // Request statement.
        if (!$match = $this->requestStatement($query, $ownerId, $reveal)) {
            return collect([]);
        }

        // Now, make the request.
        return $this->getStatements($query, $reveal && !$this->dontRevealAgents($query));
    }

    /**
     * Get statements and reveal them or not.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param bool  $reveal
     * @return \Illuminate\Support\Collection
     */
    protected function getStatements(Query $query = null, bool $reveal = true): Collection
    {
        return $reveal
            ? $this->revealStatements(parent::get($query))
            : parent::get($query);
    }

    /**
     * Create statements and return their ids.
     * This method uses a DB transaction.
     *
     * @param  \stdClass|array  $statements
     * @param  array  $attachments
     * @return array
     */
    public function createStatements($statements, array $attachments = [])
    {
        return DB::transaction(function () use ($statements, $attachments) {
            return $this->createStatementsWithoutTransaction($statements, $attachments);
        });
    }

    /**
     * Create statements and return their ids.
     * This method does not use a DB transaction.
     *
     * @param  \stdClass|array  $statements
     * @param  array  $attachments
     * @return array
     */
    public function createStatementsWithoutTransaction($statements, array $attachments = [])
    {
        if (!is_array($statements)) {
            $statements = [$statements];
        }

        // First, manage voiding because exceptions may be thrown.
        foreach ($statements as $statement) {
            if ($statement->verb->id == 'http://adlnet.gov/expapi/verbs/voided') {
                $this->voidStatement($statement->object->id);
            }
        }

        // Context.
        $context = [
            'owner_id' => null  // Because they all need an owner_id in the context, be it null.
        ];
        $access = TraxAuth::access();
        if (!is_null($access)) {
            $context = [
                'access_id' => $access->id,
                'client_id' => $access->client->id,
                'entity_id' => $access->client->entity_id,
                'owner_id' => $access->client->owner_id,
            ];
        }

        // Get the authority.
        $authority = $this->getAccessAuthority();

        // Save the agents.
        // We must do it before saving the statements because when pseudonymization is active,
        // we want to get the anonymous agents first.
        $agentsInfo = [];
        if (config('trax-xapi-store.tables.agents', false)) {
            $agentsInfo = $this->recordAgents($statements, $authority, $context);
        }
    
        // Save the statements.
        $statements = $this->recordStatements($statements, $authority, $context, $agentsInfo);

        // Save the attachments.
        $this->recordAttachments($attachments, $context);
    
        // Save the verbs.
        if (config('trax-xapi-store.tables.verbs', false)) {
            $this->recordStatementsVerbs($statements, $context);
        }
    
        // Save the activities.
        $this->recordStatementsActivities($statements, $context);

        // Return the statements IDs.
        return array_map(function ($statement) {
            return $statement->uuid;
        }, $statements);
    }

    /**
     * Import statements.
     * This method uses a DB transaction.
     *
     * @param  array  $statements
     * @param  int  $ownerId
     * @param  int  $entityId
     * @param  bool  $pseudonymize
     * @return void
     */
    public function importStatements(array $statements, int $ownerId, int $entityId = null, bool $pseudonymize = true)
    {
        return DB::transaction(function () use ($statements, $ownerId, $entityId, $pseudonymize) {

            // Context.
            $context = [
                'entity_id' => $entityId,
                'owner_id' => $ownerId,
            ];

            // Get the authority.
            $authority = $this->getImportAuthority();

            // Save the agents.
            // We must do it before saving the statements because when pseudonymization is active,
            // we want to get the anonymous agents first.
            $agentsInfo = [];
            if (config('trax-xapi-store.tables.agents', false)) {
                $agentsInfo = $this->recordAgents($statements, $authority, $context);
            }
        
            // Save the statements.
            $statements = $this->recordStatements($statements, $authority, $context, $agentsInfo, $pseudonymize);
        
            // Save the verbs.
            if (config('trax-xapi-store.tables.verbs', false)) {
                $this->recordStatementsVerbs($statements, $context);
            }
        
            // Save the activities.
            $this->recordStatementsActivities($statements, $context);
        });
    }
}
