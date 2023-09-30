<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Subscriber;

class Site extends Model
{
    use HasFactory;
    protected $fillable = [
      'site_id',
      'site_code',
      'site_name',
      'site_url',
      'region_id',
      'region_name'
    ];

    public function subscribers()
    {
        return $this->belongsToMany(Subscriber::class);
    }
    
}
