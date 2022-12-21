<?php

namespace App\Http\Controllers\API\Restaurant;

use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\RestaurantJob;
use App\Http\Controllers\Controller;
use App\Models\JobBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(){

        $response = array();
        $response['services']       =   (new Service)->All();
        $company                    =   (new Company)->company(Auth::user()->id);
        $response['jobs']           =   (new RestaurantJob)->jobsRO($company->id);
        $response['upcoming_jobs']  =   (new JobBooking)->upcomingJobRestaurant($company->id);

        return common_response(__('messages.success'), True, 200, $response);
    }
}
