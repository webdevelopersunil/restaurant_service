<?php

namespace App\Http\Service;

use App\Models\User;
use App\Mail\SendMail;
use App\Models\Provider;
use App\Jobs\SendMailJob;
use App\Mail\RoSendOffer;
use App\Mail\JobAppliedMail;
use App\Jobs\JobPostEmailNotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use  App\Models\Notification;

class PushNotificationService
{

    public static function dispatch($data){
        dispatch(new SendMailJob($data));
    }

    public function sendEmailNotification($data){

        if (count($data['providers']) > 0) {

            $job_id = $data['job_id'];

            foreach ($data['providers'] as $provider) {

                // $provider->email;
                // $provider->user_id;
                // $provider->provider_id;
                // $provider->fcm_token;
                // $provider->preferred_distance;
                // $provider->distance;

                $data = [
                    'toEmail' => $provider->email,
                ];
                //Notifying with Mail to all providers under radius range
                dispatch(new JobPostEmailNotify($data));
            }
        }
    }

    public function restaurantSendJobOfferToProvider($provider,$company,$jobApplicationDetail){

        $details = [
            'toEmail'           =>  $provider->user->email,
            'name'              =>$provider->user->first_name.' '.$provider->user->last_name,
            'template'          =>'send_offer_to_provider',
            'restaurant_name'   =>$company->business_name,
            'service_name'      =>$jobApplicationDetail->job->service->name,
        ];

        Mail::queue(new RoSendOffer($details));


        $notification_data = [
            'id'   => '1',
            'title' => 'New job offer',
            'type' => 'email',
            'message' => "New job offer. The Job ". $jobApplicationDetail->job->service->name ." you had applied for, has been accepted.",
            "ios_badgeType"=>"Increase",
            "ios_badgeCount"=> 1
        ];

        $this->sendNotificationFCM($provider->user, $notification_data);
        // Mail::to($details['toEmail'])->send(new RoSendOffer($details));

        return True;
    }

    public function providerapplyingJob($email,$user){

        $details = [
            'toEmail'=> $email,
            'title' =>  $user->first_name.' '.$user->last_name.' Provider applied for a JOb',
            'body'  =>  '',
        ];

        Mail::queue(new JobAppliedMail($details));
        // Mail::to($details['toEmail'])->send(new JobAppliedMail($details));

        return True;
    }

    public function partRequestMailToAdmin($partRequest,$provider){

        $data = [
            ':admin'        =>  'AdminName',
            ':name'         =>  Auth::user()->first_name.' '.Auth::user()->last_name,
            ':parts_detail' =>  '',
        ];
        $toMail = env('ADMIN_EMAIL');

        $emailTemplate = get_email_template('part_request_email');
        $email_body = getFormattedEmailData($data, $emailTemplate->email_body);
        $subject = getFormattedEmailData($data, $emailTemplate->email_subject);

        $data = array(
            'email_body'=>$email_body,
            'subject'   =>$subject,
            'toMail'    =>$toMail
        );

        $this->dispatch($data);
    }

    public function bankInfoUpdatedMailTOAdmin($providerBankInfo){

        $provider_id    = $providerBankInfo->provider_id;
        $uuid           = $providerBankInfo->uuid;

        $data = [
            ':admin'        =>  'AdminName',
            ':name'         =>  Auth::user()->first_name.' '.Auth::user()->last_name,
            ':parts_detail' =>  '',
        ];

        $toMail = env('ADMIN_EMAIL');

        $emailTemplate = get_email_template('bank_detail_updated');
        $email_body = getFormattedEmailData($data, $emailTemplate->email_body);
        $subject = getFormattedEmailData($data, $emailTemplate->email_subject);

        $data = array(
            'email_body'=>$email_body,
            'subject'   =>$subject,
            'toMail'    =>$toMail
        );

        $this->dispatch($data);

    }

    public function jobCompletionEmailNotification($n){
        //
    }

    public function sendNotificationFCM($user,$data){

        $user = User::where('id', $user->id)->with('UserRole')->first();
        if($user && $user->UserRole && $user->UserRole->roleDetail && $user->UserRole->roleDetail->name=== 'restaurant'){
             $accesstoken = "key=".env('RESTAURANT_FCM_KEY');
         }elseif($user->UserRole->roleDetail->name=== 'provider'){
             $accesstoken = "key=".env('TECHNICIAN_FCM_KEY');
         }

        $post_data = '{
            "to":"'.$user->fcm_token.'",
            "data" : {
                "body" : "'.$data['message'].'",
                "title" : "' . $data['title'] . '",
                "type" : "' . $data['type'] . '",
                "id" :"' . $user->id . '",
                "message" : "'.$data['message'].'"
            },
            "notification" : {
                 "body" : "' . $data['message'] . '",
                 "title" : "' . $data['title'] . '",
                  "type" : "' . $data['type'] . '",
                 "id" : "' . $user->id . '",
                 "message" : "' . $data['message'] . '",
                "icon" : "new",
                "sound" : "default"
                },

        }';

        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: ' . $accesstoken;

        $curl = curl_init();
        $URL = 'https://fcm.googleapis.com/fcm/send';
        curl_setopt_array($curl, array(
          CURLOPT_URL => $URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$post_data,
          CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $notification = Notification::create(
            array(
                'type' => $data['type'],
                'title' => $data['title'],
                'notifiable_type'=> 'App\Models\User',
                'notifiable_id'=>$user->id,
                'description'=>$data['message']
            )
        );

    }
}
