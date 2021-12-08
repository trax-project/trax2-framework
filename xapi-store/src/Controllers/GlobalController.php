<?php

namespace Trax\XapiStore\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Trax\Auth\Authorizer;
use Trax\Auth\Stores\Owners\OwnerRepository;
use Trax\XapiStore\Services\Cleaner\CleanerService;
use Trax\XapiStore\Services\Cleaner\MaxDeletableException;

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
     * @var \Trax\XapiStore\Services\Cleaner\CleanerService
     */
    protected $cleaner;


    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Authorizer  $authorizer
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @param  \Trax\XapiStore\Services\Cleaner\CleanerService  $cleaner
     * @return void
     */
    public function __construct(Authorizer $authorizer, OwnerRepository $owners, CleanerService $cleaner)
    {
        $this->authorizer = $authorizer;
        $this->owners = $owners;
        $this->cleaner = $cleaner;
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
        $this->cleaner->deleteStore($id);

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
        try {
            $this->cleaner->clearStore($id);
        } catch (MaxDeletableException $e) {
            abort(409);
        }

        return response('', 204);
    }

    /**
     * Clear the statements of a store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function clearStoreStatements(Request $request, $id)
    {
        // Check permissions.
        $this->authorizer->must('xapi-extra.manage');

        // Get the filters.
        $filters = $request->input('filters');
        unset($filters['owner_id']);
        
        // Do it.
        try {
            $this->cleaner->clearStoreStatements($id, $filters);
        } catch (MaxDeletableException $e) {
            abort(409);
        }

        return response('', 204);
    }
}
