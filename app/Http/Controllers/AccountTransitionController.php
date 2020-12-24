<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Traits\AccountReport\Ledger;
use App\Http\Traits\AccountReport\Cashbook;

class AccountTransitionController extends Controller
{
    use Cashbook;
    use Ledger;
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
        // $date_arr=$this->getDateArr($request);
        // $query=AccountTransition::where('is_cashbook',0);
        //     $query->when(!is_null($request->from_date),function($q){
        //        return  $q->where('transition_date','>=',request('from_date'));
        //     });
        //     $query->when(!is_null($request->to),function($q){
        //         return  $q->whereBetween('transition_date',[request('from_date'),request('to_date')]);
        //      });
        //      $today=Carbon::today();
        //      $query->when(is_null($request->to),function($q)use($today){
        //         return  $q->whereBetween('transition_date',[request('from_date'),$today]);
        //      });
        //      $query->when(!is_null($request->invoice_no),function($q){
        //         return  $q->where('vochur_no',request('invoice_no'));
        //      });
        //      $query->when(!is_null($request->invoice_no),function($q){
        //         return  $q->where('vochur_no',request('invoice_no'));
        //      });
        // if($request->type=='Other'){
        //     $query->when(!is_null($request->sub_account_id),function($q){
        //         return  $q->where('sub_account_id',request('sub_account_id'));
        //      });
        //      $q=$query->get();
        //      $this->filterByAccount($q);
        // }elseif($request->type=='Supplier'){
        //     $query->when(!is_null($request->supplier_id),function($q){
        //         return  $q->where('supplier_id',request('supplier_id'));
        //      });
        //      $q=$query->get();
        //      $this->filterBySupplier($q);
        // }elseif($request->type=='Customer'){
        //     $query->when(!is_null($request->customer_id),function($q){
        //         return  $q->where('customer_id',request('customer_id'));
        //      });
        //      $query=$query->get();
        //      $total_debit=$total_credit=0;
        //      foreach($query as $d){
        //          $total_debit+=$d->debit;
        //          $total_credit+=$d->credit;
        //     }
        //     $this->filterByCustomer($request,$query);
        // }
    }
}
