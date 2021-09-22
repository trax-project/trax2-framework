<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Http\Request;
use Trax\XapiStore\Abstracts\XapiController;
use Trax\XapiStore\Exceptions\XapiNotFoundException;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\Stores\Statements\StatementService;
use Trax\XapiStore\Stores\Logs\Logger;

class XapiStatementController extends XapiController
{
    use XapiStatementValidation;

    /**
     * The repository class.
     *
     * @var \Trax\XapiStore\Stores\Statements\StatementService
     */
    protected $repository;

    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'statement';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Statements\StatementService  $service
     * @return void
     */
    public function __construct(StatementService $service)
    {
        parent::__construct();
        $this->repository = $service;
    }

    /**
     * Post a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function post(Request $request)
    {
        // Alternate request.
        if ($redirectMethod = $this->checkAlternateRequest($request)) {
            return $this->$redirectMethod($request);
        }

        // Validate request.
        $xapiRequest = $this->validatePostRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Save statements.
        $ids = $this->repository->createStatements(
            $xapiRequest->statements(),
            $xapiRequest->attachments()
        );

        // Logging.
        Logger::log($this->permissionsDomain, 'POST', count($ids));

        // Response.
        return $this->response($ids);
    }

    /**
     * Put a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function put(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validatePutRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Save statement.
        $this->repository->createStatements(
            $xapiRequest->statements(),
            $xapiRequest->attachments()
        );

        // Logging.
        Logger::log($this->permissionsDomain, 'PUT', 1);

        // Response.
        return response('', 204);
    }

    /**
     * Get resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        // Find a given statement.
        if ($request->has('statementId') || $request->has('voidedStatementId')) {
            return $this->find($request);
        }

        // Validate request.
        $xapiRequest = $this->validateGetRequest($request);

        // Perform request.
        $resources = $this->getResources($xapiRequest, 'getWithStandardProcess');

        // Prepare response.
        $response = ['statements' => $resources->pluck('data')->all()];

        // Add the more link.

        if ($more = $this->repository->moreUrl(traxRequestUrl($request), $xapiRequest, $resources)) {
            $response['more'] = $more;
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'GET', count($response['statements']));

        // Response.
        $withAttachments = $xapiRequest->param('attachments') == 'true';
        return $this->repository->responseWithContent((object)$response, $withAttachments);
    }

    /**
     * Get a given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiNotFoundException
     */
    public function find(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validateFindRequest($request);

        // Perform request.
        $resources = $this->getResources($xapiRequest, 'getRelationalFirst');

        // Check result.
        $response = $resources->last();
        if (!$response) {
            throw (new XapiNotFoundException())->addHeaders(
                ['X-Experience-API-Consistent-Through' => $this->repository->consistentThrough()]
            );
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'GET', 1);

        // Prepare response.
        $withAttachments = $xapiRequest->param('attachments') == 'true';
        return $this->repository->responseWithContent($response->data, $withAttachments);
    }

    /**
     * Delete a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    public function delete(Request $request)
    {
        throw new XapiAuthorizationException('DELETE request is not allowed on this API.');
    }
}
