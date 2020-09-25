<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recepit extends Model
{
    protected $table='receipts';
    protected $fillable=['date','cash_receipt_no','debit_id','credit_id','amount','remark'];
    protected $with=['debit','credit'];
    public function debit(){
        return $this->belongsTo(SubAccount::class,'debit_id','id');
    }
    public function credit(){
        return $this->belongsTo(SubAccount::class,'credit_id','id');
    }
}
