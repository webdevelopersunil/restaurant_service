<?php

namespace App\Http\Controllers\API\Restaurant;

use DB;
use Hash;
use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanyInvite;
use App\Mail\SignUpMail;
use App\Models\UserRole;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Service\GoogleMap;
use App\Models\CompanyLocation;
use App\Models\RestaurantCuisine;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RestaurantRequest;
use App\Http\Requests\RestaurantProfileRequest;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\API\CreateReferRequest;
use App\Mail\SendReferralEmail;

class RestaurantController extends Controller
{

    public function register(RestaurantRequest $request){

        $responseData   =   [];
        $data = [
            'uuid'   =>   Str::orderedUuid(),
            'email'         =>$request->email,
            'display_name'  =>$request->full_name,
            'password'      =>Hash::make($request->password),
            'contact_number'=>$request->contact_number
        ];

        $user = User::create($data);
        $user_id = $user->id;

        event(new Registered($user));

        // Updating fcm_token
        (new User)->updatefcmToken($request->fcm_token,$user_id);

        //Assign role provider
        $role  =(new UserRole)->assignRestaurantRole($user_id);

        if($user_id){

            // Registering a Company
            $companyDetail = [
                'uuid'      =>  Str::orderedUuid(),
                'user_id'   =>  $user_id,
                'status'    =>  'pending',
                'subscription_status'   =>  'pending',
                'business_name'=>$request->business_name,
            ];
            $company = Company::create($companyDetail);
            $company_id = $company->id;

            // Registering a Company Restaurant
            if($company_id){
                $companyLocationDetalil = [
                    'uuid'          =>  Str::orderedUuid(),
                    'company_id'    =>  $company_id,
                    'phone_number'  =>  $request->contact_number,
                    'contact_name'  =>  $request->full_name,
                    'email'         =>  $request->email,
                    'city'          =>  $request->city,
                    'latitude'      =>  $request->latitude,
                    'longitude'     =>  $request->longitude,
                ];
                CompanyLocation::create($companyLocationDetalil);
            }
            //Generating Auth Token
            $responseData['api_token']   = $user->createToken('auth_token')->plainTextToken;
            $responseData['display_name']= $user->display_name;
            $responseData['uuid']        = $user->uuid;
            $responseData['role']        = $role->name;

        }

        $message        =   __('messages.register_restaurant');
        $status_code    =   200;
        $status         =   True;

        return common_response( $message, $status, $status_code, $responseData );

    }


    public function updateProfile(RestaurantProfileRequest $request){

        $user = \Auth::user();
        $user_id = $user->id;
        $responseData = [];

        if( isset($request->cusine) && count($request->cusine) > 0 ){
            $cuisines = $request->cusine;
        }

        $data = $request->except(['logo_file_id','cusine']);

        $restaurant = Company::where('user_id',$user_id)->first('id');
        $restaurant->updateOrCreate(['id'=>$restaurant->id],['logo_file_id'=>$request->logo_file_id]);

        //creating lat-long for provided address
        $latlong = (new GoogleMap)->getLatLong(['city'=>$data['city'], 'address'=>$data['address'],'state_id'=>$data['state_id'] ]);
        $data['latitude'] = $latlong['latitude'];
        $data['longitude']   =$latlong['longitude'];

        CompanyLocation::updateOrCreate(['company_id'=>$restaurant->id],$data);

        if(isset($cuisines)){
            RestaurantCuisine::whereNotIn('cuisine_id',$cuisines)->delete();
            $restaurantCuisine = (new RestaurantCuisine);
            foreach($cuisines as $cuisine){
                $restaurantCuisine->updateOrCreate(['cuisine_id'=>$cuisine,'restaurant_id'=>$restaurant->id],['cuisine_id'=>$cuisine,'restaurant_id'=>$restaurant->id] );
            }
        }

        $responseData   =   Company::where('user_id',$user->id)->with('location')->with('cuisines','file')->first();
        $responseData['restaurant_profile'] = getSingleMedia($user,'restaurant_profile',null);

        $message        =   __('messages.updated');
        $status_code    =   200;
        $status         =   True;


        return common_response( $message, $status, $status_code, $responseData );

    }

    public function profile(Request $request){

        $responseData = User::where('id',\Auth::user()->id)->with('company','company.file.fileDetail','company.location')
        ->with('company.cuisines','company.cuisines.cuisine')
        ->first();

        return common_response( __('messages.success'), True, 200, $responseData );
    }

    public function refer(CreateReferRequest $request){

        $details = [];
        $details['name'] = $request->name;
        $details['email'] = $request->email;
        $userCompany = User::where('id',\Auth::user()->id)->with('currentCompany', 'UserRole')->first();

        $details['restaurant_name'] = ucfirst($userCompany->first_name) . " " . $userCompany->last_name;
        $company_id = $userCompany->currentCompany->id;
        $role_id = $userCompany->UserRole->role_id;

        if($company_id && $role_id){
            $request['company_id'] = $company_id;
            $request['role_id'] = $role_id;
            $request['status'] = 'pending';
            
            $data = $request->all();
            $responseData = CompanyInvite::create($data);

             Mail::to( $request['email'])->send(new SendReferralEmail($details));

            $message        =   __('messages.referredSuccess');
            $status_code    =   200;
            $status         =   True;

            return common_response( $message, $status, $status_code, $responseData );   
        }
        
       
    }
}
