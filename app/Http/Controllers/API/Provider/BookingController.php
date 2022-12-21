<?php

namespace App\Http\Controllers\API\Provider;

use App\Models\WorkLog;
use App\Models\Provider;
use App\Models\JobBooking;
use App\Models\RestaurantJob;
use App\Http\Service\GoogleMap;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\WorkLogRequest;
use App\Http\Requests\StartTrackingRequest;
use App\Http\Requests\BookingInvoiceRequest;
use App\Http\Service\PushNotificationService;
use App\Models\Company;
use App\Models\Invoice;

class BookingController extends Controller
{
    public function startTracking(StartTrackingRequest $request){

        $user       =   Auth::user();
        $job        =   (new RestaurantJob)->jobDetail($request->job_id);
        $provider   =   (new Provider)->provider($user->id);
        $status     =   (new GoogleMap)->validateProviderLocation($job->latitude,$job->longitude,$request->all());

        if( $status === True ){
            $booking =   (new JobBooking)->updateBookingStatus($job->id,$provider->id,"In-Progress");

            return common_response( __('messages.success'), True, 200, [] );

        }elseif( $status === False ){

            return common_response( __('messages.not_nearby_of_job'), False, 401, [] );
        }
    }

    public function workLog(WorkLogRequest $request){

        $provider = (new Provider)->provider(Auth::user()->id);
        $response = (new WorkLog)->addWorkLog($request->all(),$provider->id);

        return common_response( $response['message'], True, 200, [] );
    }

    public function bookingInvoice(BookingInvoiceRequest $request){

        $user = Auth::user();

        $booking    =   (new JobBooking)->getDetail($request->booking_id);
        $foundIfAny =   (new WorkLog)->checkIfWorkLog($booking->id);

        if(empty($foundIfAny) || $foundIfAny == Null){
            return common_response( __('messages.work_log_submission_pending'), False, 401, [] );
        }

        $provider   =   (new Provider)->provider($user->id);

        $job        =   RestaurantJob::where('id',$booking->job_id)->first();
        $company    =   Company::find($job->company_id)->with('location')->first();

        if($provider->id === $booking->provider_id){

            (new Invoice)->createInvoice($provider,$booking,$job,$company->location->restaurant_name,$user);

            (new PushNotificationService)->jobCompletionEmailNotification($provider,$booking,$job);

        }else{

            return common_response( __('messages.un_autorized_user'), False, 401, [] );
        }
    }
}
