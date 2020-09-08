<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    public function warehouses()
    {
        return $this->hasMany('App\Warehouse');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'branch_user', 'branch_id', 'user_id');
    }
}
