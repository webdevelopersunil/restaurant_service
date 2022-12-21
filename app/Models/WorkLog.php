<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_id',
        'comment',
        'completion_status',
        'type',
        'log_time'
    ];

    protected $hidden = [
        'id'
    ];

    public function booking(){
        return $this->belongsTo(JobBooking::class, 'booking_id','id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function createWorkLog($type,$comment,$booking_id){

        $workLog            =   new WorkLog;
        $workLog->type      = $type;
        $workLog->completion_status =   Null;
        $workLog->comment   =   $comment;
        $workLog->booking_id=   $booking_id;
        $workLog->log_time  =   Carbon::now();
        $workLog->save();
    }

    public function addWorkLog($data,$provider_id){
        // booking_id
        // comment

        $jobBooking =   JobBooking::where('uuid',$data['booking_id'])->first();

        switch($data['type']) {

            case('Pause'):

                $booking = (new JobBooking)->updateBookingStatus($jobBooking->job_id,$provider_id,"Puase");
                $foundIfAny = Self::where(['type'=>'Pause', 'booking_id'=>$booking])->first();

                if(empty($foundIfAny)){
                    $this->createWorkLog('Pause',$data['comment'],$booking);
                }

                $response['message']    =   __('messages.success');
                break;

            case('Restart'):

                $booking = (new JobBooking)->updateBookingStatus($jobBooking->job_id,$provider_id,"In-Progress");

                $this->createWorkLog('Restart',$data['comment'],$booking);

                $response['message']    =   __('messages.success');
                break;

            case('WorkLog'):

                $booking = (new JobBooking)->updateBookingStatus($jobBooking->job_id,$provider_id,"Complete");

                $this->createWorkLog('WorkLog',$data['comment'],$booking);

                $response['message']    =   __('messages.success');
                break;

            default:
                $response['message'] = 'Something went wrong.';
        }
        return $response;
    }

    public function checkIfWorkLog($booking_id){

        $foundIfAny = Self::where('booking_id',$booking_id)->where('type','WorkLog')->first();

        return $foundIfAny;
    }
}
