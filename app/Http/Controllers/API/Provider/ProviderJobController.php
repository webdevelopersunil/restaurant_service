<?php

namespace App\Http\Controllers\API\Provider;

use Auth;
use Mail;
use Exception;
use App\Models\Company;
use App\Models\Provider;
use App\Models\JobBooking;
use App\Mail\JobStatusMail;
use Illuminate\Support\Str;
use App\Mail\JobAppliedMail;
use Illuminate\Http\Request;
use App\Models\RestaurantJob;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyJobRequest;
use App\Http\Service\PushNotificationService;
use App\Http\Requests\JobApplicationAcceptRequest;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Trunc;

class ProviderJobController extends Controller{

    public function jobs(Request $request){

        $provider = Provider::where('user_id',Auth::user()->id)->first();

        $responseData = (new RestaurantJob)->providerJobsLatest($provider, $request->text);

        $message        =   __('messages.success');
        $status_code    =   200;
        $status         =   True;

        return common_response( $message, $status, $status_code, $responseData );
    }

    public function jobDetail(Request $request){

        if(!isset($request->uuid)){
            return common_response( 'Job id fields is require', False, 400, [] );
        }

        $job_detail = (new RestaurantJob)->jobDetail($request->uuid);

        return common_response( __('messages.success'), True, 200, $job_detail );
    }

    public function applyJob(ApplyJobRequest $request){
        $user = Auth::user();
        $providerDetail = Provider::where('user_id',Auth::user()->id)->first();
        $responseData   = [];

        if($providerDetail->status!='approved'){

            $message        = __('messages.unapproved_provider');
            $status_code    = 400;
            $status         = False;

        }else{

            $jobDetail = RestaurantJob::where('uuid',$request->job_id)->first(['id','company_id']);

            if( empty($jobDetail) || $jobDetail == Null ){
                return common_response( __('messages.non_exist_error'), False, 402, [] );
            }
            $providerAppliedJob = JobApplication::where(['provider_id'=>$providerDetail->id,'job_id'=>$jobDetail->id])->first();

            if($providerAppliedJob){
                return common_response( __('messages.already_applied'), False, 402, [] );
            }

            $jobApplication = new JobApplication;
            $jobApplication->provider_id    =   $providerDetail->id;
            $jobApplication->job_id         =   $jobDetail->id;
            $jobApplication->rate_type      =   $request->rate_type;
            $jobApplication->rate           =   $request->rate;
            $jobApplication->application_status='Applied';
            $jobApplication->save();

            // Email to Restaurant about Provider applied for job
            $restaurant = Company::where('id',$jobDetail->company_id)->with('user')->first();

            (new PushNotificationService)->providerapplyingJob($restaurant->user->email,Auth::user());

            $notification_data = [
                    'title' => 'New Job Application',
                    'type' => 'email',
                    'message' => "New Job Application. A new Technician has applied for Job.",
                    "ios_badgeType"=>"Increase",
                    "ios_badgeCount"=> 1
                ];


            (new PushNotificationService)->sendNotificationFCM($user,$notification_data);

            $message        = __('messages.provider_applied_job');
            $status_code    = 200;
            $status         = True;

        }

        return common_response( $message, $status, $status_code, $responseData );

    }

    public function applicationAccept(JobApplicationAcceptRequest $request){

        $user               =   Auth::user();
        $provider           =   (new Provider)->provider($user->id);
        $job_application    =   (new JobApplication)->detail($request->application_id);

        $jobRestaurant = RestaurantJob::find($job_application->job_id)->with('company','service','company.user','company.location')->first();

        if($provider->id == $job_application->provider_id && $job_application->application_status=='Offer_Sent'){

            if($request->application_status == 'Offer_Accepted'){

                (new JobApplication)->updateApplicationStatus($request->application_id,'Offer_Accepted');

                $jobBooking = (new JobBooking)->findIfExist($job_application->id);

                // $checkingTimeSlot = JobBooking::whereBetween('end_at',[$jobRestaurant->start_at,$jobRestaurant->ens_at])->where('provider_id',$provider->id)->get();
                // dd($checkingTimeSlot);

                if(empty($jobBooking)){
                    (new JobBooking)->createBooking($job_application);
                    (new RestaurantJob)->updateStatus($job_application->job_id,'InProgress');
                }

                $emailTemplate = 'Job_Offer_Accepted_from_RO';
                $job_title = 'Job Offer Accepted';
                $job_body = 'The Job offer has been accepted by the technician.';

            }else if($request->application_status == 'Offer_Rejected'){

                (new JobApplication)->updateApplicationStatus($request->application_id,'Offer_Rejected');

                $emailTemplate = 'Job_Offer_Rejected_from_RO';
                $job_title = 'Job Offer Declined';
                $job_body = 'The Job offer has been declined by the technician.';
            }

            $details = [
                'toEmail'           => $jobRestaurant->company->user->email,
                'name'              =>$user->first_name.' '.$user->first_name,
                'template'          =>$emailTemplate,
                'restaurant_name'   =>$jobRestaurant->company->location->restaurant_name,
                'service_name'      =>$jobRestaurant->service->name,
            ];

            Mail::queue(new JobStatusMail($details));

            $notification_data = [
                    'title' => $job_title,
                    'type' => 'email',
                    'message' => $job_body,
                    "ios_badgeType"=>"Increase",
                    "ios_badgeCount"=> 1
                ];

            (new PushNotificationService)->sendNotificationFCM($user,$notification_data);

            $status_code    = 200;
            $status         = True;
            $message = __('messages.offer_status_updated');

        }else{

            $status_code    = 402;
            $status         = True;
            $message = __(__('messages.request_was_unacceptable'));
        }

        return common_response( $message, $status, $status_code, [] );
    }

    public function bookingList(){

        $responseData   = [];

        $user = Auth::user();
        $provider = Provider::where('user_id',$user->id)->first('id');
        $bookings_jobs = JobApplication::where('provider_id', $provider->id)->where('application_status', '!=', 'Applied')->with(['job'])->paginate(10);

        if($bookings_jobs){
            $responseData = $bookings_jobs;
            $message        = __('messages.provider_booking_list');
            $status_code    = 200;
            $status         = True;
        }else{

            $status_code    = 402;
            $status         = True;
            $message = __(__('messages.request_was_unacceptable'));
        }
        return common_response( $message, $status, $status_code, $responseData );

    }
}
