<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SignUpMail;
use App\Http\Service\PushNotificationService;

class VerifyEmailController extends Controller
{

    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect('verified-success')->with('verified', true);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $emailTemplate = get_email_template('thankyou_email_after_verify_account');
            if($emailTemplate){
                Mail::to($user->email)->send(new SignUpMail($user));

                $notification_data = [
                    'title' => 'Email Verification',
                    'type' => 'email',
                    'message' => "Welcome ". $user->first_name."! Welcome to the 2Top Tech team.",
                    "ios_badgeType"=>"Increase",
                    "ios_badgeCount"=> 1
                ];

                (new PushNotificationService)->sendNotificationFCM($user,$notification_data);
            }
            
        }
        return redirect('verified-success')->with('verified', true);
    }

}