<?php

namespace App\Http\Controllers\API;

use Auth;
use Stripe;
use Exception;
use App\Models\Plans;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\CompanyPayment;
use Illuminate\Support\Carbon;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyPaymentsRequest;

class StripeController extends Controller
{

    public function craeteSubscription(CompanyPaymentsRequest $request)
    {

        $responseData = [];

        try {

            $user = Auth::user();
            $planDetail = Plans::where('id', $request->plan_id)->first();
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $customer = Stripe\Customer::create(array(
                "email" => $user->email,
                "name" => $user->display_name,
                'source' => $request->card_token
            ));

            $subscriptions = Stripe\Subscription::create([
                'customer'      => $customer->id,
                'items' => [
                    ['price' => $planDetail->plan_price_id],
                ],
            ]);

            if (($subscriptions && $subscriptions['status'] && $subscriptions['status'] == 'active') || ($request->plan == '1')) {

                // Sending Mail to Restaurant and Admin
                $details = array('email' => $user->email);
                dispatch(new \App\Jobs\SubscriptionJob($details));

                $expire_at =  $planDetail->type == 'monthly' ? Carbon::now()->addMonth(1) : Carbon::now()->addMonth(12);
                $company = Company::where('user_id', $user->id)->update(['stripe_customer_id' => $customer->id, 'subscription_status' => 'active', 'stripe_subscription_id' => $subscriptions->id, 'expires_at' => $expire_at]);
                $subscriberPlan = (new SubscriptionPlan)->createSubrcriptionPlan($planDetail, $company, $expire_at);

                $message            = __('messages.subscribed_successfully');
                $responseData       = [];
                $status_code        = 200;
                $status             = True;
            }
        } catch (\Stripe\Exception\InvalidRequestException $e) {

            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $status =   $e->getError()->code;
            $status_code = $e->getHttpStatus();
            $message = $e->getError()->message;
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $status = $e->getHttpStatus();
            echo 'Type is:' . $e->getError()->type;
            $status_code = $e->getError()->code;
            $message = $e->getError()->message;
        }

        return common_response($message, $status, $status_code, $responseData);
    }
}
