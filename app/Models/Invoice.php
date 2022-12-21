<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'uuid',
        'invoice_number',
        'booking_id',
        'provider_id',
        'company_id',
        'approved_by',
        'billing_name',
        'billing_address',
        'service_id',
        'sub_total',
        'tax',
        'total_amount',
        'status',
    ];

    protected $hidden = [
        'id',
        'booking_id',
        'provider_id',
        'company_id',
        'approved_by',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function getInvoiceNumber(){

        $invoice   =   Self::orderBy('created_at', 'desc')->first('invoice_number');
        if($invoice == null || empty($invoice)){
            $invoiceNumber = 'INV1000';
        }else{
            $invoiceNumber = $invoice->invoice_number + 1;
        }
        return $invoiceNumber;
    }

    public function createInvoice($provider,$booking,$job,$billing_name,$user){

        $invoice = new Invoice;

        $invoice->invoice_number    =   'INV1000';
        $invoice->booking_id        =   $booking->id;
        $invoice->provider_id       =   $provider->id;
        $invoice->company_id        =   $job->company_id;
        $invoice->approved_by       =   Null;
        $invoice->billing_name      =   $billing_name;
        $invoice->billing_address   =   $provider->address;
        $invoice->service_id        =   $job->service_id;
        $invoice->sub_total         =   100.2;
        $invoice->tax               =   18;
        $invoice->total_amount      =   200;
        $invoice->status            =   'Pending';

        $invoice->save();

        return $invoice;
    }
}
