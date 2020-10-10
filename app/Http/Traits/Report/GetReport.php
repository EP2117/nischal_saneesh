<?php
namespace App\Http\Traits\Report;
use App\AccountTransition;
use App\CollectionPurchase;
use App\PurchaseCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait GetReport{
    public function getPaymentReport($request){
//        dd($request->all());
        $payments =DB::table('collection_purchase')
            ->select(DB::raw("collection_purchase.*, credit_purchase_collections.collection_no,credit_purchase_collections.collection_date,purchase_invoices.invoice_date, purchase_invoices.invoice_no,credit_purchase_collections.supplier_id, purchase_invoices.branch_id,suppliers.state_id,suppliers.township_id,suppliers.name as supplier_name,branches.branch_name"))
            ->leftjoin('purchase_invoices', 'purchase_invoices.id', '=', 'collection_purchase.purchase_id')
//            ->leftjoin('products', 'products.id', '=', 'product_purchase.product_id')
            ->leftjoin('credit_purchase_collections', 'credit_purchase_collections.id', '=', 'collection_purchase.purchase_collection_id')
//            ->leftjoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftjoin('suppliers', 'suppliers.id', '=', 'credit_purchase_collections.supplier_id')
            ->leftjoin('states', 'states.id', '=', 'suppliers.state_id')
            ->leftjoin('townships', 'townships.id', '=', 'suppliers.township_id')
//            ->leftjoin('uoms', 'uoms.id', '=', 'product_purchase.uom_id')
            ->leftjoin('branches', 'branches.id', '=', 'credit_purchase_collections.branch_id')->orderBy('credit_purchase_collections.collection_date','asc');
//        $payment=CollectionPurchase::orderBy('id','asc')->get();
//        dd($payments);
        if($request->payment_no!=null){
            $payments->where('credit_purchase_collections.collection_no',$request->payment_no);
        }
        if($request->from_date != '' && $request->to_date != '')
        {
            $payments->whereBetween('credit_purchase_collections.collection_date', array($request->from_date, $request->to_date));
        } else if($request->from_date != '') {
            $payments->whereDate('credit_purchase_collections.collection_date', '>=', $request->from_date);
        }else if($request->to_date != '') {
            $payments->whereDate('credit_purchase_collections.collection_date', '<=', $request->to_date);
        }
        if($request->branch_id!=null){
            $payments->where('credit_purchase_collections.branch_id',$request->branch_id);
        }
        if($request->supplier_id!=null){
            $payments->where('credit_purchase_collections.supplier_id',$request->supplier_id);
        }
        if($request->state_id!=null){
            $payments->where('suppliers.state_id',$request->state_id);
        }
        if($request->township_id!=null){
            $payments->where('suppliers.township_id',$request->township_id);
        }
        $payments=$payments->get();
//        if($request->supplier_id!=null){
//            $payments->where('collection_no',$request->payment_no);
//        }
//        if($request->invoice_no!=null){
//            $payments->where('collection_no',$request->payment_no);
//        }
        $total_paid=0;
        $total_discount=0;
        $html="";
        foreach($payments as $payment) {
            if($payment->discount==null){
                $payment->discount=0;
            }
            if($payment->paid_amount==null){
                $payment->padi_amount=0;
            }
            $total_discount+=$payment->discount;
            $total_paid+=$payment->paid_amount;
            $html .= '<tr><td class="text-center"></td><td class="text-center">'.$payment->collection_date.'</td><td class="text-center">'.$payment->collection_no.'</td>';
            $html .= '<td class="text-center">'.$payment->invoice_date.'</td><td>'.$payment->invoice_no.'</td>';
            $html .= '<td class="mm-txt">'.$payment->supplier_name.'</td>';
            $html .= '<td class="text-center">'.$payment->paid_amount.'</td>';
            $html .= '<td class="mm-txt">'.$payment->discount.'</td>';
//            if($purchase->is_foc == 0) {
//                $html .= '<td class="text-right">'.$payment->price.'</td>';
//            }
//            else {
//                $html .= '<td>FOC</td>';
//            }

            $html .= '</tr>';

//            if($purchase->is_foc == 0){
//                $total = $total + $purchase->total_amount;
//            }

//            $i++;

        }


        $html .= '<tr><td colspan ="6" style="text-align: right;"><strong>Total</strong></td><td class="text-center">'.number_format($total_paid).'</td><td>'.number_format($total_discount).'</td></tr>';
        return $html;
//
    }
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
//                $account_transition[$k]->total_credit=$total_credit;
//                $account_transition[$k]->total_debit=$total_debit;
//                dd($account_transition);
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
//                is from_date'data is null in database
//                $is_from_date=AccountTransition::whereDate('transition_date','<',$d)->where('is_cashbook',1)->latest()->first();
                $is_from_date=AccountTransition::whereDate('transition_date','<',$d)->where('is_cashbook',1)->latest()->first();
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
