<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = "job_applications";

    protected $fillable = [
        'uuid',
        'job_id',
        'provider_id',
        'application_status',
        'comment',
        'rate_type',
        'rate'
    ];

    protected $hidden = [
        'id',
        'job_id',
        'provider_id'
    ];

    protected $primaryKey = "id";

    public function job(){
        return $this->belongsTo(RestaurantJob::class, 'job_id','id')->with('service');
    }

    public function provider(){
        return $this->belongsTo(Provider::class, 'id','provider_id');
    }

    public function providerDetail(){
        return $this->hasOne(Provider::class, 'id','provider_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function updateApplicationStatus($uuid,$status){

        $application = self::where('uuid',$uuid)->update( ['application_status'=>$status] );
        return $application;
    }

    public function detail($id){

        $detail =   self::where('uuid',$id)->first();
        return $detail;
    }

    public function providerOffers($provider_id,$status){

        $offers =   Self::where('provider_id',$provider_id)
                    ->whereIn('application_status',$status)->orderBy('id', 'DESC')
                    ->with('job','job.service')->take(20)->get();

        return $offers;
    }

    public function JobApplicationsRO($company_id,$uuid){

        $jobApplications   =   JobApplication::join('restaurant_jobs', 'job_applications.job_id', '=', 'restaurant_jobs.id')
                                ->join('providers','providers.id','=','job_applications.provider_id')
                                ->join('users','users.id','=','providers.user_id')
                                ->join('services','services.id','=','restaurant_jobs.service_id')
                                ->join('provider_documents','provider_documents.provider_id','=','providers.id')
                                ->join('files','files.id','=','provider_documents.file_id')
                                ->where('restaurant_jobs.company_id',$company_id)
                                ->where('restaurant_jobs.uuid',$uuid)
                                ->where('provider_documents.document_type','provider_profile_picture')
                                ->where('job_applications.application_status','Applied')
                                ->with('providerDetail')
                                ->select('job_applications.*','users.first_name as provider_first_name','users.last_name as provider_last_name','services.name as service_name','services.description as service_description','files.name as provider_profile_pic')->get();

        return  $jobApplications;
    }
}
