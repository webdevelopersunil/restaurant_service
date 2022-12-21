<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'job_id',
        'job_application_id',
        'provider_id',
        'status',
        'rate_type',
        'rate',
        'start_at',
        'end_at',
        'duration'
    ];

    protected $hidden = [
        'id',
        'job_id',
        'provider_id'
    ];

    public function createBooking($jobApplication){

        $jobBooking                 =   new JobBooking;
        $jobBooking->uuid           =   Str::orderedUuid();
        $jobBooking->job_id         =   $jobApplication->job_id;
        $jobBooking->job_application_id =$jobApplication->id;
        $jobBooking->provider_id    =   $jobApplication->provider_id;
        $jobBooking->rate_type      =   $jobApplication->rate_type;
        $jobBooking->rate           =   $jobApplication->rate;
        $jobBooking->start_at       =   $jobApplication->job->start_at;
        $jobBooking->end_at         =   $jobApplication->job->end_at;
        $jobBooking->duration       =   NULL;
        $jobBooking->save();

        return $jobBooking;
    }

    public function provider(){
        return $this->belongsTo(Provider::class, 'provider_id','id');
    }

    public function job(){
        return $this->belongsTo(RestaurantJob::class, 'job_id','id');
    }

    public function findIfExist($job_application_id){

        $jobBooking = JobBooking::where('job_application_id',$job_application_id)->first();
        return $jobBooking;
    }

    public function upcomingJobRestaurant($company_id){

        $upcomingJobs = Self::join('restaurant_jobs', 'restaurant_jobs.id', 'job_bookings.job_id')
                        ->join('providers','providers.id','job_bookings.provider_id')
                        ->join('provider_documents','provider_documents.provider_id','providers.id')
                        ->join('files','files.id','provider_documents.file_id')
                        ->join('services','services.id','restaurant_jobs.service_id')
                        ->where('provider_documents.document_type','=','provider_profile_picture')
                        ->where('restaurant_jobs.company_id', $company_id)
                        ->where('job_bookings.start_at', '>=', Carbon::now())
                        ->select('job_bookings.*', 'files.name as provider_profile_pic', 'restaurant_jobs.uuid as jobId', 'services.name as service_name','services.description as service_description','providers.uuid as providerId')
                        ->whereIn('job_bookings.status',['Pending', 'In-Progress'])
                        ->limit(20)->get();
        return  $upcomingJobs;
    }

    public function providerBookings($provider_id,$status){

        $bookings = Self::where('provider_id',$provider_id)
                    ->whereNotIn('status',$status)
                    ->whereDate('start_at', '>=', Carbon::now()->toDateString())
                    ->with('job','job.service','job.company','job.company.file')
                    // ->with('provider')
                    // ->with('provider.documents')
                    ->orderBy('id', 'DESC')
                    ->take(20)->get();

        return $bookings;
    }

    public function updateBookingStatus($job_id,$provider_id,$status){

        $jobBooking = Self::where( ['job_id'=>$job_id, 'provider_id'=>$provider_id] )->update( ['status'=>$status] );

        return $jobBooking;
    }

    public function getDetail($uuid){

        $detail = Self::where('uuid',$uuid)->first();
        return $detail;
    }
}
