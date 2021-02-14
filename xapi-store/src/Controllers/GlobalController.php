<?php

namespace Trax\XapiStore\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Trax\Auth\Authorizer;
use Trax\Auth\Stores\Owners\OwnerRepository;
use Trax\XapiStore\Services\GlobalService;

class GlobalController extends Controller
{
    /**
     * @var \Trax\Auth\Authorizer
     */
    protected $authorizer;

    /**
     * @var \Trax\Auth\Stores\Owners\OwnerRepository
     */
    protected $owners;

    /**
     * @var \Trax\XapiStore\Services\GlobalService
     */
    protected $service;


    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Authorizer  $authorizer
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @param  \Trax\XapiStore\Services\GlobalService  $service
     * @return void
     */
    public function __construct(Authorizer $authorizer, OwnerRepository $owners, GlobalService $service)
    {
        $this->authorizer = $authorizer;
        $this->owners = $owners;
        $this->service = $service;
    }

    /**
     * Clear all stores.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clearStores(Request $request)
    {
        // Check permissions.
        $this->authorizer->must('xapi-extra.manage');

        // Do it.
        $this->service->clearStores();

        return response('', 204);
    }

    /**
     * Clear a store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function clearStore(Request $request, $id)
    {
        // Check permissions.
        $owner = $this->owners->findOrFail($id);
        $this->authorizer->must('owner.delete', $owner);
        $this->authorizer->must('xapi-extra.manage');

        // Do it.
        $this->service->clearStore($id);

        return response('', 204);
    }
}
