<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Traits\AccountReport\Ledger;
use App\Http\Traits\AccountReport\Cashbook;
use App\Http\Traits\Report\GetReport;

class AccountTransitionController extends Controller
{
    use Cashbook;
    use Ledger;
    use GetReport;
    public function getAllCashbook(Request $request){
        $date_arr=$this->getDateArr($request);
        $cashbook=$this->getCashBookList($request);
        return response(compact('cashbook'));
    }
    public function getAllLedger(Request $request){
        $ledger=$this->getLedgerList($request);
        return response()->json([
            'ledger'=>$ledger,
            'account_type'=>$request->type,
        ]);
    }
    public function getProfitAndLossReport(Request $request){
        $pl=$this->getProfitAndLoss($request);
    }
}
