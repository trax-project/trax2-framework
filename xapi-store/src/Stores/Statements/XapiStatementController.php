<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Http\Request;
use Trax\XapiStore\Abstracts\XapiController;
use Trax\XapiStore\Exceptions\XapiNotFoundException;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\Stores\Statements\StatementRepository;
use Trax\XapiStore\Stores\Logs\Logger;

class XapiStatementController extends XapiController
{
    use XapiStatementValidation;

    /**
     * The repository class.
     *
     * @var \Trax\XapiStore\Stores\Statements\StatementRepository
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
     * @param  \Trax\XapiStore\Stores\Statements\StatementRepository  $repository
     * @return void
     */
    public function __construct(StatementRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Post a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function post(Request $request)
    {
        $service = app(\Trax\XapiStore\Services\StatementRecord\StatementRecordService::class);

        // Alternate request.
        if ($redirectMethod = $this->checkAlternateRequest($request)) {
            return $this->$redirectMethod($request);
        }

        // Validate request.
        $xapiRequest = $this->validatePostRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Save statements.
        $ids = $service->createStatements(
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
        $service = app(\Trax\XapiStore\Services\StatementRecord\StatementRecordService::class);

        // Validate request.
        $xapiRequest = $this->validatePutRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Save statement.
        $service->createStatements(
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
        $service = app(\Trax\XapiStore\Services\StatementRequest\StatementRequestService::class);

        // Find a given statement.
        if ($request->has('statementId') || $request->has('voidedStatementId')) {
            return $this->find($request);
        }

        // Validate request.
        $xapiRequest = $this->validateGetRequest($request);

        // Perform request.
        $resources = $this->getResources($xapiRequest, 'getWithStandardProcess', $service);

        // Prepare response.
        $response = ['statements' => $resources->pluck('data')->all()];

        // Add the more link.
        if ($more = $service->moreUrl($request->url(), $xapiRequest, $resources)) {
            $response['more'] = $more;
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'GET', count($response['statements']));

        // Response.
        $withAttachments = $xapiRequest->param('attachments') == 'true';
        return $service->responseWithContent((object)$response, $withAttachments);
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
        $service = app(\Trax\XapiStore\Services\StatementRequest\StatementRequestService::class);

        // Validate request.
        $xapiRequest = $this->validateFindRequest($request);

        // Perform request.
        $resources = $this->getResources($xapiRequest, 'getRelationalFirst', $service);

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
        return $service->responseWithContent($response->data, $withAttachments);
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
