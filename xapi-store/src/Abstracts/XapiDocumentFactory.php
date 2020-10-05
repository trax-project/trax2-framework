<?php

namespace Trax\XapiStore\Abstracts;

use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\XapiStore\XapiDate;
use Trax\XapiStore\Exceptions\XapiBadRequestException;

abstract class XapiDocumentFactory implements ModelFactoryContract
{
    /**
     * Update an existing model instance, given some data.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function update($model, array $data)
    {
        $data['timestamp'] = XapiDate::now();
        $model->update($data);
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
        return $data;
    }

    /**
     * Merge data with an existing model instance.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return bool
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    public static function merge($model, array $data): bool
    {
        // Get content types.
        $modelIsJson = strpos('application/json', $model->data->type) !== false;
        $dataIsJson = strpos('application/json', $data['data']['type']) !== false;

        // Only one is JSON.
        if (($modelIsJson || $dataIsJson) && $modelIsJson != $dataIsJson) {
            throw new XapiBadRequestException('JSON content can not be merged because one content type is not JSON.');
        }

        // Merge content.
        $newData = $model->data;
        $newData->content = array_merge(get_object_vars($newData->content), $data['data']['content']);
        $model->data = $newData;

        $model->timestamp = XapiDate::now();
        return true;
    }
}
