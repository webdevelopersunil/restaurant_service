<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BookingRating extends Model
{
    use HasFactory;
    protected $table = 'ratings';
    protected $fillable = [
        'booking_id', 'rating_type', 'rating_comment' , 'rate', 'rating_by'
    ];

    /**
         * Validation rules
         *
         * @var array
         */
    public static $rules = [

    ];
    protected $casts = [
        'booking_id'    => 'integer',
        'rating_by'    => 'integer',
    ];

    // public function customer()
    // {
    //     return $this->belongsTo(User::class, 'customer_id', 'id');
    // }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'id', 'booking_id');
    }

    // public function service(){
    //     return $this->belongsTo(Service::class, 'service_id', 'id');
    // }



    public function scopeMyRating($query){
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            $query =  $query;
        }

        if($user->hasRole('provider')) {
            $query = $query->whereHas('service',function ($q) use($user) {
                $q->where('provider_id',$user->id);
            });
        }

        return  $query;
    }
}
