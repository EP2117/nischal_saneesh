<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch')->select('id', 'branch_name');
    }
}
