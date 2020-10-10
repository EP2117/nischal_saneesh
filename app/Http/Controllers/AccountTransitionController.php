<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use App\Http\Traits\Report\GetReport;
use App\PurchaseInvoice;
use App\Sale;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AccountTransitionController extends Controller
{
    use GetReport;
    public function getAllCashbook(Request $request){
        $date_arr=$this->getDateArr($request);
//        foreach($date_arr as $key=>$d){
//            $cashbook[$key]=new \stdClass();
//
//            $account_transition=AccountTransition::whereDate('transition_date',$d)->where('is_cashbook',1)->get();
////            if($account_transition->isEmpty()){
////                dd('bbb');
////            }
//            if($account_transition->isNotEmpty()){
//
//
//                $opening_balance = $this->getOpening($request,$d);
//                $total_credit = 0;
//                $total_debit = 0;
//                foreach($account_transition as $k=>$at){
//                    $total_debit += $at->debit;
//                    $total_credit += $at->credit;
//
//                }
//                $cashbook[$key]->total_credit=$total_credit;
//                $cashbook[$key]->total_debit=$total_debit;
//                $cashbook[$key]->date=$d;
//                $cashbook[$key]->opening_balance =$opening_balance;
//                $cashbook[$key]->closing_balance = $this->getClosing($request,$d, $opening_balance);
//                $cashbook[$key]->cashbook_list= $account_transition;
//            }
//            else{
//                $cashbook[$key]->date=$d;
//                $cashbook[$key]->opening_balance =$cashbook[$key-1]->closing_balance;
//                $cashbook[$key]->closing_balance = $cashbook[$key-1]->closing_balance;
//                $cashbook[$key]->cashbook_list= [];
//            }
//
//        }

        $cashbook=$this->getCashBookList($request);
        return response(compact('cashbook'));
    }
//    public function getOpening($request,$at){
//        $opening=AccountTransition::whereDate('transition_date','<',$at->transition_date)->where('is_cashbook',1);
//        if($request->sub_account!=null){
//            $opening->where('sub_account_id',$request->sub_account);
//        }
//        if($request->vochur_no!=null){
//            $opening->where('vochur_no',$request->vochur_no);
//        }
//          $opening=$opening->get();
//        if($opening->isNotEmpty()){
//            $op_debit=$op_credit=0;
//            foreach($opening as $op){
//                $op_debit+=$op->debit;
//                $op_credit+=$op->credit;
//            }
//            $opening_balance=$op_debit-$op_credit;
//        }else{
//            $opening_balance=0;
//        }
//        return $opening_balance;
//
//
//    }
//    public function getClosing($request,$t,$opening_balance){
//        $ac=AccountTransition::where('is_cashbook',1);
//        $ac->whereDate('transition_date',$t->transition_date);
//
//        if($request->sub_account!=null){
//            $ac->where('sub_account_id',$request->sub_account);
//        }
//        if($request->vochur_no!=null){
//            $ac->where('vochur_no',$request->vochur_no);
//        }
////        if($request->from_date!= null && $request->to_date!=null ){
////            $ac->whereDate('transition_date','>=',$request->from_date)
////                ->whereDate('transition_date','<=',$request->to_date)->where('is_cashbook',1);
////        }
//        $closing=$ac->get();
//        if($closing->isNotEmpty()){
//            $cl_debit=$cl_credit=0;
//            foreach($closing as $c){
//                $cl_debit+=$c->debit;
//                $cl_credit+=$c->credit;
//            }
////            dd($opening_balance);
//            $closing_balance=($opening_balance+$cl_debit)-$cl_credit;
//        }else{
//            $closing_balance=0;
//        }
//        return $closing_balance;
//
//
//    }
}
