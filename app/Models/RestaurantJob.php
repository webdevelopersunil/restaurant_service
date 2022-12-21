<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Media;

class RestaurantJob extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'company_id',
        'service_id',
        'description',
        'schedule_type',
        'status',
        'start_at',
        'end_at',
        'restaurant_name',
        'restaurant_location',
        'latitude',
        'longitude',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'company_id'
    ];

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function equipments()
    {
        return $this->hasMany(JobEquipment::class, 'job_id', 'id');
    }

    public function service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id')->with('mediaFile');
    }

    public function files()
    {
        return $this->hasMany(JobFile::class, 'job_id', 'id');
    }

    public function applications(){
        return $this->hasMany(JobApplication::class, 'job_id', 'id');
    }

    public function updateStatus($job_id,$status){

        $response = self::where('id',$job_id)->update([ 'status' => $status ]);
        return $response;
    }

    public function providerJobsLatest($provider,$text){

        $query  =   self::query()->select(DB::raw('restaurant_jobs.id,restaurant_jobs.uuid,restaurant_jobs.service_id,restaurant_jobs.company_id,restaurant_jobs.description, SQRT(POW(69.1 * (restaurant_jobs.latitude - '.$provider->latitude.'), 2) + POW(69.1 * ('.$provider->longitude.'-restaurant_jobs.longitude) * COS(restaurant_jobs.latitude / 57.3), 2)) AS distance'))
                    ->havingRaw('distance <='.$provider->preferred_distance)
                    ->join('provider_services','restaurant_jobs.service_id','=','provider_services.service_id')
                    ->where('provider_services.provider_id',$provider->id)
                    ->where('restaurant_jobs.status','Pending')
                    ->with('service')->with('company','files','files.fileDetail');

        if ($text != Null && !empty($text)) {
            $query->where('restaurant_jobs.description','like', '%'.$text.'%');
        }

        return $query->paginate(10);
    }

    public function jobs($provider){

        $data   =   Self::query()->select('restaurant_jobs.*',DB::raw('restaurant_jobs.id,restaurant_jobs.uuid,restaurant_jobs.service_id,restaurant_jobs.company_id,restaurant_jobs.description, SQRT(POW(69.1 * (restaurant_jobs.latitude - '.$provider->latitude.'), 2) + POW(69.1 * ('.$provider->longitude.'-restaurant_jobs.longitude) * COS(restaurant_jobs.latitude / 57.3), 2)) AS distance'))
                    ->havingRaw('distance <='.$provider->preferred_distance)
                    ->join('provider_services','restaurant_jobs.service_id','=','provider_services.service_id')
                    ->where('provider_services.provider_id',$provider->id)
                    ->where('restaurant_jobs.status','Pending')
                    ->with('service')->with('files','files.fileDetail','company')->take(20);

        return $data->get();
    }

    public function jobDetail($uuid){

        $jobDetail  =   Self::join('job_applications', 'job_applications.job_id', '=', 'restaurant_jobs.id')
                        ->join('job_bookings','job_bookings.job_application_id','=','job_applications.id')
                        ->where('restaurant_jobs.uuid',$uuid)
                        ->select('restaurant_jobs.*', 'job_applications.application_status as application_status','job_bookings.status as job_bookings_status','job_bookings.duration as booking_duration','job_bookings.rate_type as booking_rate_type','job_bookings.rate as booking_rate','job_bookings.uuid as booking_id')
                        ->with('equipments.equipment','equipments.equipment.file')
                        ->with('service')->with('company','files','files.fileDetail')->first();

        return $jobDetail;
    }

    public function jobDetailRO($uuid){

        $jobDetail  =   Self::where('uuid',$uuid)
                        ->with('files','files.fileDetail','service','equipments.equipment.file')
                        ->with('applications','applications.providerDetail')
                        ->first();

        return $jobDetail;
    }

    public function jobsRO($company_id){

        $jobs =     Self::where('company_id', $company_id)->where('status', 'Pending')
                    ->with('service')
                    ->limit(20)->orderBy('id', 'ASC')->get();

        return $jobs;
    }
}
