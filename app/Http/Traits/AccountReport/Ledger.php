<?php
namespace App\Http\Traits\AccountReport;
use Carbon\Carbon;
use App\AccountTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait Ledger{
    public function storetInLedger($sale,$sub_account_id,$sale_common_account_id=null){
            AccountTransition::create([
                'sub_account_id' => $sub_account_id,
                'transition_date' => $sale->invoice_date,
                'sale_id' => $sale->id,
                'customer_id'=>$sale->customer_id,
                'vochur_no'=>$sale->invoice_no,
                'description'=>'',
                'is_cashbook' => 0,
                'debit' => $sale->sub_total,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);
            // if($sale->)
            if($sale->payment_type=='cash' ||  ($sale->payment_type=='credit' && $sale->pay_amount!=0)){
                AccountTransition::create([
                    'sub_account_id' => $sale->payment_type=='cash' ? $sale_common_account_id : $sub_account_id,
                    'transition_date' => $sale->invoice_date,
                    'sale_id' => $sale->id,
                    'customer_id'=>$sale->customer_id,
                    'vochur_no'=>$sale->invoice_no,
                    'description'=>'',
                    'is_cashbook' => 0,
                    'credit' => $sale->pay_amount,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }
            
            if($sale->cash_discount!=null || $sale->cash_discount!= 0){
                $discount_allowed_account_id=config('global.discount_allowed');     /*sub account_id for Discount allowed*/
                AccountTransition::create([
                    'sub_account_id' => $discount_allowed_account_id,
                    'transition_date' => $sale->invoice_date,
                    'sale_id' => $sale->id,
                    'customer_id'=>$sale->customer_id,
                    'vochur_no'=>$sale->invoice_no,
                    'description'=>'',
                    'is_cashbook' => 0,
                    'credit' => $sale->cash_discount,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }
        
    }
}