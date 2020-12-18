<?php

namespace App\Http\Controllers;

use App\Http\Traits\AccountReport\Cashbook;
use App\Http\Traits\AccountReport\Ledger;
use Illuminate\Http\Request;

class AccountTransitionController extends Controller
{
    use Cashbook;
    use Ledger;
    public function getAllCashbook(Request $request){
        $date_arr=$this->getDateArr($request);
        $cashbook=$this->getCashBookList($request);
        return response(compact('cashbook'));
    }
}
