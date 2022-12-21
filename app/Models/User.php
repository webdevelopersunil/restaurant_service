<?php

namespace App\Models;

use Mail;
use App\Models\Otp;
use App\Mail\SignUpMail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles, InteractsWithMedia,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid','username',
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_status',
        'user_type',
        'contact_number',
        'email_verified_at',
        'remember_token',
        'handymantype_id',
        'player_id',
        'country_id',
        'state_id',
        'city'
        ,'zipcode',
        'address',
        'provider_id'
        ,'status',
        'display_name',
        'providertype_id',
        'is_featured',
        'time_zone'
        ,'last_notification_seen',
        'login_type',
        'service_address_id',
        'uid',
        'is_subscribe',
        'social_image',
        'is_available',
        'designation',
        'last_online_time',
        'fcm_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'country_id'        => 'integer',
        'state_id'          => 'integer',
        'city'           => 'string',
        'contact_number'    => 'integer',
        'zipcode'           => 'integer',
        'is_featured'       => 'integer',
        'providertype_id'   => 'integer',
        'provider_id'       => 'integer',
        'service_address_id' => 'integer',
        'status'            => 'integer',
        'handymantype_id'            => 'integer',
        'service_address_id'            => 'integer',
        'is_subscribe'            => 'integer',
        'is_available'            => 'integer',
    ];

    protected static function boot(){
        parent::boot();

        /* static::created(function($user) {
            $user->assignRole('user');
        }); */
        static::deleted(function ($row) {
            switch ($row->user_type) {
                case 'provider':
                    if($row->forceDeleting === true){
                        $row->providerService()->forceDelete();
                        $row->providerBooking()->forceDelete();
                    }else{
                        $row->providerService()->delete();
                        $row->providerBooking()->delete();
                    }
                    break;

                case 'handyman':
                    if($row->forceDeleting === true){
                        $row->handyman()->forceDelete();
                    }else{
                        $row->handyman()->delete();
                    }
                    break;

                case 'customer':
                    if($row->forceDeleting === true){
                        $row->booking()->forceDelete();
                        $row->payment()->forceDelete();
                    }else{
                        $row->booking()->delete();
                        $row->payment()->delete();
                    }
                    break;

                default:
                    # code...
                    break;
            }
        });
        static::restoring(function($row) {
            switch ($row->user_type) {
                case 'provider':
                    $row->providerService()->withTrashed()->restore();
                    $row->providerBooking()->withTrashed()->restore();
                    break;

                case 'handyman':
                   $row->handyman()->withTrashed()->restore();
                    break;

                case 'customer':
                   $row->booking()->withTrashed()->restore();
                   $row->payment()->withTrashed()->restore();
                    break;

                default:
                    # code...
                    break;
            }
        });
    }

    public function country(){
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function state(){
        return $this->belongsTo(State::class, 'state_id','id');
    }

    public function providertype(){
        return $this->belongsTo(ProviderType::class, 'providertype_id','id');
    }

    public function providers(){
        return $this->belongsTo(User::class, 'provider_id','id');
    }

    public function handyman(){
        return $this->hasMany(BookingHandymanMapping::class, 'handyman_id','id');
    }

    public function booking(){
        return $this->hasMany(Booking::class, 'customer_id','id');
    }

    public function payment(){
        return $this->hasMany(Payment::class, 'customer_id','id');
    }

    public function routeNotificationForOneSignal(){
        return $this->player_id;
    }

    protected function getUserByKeyValue($key,$value){
        return $this->where($key, $value)->first();
    }
    public function providerTaxMapping(){
        return $this->hasMany(ProviderTaxMapping::class, 'provider_id','id');
    }
    public function providerTaxMappingData(){
        return $this->hasMany(ProviderTaxMapping::class, 'provider_id','id')->with('taxes');
    }

    public function scopeMyUsers($query,$type=''){
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            if($type === 'get_provider'){
                $query->where('user_type', 'provider')->where('status',1);
            }
            if($type === 'get_customer'){
                $query->where('user_type', 'user');
            }
            return $query;
        }
        if($user->hasRole('provider')) {
            return $query->where('user_type', 'handyman')->where('provider_id',$user->id);
        }
    }
    public function providerService(){
        return $this->hasMany(Service::class, 'provider_id','id');
    }
    public function providerHandyman(){
        return $this->hasMany(User::class, 'provider_id','id');
    }
    public function getServiceRating(){
        return $this->hasManyThrough(
            BookingRating::class,
            Service::class,
            'provider_id', // services
            'service_id', // booking rating
            'id', // users
            'id' // services
        );
    }

    public function providerBooking(){
        return $this->hasMany(Booking::class, 'provider_id','id');
    }
    public function providerPendingBooking(){
        return $this->hasMany(Booking::class, 'provider_id','id')->whereNull('payment_id');
    }
    public function handymanPendingBooking(){
        return $this->hasMany(BookingHandymanMapping::class, 'handyman_id','id')->whereHas('bookings', function($q){
            $q->whereNull('payment_id');
        });
    }
    public function handymanAddressMapping(){
        return $this->belongsTo(ProviderAddressMapping::class, 'service_address_id','id');
    }

    public function handymanRating(){
        return $this->hasMany(HandymanRating::class, 'handyman_id','id');
    }

    public function providerDocument(){
        return $this->hasMany(ProviderDocument::class, 'provider_id','id');
    }
    public function handymantype(){
        return $this->belongsTo(HandymanType::class, 'handymantype_id','id');
    }
    public function subscriptionPackage(){
        return $this->hasOne(ProviderSubscription::class, 'user_id','id')->where('status',config('constant.SUBSCRIPTION_STATUS.ACTIVE'));
    }


    // -------------New  Relation-------------

    public function sendPasswordResetNotification($token)
    {
        $email = $this->email;
        $otp = rand(111111,999999);
        Otp::UpdateOrCreate(['identifier'=>$email],['identifier'=>$email,'otp'=>$otp,'validity'=>15,'valid'=>1]);

        return $this->notify(new ResetPassword($token,$otp));
    }

    public function companies(){
        return $this->hasMany(Company::class, 'user_id','id')->with('location');
    }

    public function company(){
        return $this->hasOne(Company::class, 'user_id','id')->with('equipments', 'file', 'location', 'subsciptionPlan');
    }

    public function currentCompany(){
        return $this->hasOne(Company::class, 'user_id','id');
    }

    public function provider(){
        return $this->hasOne(Provider::class, 'user_id','id');
    }

    public function UserRole(){
        return $this->belongsTo(UserRole::class, 'id', 'user_id')->with('roleDetail');
    }

   /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail); // my notification
    }

    public function updatefcmToken($fcm_token,$user_id){
        $user = User::find($user_id);
        $user->fcm_token = $fcm_token;
        $user->save();
    }
}
