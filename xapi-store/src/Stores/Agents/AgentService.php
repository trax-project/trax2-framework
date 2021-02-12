<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Container\Container;

class AgentService extends AgentRepository
{
    /**
     * The persons repository.
     *
     * @var \Trax\XapiStore\Stores\Persons\PersonRepository
     */
    protected $persons;

    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Container\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->persons = $container->make(\Trax\XapiStore\Stores\Persons\PersonRepository::class);
        parent::__construct();
    }

    /**
     * Get data for a new person.
     *
     * @param  integer|null  $ownerId
     * @return array
     */
    public function newPersonData($ownerId = null): array
    {
        return ['owner_id' => $ownerId];
    }

    /**
     * Get data for a new pseudo.
     *
     * @param  string  $objectType
     * @param  integer  $personId
     * @param  integer|null  $ownerId
     * @return array
     */
    public function newPseudoData(string $objectType, int $personId, $ownerId = null): array
    {
        return [
            'data' => [
                'objectType' => $objectType,
                'account' => [
                    'name' => \Str::uuid(),
                    'homePage' => config('trax-xapi-store.gdpr.pseudo_iri', 'http://pseudo.traxlrs.com'),
                ]
            ],
            'person_id' => $personId,
            'owner_id' => $ownerId,
            'pseudonymized' => true
        ];
    }

    /**
     * Get data for a new agent.
     *
     * @param  object  $agent
     * @param  integer  $personId
     * @param  integer|null  $pseudoId
     * @param  integer|null  $ownerId
     * @return array
     */
    public function newAgentData(object $agent, $personId, $pseudoId = null, $ownerId = null): array
    {
        return [
            'data' => $agent,
            'person_id' => $personId,
            'pseudo_id' => $pseudoId,
            'owner_id' => $ownerId
        ];
    }

    /**
     * Check that an agent has a pseudonymized equivalent.
     *
     * @param  \Trax\XapiStore\Stores\Agents\Agent  $agent
     * @return void
     */
    public function checkAgentWithPerson(Agent $agent): void
    {
        // Create a pseudonymized agent and update the native agent.
        if (config('trax-xapi-store.gdpr.pseudonymization', false) && empty($agent->pseudo_id)) {
            $pseudo = $this->createPseudoAgent($agent->data->objectType, $agent->person->id, $agent->owner_id);
            $agent->pseudo_id = $pseudo->id;
            $agent->save();
        }
    }

    /**
     * Create a pseudonymized agent.
     *
     * @param  string  $objectType
     * @param  int  $personId
     * @param  int  $ownerId
     * @return \Trax\XapiStore\Stores\Agents\Agent
     */
    public function createPseudoAgent(string $objectType, $personId, $ownerId): Agent
    {
        return $this->create(
            $this->newPseudoData($objectType, $personId, $ownerId)
        );
    }

   /**
     * Get a real person.
     *
     * @param  \Trax\XapiStore\Stores\Agents\Agent  $agent
     * @return object
     */
    public function getRealPerson(Agent $agent)
    {
        $person = (object)[];
        $agents = $agent->person->agents;
        foreach ($agents as $agent) {
            foreach ((array)$agent->data as $prop => $value) {
                if (in_array($prop, ['mbox', 'mbox_sha1sum', 'openid', 'account', 'name'])) {
                    if (!isset($person->$prop)) {
                        $person->$prop = [];
                    }
                    $person->$prop[] = $value;
                }
            }
        }
        $person->objectType = 'Person';
        return $person;
    }

    /**
     * Get a virtual person.
     *
     * @param  object  $agent
     * @return object
     */
    public function getVirtualPerson(object $agent)
    {
        $person = (object)[];
        foreach ((array)$agent as $prop => $value) {
            if (in_array($prop, ['mbox', 'mbox_sha1sum', 'openid', 'account', 'name'])) {
                $person->$prop = [$value];
            }
        }
        $person->objectType = 'Person';
        return $person;
    }
}
