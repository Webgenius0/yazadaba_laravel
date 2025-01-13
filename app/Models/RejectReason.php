<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectReason extends Model
{
    protected $fillable = ['user_id', 'reason'];
}
