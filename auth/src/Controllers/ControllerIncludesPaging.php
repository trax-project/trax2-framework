<?php

namespace Trax\Auth\Controllers;

use Trax\Repo\CrudRequest;

trait ControllerIncludesPaging
{
    /**
     * Include paging data into the response.
     *
     * @param  array  $responseData
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @return array
     */
    protected function addPagingData(&$responseData, CrudRequest $crudRequest)
    {
        if ($crudRequest->hasParam('skip')) {
            $responseData['paging'] = [
                'limit' => intval($crudRequest->param('limit')),
                'skip' => intval($crudRequest->param('skip')),
                'count' => $this->countAllResources($this->permissionsDomain, $this->repository, $crudRequest)
            ];
        }
        return $responseData;
    }
}
