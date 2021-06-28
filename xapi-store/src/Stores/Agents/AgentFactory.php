<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Repo\Contracts\ModelFactoryContract;

class AgentFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Agent::class;
    }

    /**
     * Create a new model instance given some data.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function make(array $data)
    {
        $modelClass = self::modelClass();
        $model = new $modelClass;

        // Required data.
        $model->person_id = $data['person_id'];
        $model->vid = self::virtualId($data['agent']);

        // Nullable name.
        if (isset($data['agent']['name'])) {
            $model->name = $data['agent']['name'];
        }
        
        // Optional is_group.
        if (isset($data['agent']['objectType']) && $data['agent']['objectType'] == 'Group') {
            $model->is_group = true;
        }
        
        // Optional pseudonymized.
        if (isset($data['pseudonymized'])) {
            $model->pseudonymized = $data['pseudonymized'];
        }

        // Nullable pseudo_id.
        if (isset($data['pseudo_id'])) {
            $model->pseudo_id = $data['pseudo_id'];
        }

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        return $model;
    }

    /**
     * Prepare data before recording (used for bulk insert).
     *
     * @param array  $data
     * @return array
     */
    public static function prepare(array $data)
    {
        // VID.
        $data['vid'] = self::virtualId($data['agent']);

        // Nullable name.
        $data['name'] = isset($data['agent']->name) ? $data['agent']->name : null;
        
        // Is group.
        $data['is_group'] = isset($data['agent']->objectType) && $data['agent']->objectType == 'Group';

        unset($data['agent']);

        // Timestamps.
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $data;
    }

    /**
     * Update an existing model instance, given some data.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function update($model, array $data)
    {
        $model->update($data);
        return $model;
    }

    /**
     * Duplicate an existing model in the database, given some data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function duplicate($model, array $data = [])
    {
        $copy = $model->replicate()->fill($data);
        $copy->save();
        return $copy;
    }

    /**
     * Generate a virtual ID for this agent.
     *
     * @param  object|array|string  $agent
     * @return string|null
     */
    public static function virtualId($agent)
    {
        // Agent as an object.
        if (is_string($agent)) {
            $agent = json_decode($agent);
        } elseif (is_array($agent)) {
            $agent = json_decode(json_encode($agent));
        }
        // Generate the virtual ID.
        if (isset($agent->mbox)) {
            return "mbox::$agent->mbox";
        } elseif (isset($agent->mbox_sha1sum)) {
            return "mbox_sha1sum::$agent->mbox_sha1sum";
        } elseif (isset($agent->openid)) {
            return "openid::$agent->openid";
        } elseif (isset($agent->account)) {
            return 'account::'.$agent->account->name.'@'.$agent->account->homePage;
        } else {
            return null;
        }
    }

    /**
     * Get the agent from a virtual ID.
     *
     * @param  string  $vid
     * @param  bool  $asObject
     * @return array|object
     */
    public static function reverseVirtualId(string $vid, bool $asObject = false)
    {
        list($type, $value) = explode('::', $vid);
        if ($type == 'mbox') {
            $agent = ['mbox' => $value];
        } elseif ($type == 'mbox_sha1sum') {
            $agent = ['mbox_sha1sum' => $value];
        } elseif ($type == 'openid') {
            $agent = ['openid' => $value];
        } elseif ($type == 'account') {
            list($name, $homePage) = explode('@', $value);
            $agent = ['account' => [
                'name' => $name,
                'homePage' => $homePage,
            ]];
        }
        return $asObject ? (object)$agent : $agent;
    }

    /**
     * Extract the agent props from a virtual ID.
     *
     * @param  string $vid
     * @return array
     */
    public static function agentPropsFromVid(string $vid)
    {
        list($type, $value) = explode('::', $vid);

        if ($type == 'account') {
            list($name, $homePage) = explode('@', $value);
            return [$type => [
                'name' => $name,
                'homePage' => $homePage,
            ]];
        }
        return [$type => $value];
    }
}
