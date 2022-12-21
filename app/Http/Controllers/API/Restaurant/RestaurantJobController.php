<?php

namespace App\Http\Controllers\API\Restaurant;

use DB;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\JobFile;
use App\Mail\RoSendOffer;
use App\Models\Equipment;
use App\Models\JobBooking;
use Illuminate\Support\Str;
use App\Models\JobEquipment;
use Illuminate\Http\Request;
use App\Models\RestaurantJob;
use App\Models\JobApplication;
use App\Http\Service\GoogleMap;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Http\Service\PushNotificationService;
use App\Http\Requests\JobApplicationIdRequest;
use App\Models\Provider;

class RestaurantJobController extends Controller
{

    public function jobs(Request $request){

        $query = RestaurantJob::query()->with('service')->with('company','files','files.fileDetail');

        if ($request->query('service_id')) {
            $query->whereIn('service_id', $request->service_id);
        }
        if ($request->query('status')) {
            $query->where('status', $request->status);
        }
        if ($request->query('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        $responseData = $query->get();

        $message        =   __('messages.success');
        $status_code    =   200;
        $status         =   True;

        return common_response( $message, $status, $status_code, $responseData );
    }

    public function jobPost(JobPostingRequest $request){

        $responseData = [];

            $data           =   $request->except(['restaurant_image','cusine','files_id']);
            $data['uuid']   =   Str::orderedUuid();
            $company_id = Company::where('user_id',Auth::user()->id)->first('id');
            $data['company_id'] = $company_id->id;

            $jobDetail      =   RestaurantJob::create($data);

            if($request->has('files_id')){
                $filesArr=explode(',',$request->files_id);

                $jobFile = new JobFile;
                foreach($filesArr as $file_id){
                    $jobFile->updateOrCreate(['job_id'=>$jobDetail->id,'file_id'=>$file_id],['job_id'=>$jobDetail->id,'file_id'=>$file_id]);
                }
            }

            if($request->has('equipments_id')){
                $equipmentList = Equipment::whereIn('uuid',$request->equipments_id)->get('id');
                if(count($equipmentList) >= 0){
                    $equipment = new JobEquipment;
                    foreach($equipmentList as $equipment_id){
                        $equipment->updateOrCreate(['job_id'=>$jobDetail->id,'equipment_id'=>$equipment_id->id],['job_id'=>$jobDetail->id,'equipment_id'=>$equipment_id->id]);
                    }
                }
            }

            // Fetch Providers in Radius
            $response = (new GoogleMap)->fetchInRadiusRecords($data['latitude'], $data['longitude'],$data['service_id'],$jobDetail->id);
            (new PushNotificationService)->sendEmailNotification($response);

            $jobDetail->with('files');
            $responseData['uuid'] = $jobDetail->uuid;
            $message            = __('messages.job_created');
            $status_code        = 200;
            $status             = True;

        return common_response( $message, $status, $status_code, $responseData );
    }

    public function mailNearByProviders(){

        $emails = User::join('providers','providers.user_id','=','users.id')->get('email');

        if(count($emails) > 0){
            foreach($emails as $email){
                $details['email'] = $email;
                $details['message'] = "Message";
                dispatch(new \App\Jobs\EmailJob($details));

            }
        }

    }

    public function jobDetail(Request $request){

        if(!isset($request->uuid)){
            return common_response( 'Job id fields is require', False, 400, [] );
        }

        $jobDetail = (new RestaurantJob)->jobDetailRO($request->uuid);

        $message = !empty($jobDetail) ? __('messages.success') : __('messages.no_record_found');

        return common_response( $message, True, 200, $jobDetail );
    }

    public function jobCancel(Request $request){

        if(!isset($request->job_id)){
            return common_response('Job id fields is require',False, 400,[]);
        }

        $responseData = [];

            $company_id = Company::where('user_id',\Auth::user()->id)->first('id');
            $job_creator_id = RestaurantJob::where('uuid',$request->job_id)->first('company_id');

            if( $company_id->id == $job_creator_id->company_id ){

                $job_detail = RestaurantJob::where('uuid',$request->job_id)->first('uuid');
                $message = !empty($job_detail) ? __('messages.cancel_job_success') : __('messages.no_record_found');
                RestaurantJob::where('uuid',$request->job_id)->update(['status'=>'Cancelled']);

            }else{

                $message = __('messages.not_authorized');
            }

            $responseData       = [];
            $status_code        = 200;
            $status             = True;

        return common_response( $message, $status, $status_code, $responseData );
    }

    public function sendOffer(JobApplicationIdRequest $request){

        $user   =   Auth::user();
        $company = (new Company)->company($user->id);

        $jobApplicationDetail   =   JobApplication::where('uuid',$request->application_id)->with('job')->first();

        if(empty($jobApplicationDetail) || $jobApplicationDetail == Null){
            return common_response( __('messages.application_id_does_not_exist'), False, 404, []);
        }

        $provider               =   Provider::where('id',$jobApplicationDetail->provider_id)->with('user')->first();

        if($jobApplicationDetail->job->company_id != $company->id){
            return common_response( __('messages.not_valid_request'), False, 404, []);
        }

        if($jobApplicationDetail->application_status =='Applied' && $jobApplicationDetail->job->company_id == $company->id ){

            (new JobApplication)->updateApplicationStatus($request->application_id,'Offer_Sent');
            (new PushNotificationService)->restaurantSendJobOfferToProvider($provider,$company,$jobApplicationDetail);

            return common_response( __('messages.ro_offer_sent'), True, 200, []);

        }else{

            return common_response( __('messages.offer_has_been_sent_already'), True, 200, []);
        }

    }

    public function getJobApplications(Request $request){

        $company = (new Company)->company(Auth::user()->id);
        $jobApplications = (new JobApplication)->JobApplicationsRO($company->id,$request->uuid);

        return common_response( __('messages.success'), True, 200, $jobApplications);
    }
}
