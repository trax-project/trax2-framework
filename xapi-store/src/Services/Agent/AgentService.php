<?php

namespace Trax\XapiStore\Services\Agent;

use Illuminate\Container\Container;
use Trax\XapiStore\Stores\Agents\Agent;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Services\Agent\Actions\ProcessPendingStatements;
use Trax\XapiStore\Services\Agent\Actions\BuildStatementsQuery;

class AgentService
{
    use ProcessPendingStatements, BuildStatementsQuery;

    /**
     * Repository.
     *
     * @var \Trax\XapiStore\Stores\Agents\AgentRepository
     */
    protected $repository;

    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Container\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->repository = $container->make(\Trax\XapiStore\Stores\Agents\AgentRepository::class);
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
