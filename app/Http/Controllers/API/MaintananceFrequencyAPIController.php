<?php

namespace App\Http\Controllers\API;

use Response;
use Exception;
use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Models\MaintananceFrequency;
use App\Http\Controllers\AppBaseController;
use App\Repositories\MaintananceFrequencyRepository;
use App\Http\Requests\API\CreateMaintananceFrequencyAPIRequest;
use App\Http\Requests\API\UpdateMaintananceFrequencyAPIRequest;

/**
 * Class MaintananceFrequencyController
 * @package App\Http\Controllers\API
 */

class MaintananceFrequencyAPIController extends AppBaseController
{
    /** @var  MaintananceFrequencyRepository */
    private $maintananceFrequencyRepository;

    public function __construct(MaintananceFrequencyRepository $maintananceFrequencyRepo)
    {
        $this->maintananceFrequencyRepository = $maintananceFrequencyRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @OA\Get(
     *      path="/maintananceFrequencies",
     *      summary="Get a listing of the MaintananceFrequencies.",
     *      tags={"MaintananceFrequency"},
     *      description="Get all MaintananceFrequencies",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/definitions/MaintananceFrequency")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $maintananceFrequencies = $this->maintananceFrequencyRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($maintananceFrequencies->toArray(), 'Maintanance Frequencies retrieved successfully');
    }

    /**
     * @param CreateMaintananceFrequencyAPIRequest $request
     * @return Response
     *
     * @OA\Post(
     *      path="/maintananceFrequencies",
     *      summary="Store a newly created MaintananceFrequency in storage",
     *      tags={"MaintananceFrequency"},
     *      description="Store MaintananceFrequency",
     *      @OA\Parameter(
     *          name="body",
     *          in="path",
     *          description="MaintananceFrequency that should be stored",
     *          required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/definitions/MaintananceFrequency"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMaintananceFrequencyAPIRequest $request)
    {

        $response = [];

        $input = $request->all();
        // $maintananceFrequency = $this->maintananceFrequencyRepository->create($input);


        $equipmentDetail = Equipment::where('uuid',$input['equipment_id'])->first('id');
        if( !isset($equipmentDetail->id) ){
            return common_response( "Equipment id is not valid", False, 400, [] );
        }
        $input['equipment_id'] = $equipmentDetail->id;
        $maintananceFrequency = MaintananceFrequency::updateOrCreate(['equipment_id'=>$equipmentDetail->id],$input);

        $response       = [ 'id' => $maintananceFrequency->id ];
        $message        = __('messages.maintanance_frequency_saved_successfully');
        $status_code    = 200;
        $status         = True;

        return common_response( $message, $status, $status_code, $response );
    }

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Get(
     *      path="/maintananceFrequencies/{id}",
     *      summary="Display the specified MaintananceFrequency",
     *      tags={"MaintananceFrequency"},
     *      description="Get MaintananceFrequency",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MaintananceFrequency",
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/definitions/MaintananceFrequency"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        /** @var MaintananceFrequency $maintananceFrequency */
        $maintananceFrequency = $this->maintananceFrequencyRepository->find($id);

        if (empty($maintananceFrequency)) {
            return $this->sendError('Maintanance Frequency not found');
        }

        return $this->sendResponse($maintananceFrequency->toArray(), 'Maintanance Frequency retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateMaintananceFrequencyAPIRequest $request
     * @return Response
     *
     * @OA\Put(
     *      path="/maintananceFrequencies/{id}",
     *      summary="Update the specified MaintananceFrequency in storage",
     *      tags={"MaintananceFrequency"},
     *      description="Update MaintananceFrequency",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MaintananceFrequency",
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Parameter(
     *          name="body",
     *          in="path",
     *          description="MaintananceFrequency that should be updated",
     *          required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/definitions/MaintananceFrequency"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMaintananceFrequencyAPIRequest $request)
    {

        $response = [];
        $input = $request->all();

        /** @var MaintananceFrequency $maintananceFrequency */
        $maintananceFrequency = $this->maintananceFrequencyRepository->find($id);

        if (empty($maintananceFrequency)) {
            return $this->sendError('Maintanance Frequency not found');
        }
        $maintananceFrequency = $this->maintananceFrequencyRepository->update($input, $id);


        $response       = $maintananceFrequency->toArray();
        $message        = 'MaintananceFrequency updated successfully';
        $status_code    = 200;
        $status         = True;

        return common_response( $message, $status, $status_code, $response );
    }

    /**
     * @param int $id
     * @return Response
     *
     * @OA\Delete(
     *      path="/maintananceFrequencies/{id}",
     *      summary="Remove the specified MaintananceFrequency from storage",
     *      tags={"MaintananceFrequency"},
     *      description="Delete MaintananceFrequency",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MaintananceFrequency",
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id)
    {

        $response = [];

        /** @var MaintananceFrequency $maintananceFrequency */
        $maintananceFrequency = $this->maintananceFrequencyRepository->find($id);

        if (empty($maintananceFrequency)) {
            return $this->sendError('Maintanance Frequency not found');
        }
        $maintananceFrequency->delete();

        $message        = __('messages.maintanance_deleted');
        $status_code    = 200;
        $status         = True;

        return common_response( $message, $status, $status_code, $response );


    }
}
