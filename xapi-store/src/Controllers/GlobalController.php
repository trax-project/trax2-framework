<?php

namespace Trax\XapiStore\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Trax\Auth\Authorizer;
use Trax\Auth\Stores\Owners\OwnerRepository;
use Trax\XapiStore\Services\Destroyer\DestroyerService;

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
     * @var \Trax\XapiStore\Services\Destroyer\DestroyerService
     */
    protected $destroyer;


    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Authorizer  $authorizer
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @param  \Trax\XapiStore\Services\Destroyer\DestroyerService  $destroyer
     * @return void
     */
    public function __construct(Authorizer $authorizer, OwnerRepository $owners, DestroyerService $destroyer)
    {
        $this->authorizer = $authorizer;
        $this->owners = $owners;
        $this->destroyer = $destroyer;
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
        $this->destroyer->clearStores();

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
        $this->destroyer->clearStore($id);

        return response('', 204);
    }

    /**
     * Delete a store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteStore(Request $request, $id)
    {
        // Check permissions.
        $owner = $this->owners->findOrFail($id);
        $this->authorizer->must('owner.delete', $owner);
        $this->authorizer->must('xapi-extra.manage');

        // Do it.
        $this->destroyer->deleteStore($id);

        return response('', 204);
    }
}
