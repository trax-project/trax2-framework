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
        $model->data = $data['data'];
        $model->person_id = $data['person_id'];

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

        // VID.
        $model->vid = self::virtualId($model->data);

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
        $data['vid'] = self::virtualId($data['data']);

        // JSON data.
        $data['data'] = json_encode($data['data']);

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
}
