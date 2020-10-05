<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use App\PurchaseInvoice;
use App\Sale;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AccountTransitionController extends Controller
{
    public function getAllCashbook(Request $request){
        $login_year = Session::get('loginYear');
//        $from_date=$request->date;
//        **********************test*********************
        $from_date=new Carbon($request->from_date);
        $account_transition=AccountTransition::orderBy('transition_date','asc')->where('is_cashbook',1);
        $from_year=(int)$from_date->format('Y');  $from_day=(int)$from_date->format('d'); $from_month=(int)$from_date->format('m');
        $to_date=new Carbon($request->to_date);
        $to_year=(int)$from_date->format('Y');  $to_day=(int)$to_date->format('d'); $to_month=(int)$to_date->format('m');
        $account_transition->whereBetween('transition_date', array($request->from_date, $request->to_date));
        $account_transition=$account_transition->groupBy('transition_date')->get();
        foreach($account_transition as $key=>$a) {
            $at = AccountTransition::where('is_cashbook', 1);
            if ($request->vochur_no != '') {
                $at->where('vochur_no', $request->vochur_no);
            }
            if ($request->sub_account != '') {
                $at->where('sub_account_id', $request->sub_account);
            }
            $at->whereDate('transition_date', $a->transition_date);
            $opening_balance = $this->getOpening($request,$a);
            $total_credit = 0;
            $total_debit = 0;
            $at = $at->orderBy('transition_date')->get();

            foreach ($at as $key => $c) {
                $total_debit += $c->debit;
                $total_credit += $c->credit;
            }
            $a->total_credit=$total_credit;
            $a->total_debit=$total_debit;
            $a->opening_balance =$opening_balance;
            $a->closing_balance = $this->getClosing($request,$a, $opening_balance);
            $a->cashbook = $at;
        }

        return response(compact('account_transition','total_debit','total_credit'));
    }
    public function getOpening($request,$at){
        $opening=AccountTransition::whereDate('transition_date','<',$at->transition_date)->where('is_cashbook',1);
        if($request->sub_account!=null){
            $opening->where('sub_account_id',$request->sub_account);
        }
        if($request->vochur_no!=null){
            $opening->where('vochur_no',$request->vochur_no);
        }
          $opening=$opening->get();
        if($opening->isNotEmpty()){
            $op_debit=$op_credit=0;
            foreach($opening as $op){
                $op_debit+=$op->debit;
                $op_credit+=$op->credit;
            }
            $opening_balance=$op_debit-$op_credit;
        }else{
            $opening_balance=0;
        }
        return $opening_balance;


    }
    public function getClosing($request,$t,$opening_balance){
        $ac=AccountTransition::where('is_cashbook',1);
        $ac->whereDate('transition_date',$t->transition_date);

        if($request->sub_account!=null){
            $ac->where('sub_account_id',$request->sub_account);
        }
        if($request->vochur_no!=null){
            $ac->where('vochur_no',$request->vochur_no);
        }
//        if($request->from_date!= null && $request->to_date!=null ){
//            $ac->whereDate('transition_date','>=',$request->from_date)
//                ->whereDate('transition_date','<=',$request->to_date)->where('is_cashbook',1);
//        }
        $closing=$ac->get();
        if($closing->isNotEmpty()){
            $cl_debit=$cl_credit=0;
            foreach($closing as $c){
                $cl_debit+=$c->debit;
                $cl_credit+=$c->credit;
            }
//            dd($opening_balance);
            $closing_balance=($opening_balance+$cl_debit)-$cl_credit;
        }else{
            $closing_balance=0;
        }
        return $closing_balance;


    }
}
