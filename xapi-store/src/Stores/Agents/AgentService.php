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
    public function newPseudoData(string $objectType, $personId, $ownerId = null): array
    {
        return [
            'agent' => [
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
            'agent' => $agent,
            'person_id' => $personId,
            'pseudo_id' => $pseudoId,
            'owner_id' => $ownerId
        ];
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

            // VID props.
            $agentProps = AgentFactory::agentPropsFromVid($agent->vid);
            foreach ($agentProps as $prop => $value) {
                if (!isset($person->$prop)) {
                    $person->$prop = [];
                }
                $person->$prop[] = $value;
            }

            // Name.
            if (!is_null($agent->name)) {
                if (!isset($person->name)) {
                    $person->name = [];
                }
                $person->name[] = $agent->name;
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
