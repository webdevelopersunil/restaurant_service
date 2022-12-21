<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\State;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
            'uuid',
            'user_id',
            'bussiness_name',
            'contact_number',
            'address',
            'city',
            'zipcode',
            'state_id',
            'dob',
            'ssn',
            'experience_years',
            'education',
            'previous_employer',
            'referral',
            'trade_education',
            'bio',
            'preferred_distance',
            'insurance',
            'trade_organization',
            'hourly_rate',
            'weekend_rate',
            'status',
            'bank_info_status',
            'longitude',
            'latitude',
            'fcm_token'
    ];

    protected $hidden = [
        'id',
        'user_id'
    ];

    public function updateBankStatus($id){
        $provider = self::find($id);
        $provider->bank_info_status = 'True';
        $provider->save();
    }

    public function getProfile($provider_id){

        $provider = Provider::where('id',$provider_id)->with('services','documents','states')->first();
        return $provider;
    }

    public function states(){
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function services(){
        return $this->hasMany(ProviderService::class, 'provider_id','id')->with('service');
    }

    public function documents(){
        return $this->hasMany(ProviderDocument::class, 'provider_id','id')->with('document');
    }

     public function user()
    {
         return $this->belongsTo(User::class, 'user_id','id');
    }


    public function providerPatymentMethod()
    {
        return $this->hasOne(\App\Models\ProviderPaymentMethod::class, 'provider_id', 'id');
    }

    public function provider($user_id){

        $data = Self::where('user_id',$user_id)->first();

        return $data;
    }
}
