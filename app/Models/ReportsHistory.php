<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportsHistory extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'seller_email','seller_amz_id','report_id' ,'is_downloaded',
    ];
}
