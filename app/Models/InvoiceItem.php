<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'title',
        'quantity',
        'unit',
        'price',
        'sub_total'
    ];

    protected $hidden = [
        'id'
    ];

}
