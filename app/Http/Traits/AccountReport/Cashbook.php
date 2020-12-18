<?php
namespace App\Http\Traits\AccountReport;
use Carbon\Carbon;
use App\AccountTransition;
use Illuminate\Support\Facades\DB;

trait Cashbook{
    public function getDateArr($request){
        $to = \Carbon\Carbon::createFromFormat('Y-m-d',$request->from_date);
        $from = \Carbon\Carbon::createFromFormat('Y-m-d',$request->to_date);

        $count_of_days = $to->diffInDays($from);

        $from_date=new Carbon($request->from_date);
        $to_date=new Carbon($request->to_date);
        $from_year=$from_date->format('Y');  $from_day=$from_date->format('d'); $from_month=$from_date->format('m');
        $to_year=(int)$from_date->format('Y');  $to_day=(int)$to_date->format('d'); $to_month=(int)$to_date->format('m');
        $date_arr=[];
        $t=$count_of_days+(int)$from_day;
        for($i=(int)$from_day ; $i <=$t;$i++){
            $date = $from_year . '-' . $from_month . '-' . $i;
            array_push($date_arr,$date);
        }
        return $date_arr;
    }
    public function getOpening($request,$a){
        $opening=AccountTransition::whereDate('transition_date','<',$a)->where('is_cashbook',1);
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
    public function getClosing($request,$a,$opening_balance){
        $ac=AccountTransition::where('is_cashbook',1);
        $ac->whereDate('transition_date',$a);

//        if($request->sub_account!=null){
//            $ac->where('sub_account_id',$request->sub_account);
//        }
//        if($request->vochur_no!=null){
//            $ac->where('vochur_no',$request->vochur_no);
//        }
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
            $closing_balance=($opening_balance+$cl_debit)-$cl_credit;
        }else{
            $closing_balance=0;
        }
        return $closing_balance;
    }
    public function getCashBookList($request){
        $date_arr=$this->getDateArr($request);
        foreach($date_arr as $key=>$d){
            $cashbook[$key]=new \stdClass();
            $account_transition=AccountTransition::whereDate('transition_date',$d)->where('is_cashbook',1);
            if($request->vochur_no!= null){
                $account_transition->where('vochur_no',$request->vochur_no);
            }if($request->sub_account!=null) {
                $account_transition->where('sub_account_id',$request->sub_account);
            }
            if($request->vochur_no !=null || $request->sub_account!=null) {
                $cashbook[$key]->hide=true;
            }
                $account_transition=$account_transition->get();
            if($account_transition->isNotEmpty()){
                $opening_balance = $this->getOpening($request,$d);
                $total_credit = 0;
                $total_debit = 0;
                foreach($account_transition as $k=>$at){
                    $total_debit += $at->debit;
                    $total_credit += $at->credit;

                }
                $cashbook[$key]->total_credit=$total_credit;
                $cashbook[$key]->total_debit=$total_debit;
                $cashbook[$key]->date=$d;
                $cashbook[$key]->opening_balance =$opening_balance;
                $cashbook[$key]->closing_balance = $this->getClosing($request,$d, $opening_balance);
                $cashbook[$key]->cashbook_list= $account_transition;
            } elseif($account_transition->isNotEmpty() && count($date_arr)>1){
                $cashbook[$key]->date=$d;
                $cashbook[$key]->opening_balance =$cashbook[$key-1]->closing_balance;
                $cashbook[$key]->closing_balance = $cashbook[$key-1]->closing_balance;
                $cashbook[$key]->cashbook_list= [];
            }else{
            
                // $is_from_date=AccountTransition::whereDate('transition_date','<',$d)->where('is_cashbook',1)->latest()->first();
                $opening_balance = $this->getOpening($request,$d);
                $total_debit=0;
                $total_credit=0;
                $cashbook[$key]->date=$d;
                $cashbook[$key]->opening_balance =$opening_balance;
                    $cashbook[$key]->closing_balance = $opening_balance;
                $cashbook[$key]->cashbook_list= [];
            }
        }
        return $cashbook;
    }
}