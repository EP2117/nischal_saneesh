<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use App\Http\Traits\AccountReport\Ledger;
use App\Http\Traits\Report\GetReport;
use App\PurchaseCollection;
use App\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PurchaseCollectionController extends Controller
{
    use GetReport;
    use Ledger;
    public function getPurchaseCollection(Request  $request){
        $login_year = Session::get('loginYear');
        $data = PurchaseCollection::with('branch');
        if($request->collection_no != "") {
            $data->where('collection_no',  $request->collection_no);
        }
        //for Country Head and Admin roles (can access multiple branch)
        if(Auth::user()->role->id == 6 || Auth::user()->role->id == 2) {
            if(Auth::user()->role->id != 1) { //system can access all branches
                $branches = Auth::user()->branches;
                $branch_arr = array();
                foreach($branches as $branch) {
                    array_push($branch_arr, $branch->id);
                }
                $data->whereIn('branch_id',$branch_arr);
            }
        } else {
            //other roles can access only one branch
            if(Auth::user()->role->id != 1) { //system can access all branches
                $branch = Auth::user()->branch_id;
                $data->where('branch_id',$branch);
            }
        }

        if($request->branch_id != "") {
            $data->where('branch_id',  $request->branch_id);
        }

        if($request->from_date != '' && $request->to_date != '')
        {
            $data->whereBetween('collection_date', array($request->from_date, $request->to_date));
        } else if($request->from_date != '') {
            $data->whereDate('collection_date', '>=', $request->from_date);
        }else if($request->to_date != '') {
            $data->whereDate('collection_date', '<=', $request->to_date);
        } else {
            $data->whereBetween('collection_date', array($login_year.'-01-01', $login_year.'-12-31'));
        }
        if($request->supplier_id != "") {
            $data->where('supplier_id', $request->supplier_id);
        }

        $data = $data->orderBy('id', 'DESC')->get();
//        dd($data);
        return response(compact('data'), 200);
    }
    public function getSupplierCreditPurchase($supplier_id,Request $request){
        if($request->branch_id && $request->branch_id != '') {
            $data = PurchaseInvoice::orderBy('invoice_date', 'ASC')
                ->where('supplier_id',$supplier_id)
                ->where('payment_type', 'credit')
                ->where('branch_id', $request->branch_id)
                    ->whereRaw('(total_amount-(pay_amount+collection_amount)) > 0')
                ->get();
        } else { $data = ''; }
//        dd($data);
        return response(compact('data'), 200);    }
        public function store(Request  $request){
    //    dd($request->all());
            //auto generate collection no;
            DB::beginTransaction();
            try {
                $max_id = PurchaseCollection::max('id');
                if($max_id) {
                    $max_id = $max_id + 1;
                } else {
                    $max_id = 1;
                }
                $collection_no = "P".str_pad($max_id,5,"0",STR_PAD_LEFT);
                if($request->is_auto == true) {
                    $auto_payment	= 1;
                    $total_paid_amount	= $request->pay_amount;
                } else {
                    $auto_payment	= 0;
                    $total_paid_amount	= $request->total_pay;
                }
                $p_collection=PurchaseCollection::create([
                    'supplier_id'=>$request->supplier_id,
                    'branch_id'=>$request->branch_id,
                    'collection_no'=>$collection_no,
                    'collection_date'=>$request->collection_date,
                    'auto_payment'=>$auto_payment,
                    'total_paid_amount'=>$total_paid_amount,
                    'created_by' => Auth::user()->id,
                ]);
                $description=$p_collection->collection_no.",Date ".$p_collection->collection_date." by " .$p_collection->supplier->name;

                $sub_account_id=config('global.credit_payment');    /*sub account id for credit payment */
                if($p_collection){
                    AccountTransition::create([
                        'sub_account_id' => $sub_account_id,
                        'transition_date' => $request->collection_date,
                        'purchase_id' => $p_collection->id,
                        'supplier_id'=>$p_collection->supplier_id,
                        'is_cashbook' => 1,
                        'description'=>$description,
                        'vochur_no'=>$p_collection->collection_no,
                        'credit' => $total_paid_amount,
                        'status'=>'credit_payment',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                    $this->storeCreditPaymentInLedger($p_collection,$request);
                }
                for($i=0; $i<count($request->invoices); $i++) {
                    if($request->discounts[$i]==null){
                        $dsc=0;
                    }else{
                        $dsc=$request->discounts[$i];
                    }
                    //add invoices into pivot table
                    $pivot = $p_collection->purchases()->attach($request->invoices[$i],['paid_amount' => $request->payments[$i], 'discount' => $dsc]);

                    //Get all collection amount and update collection_amount in each sale invoice

                    $collect_qry = DB::table("collection_purchase")
                        ->select(DB::raw("SUM(paid_amount)  as total_paid, SUM(discount)  as total_discount"))
                        ->where('purchase_id', $request->invoices[$i])
                        ->groupBy('purchase_id')
                        ->first();
    //                dd($collect_qry);
                    if($collect_qry) {
                        if($collect_qry->total_discount==null){
                            $collect_qry->total_discount=0;
                        }
                        $collection_amount = $collect_qry->total_paid + $collect_qry->total_discount;
                    } else {
                        $collection_amount = 0;
                    }
                    $pu = PurchaseInvoice::find($request->invoices[$i]);
                    $pu->collection_amount = $collection_amount;
                    $pu->save();
                }

                $status = "success";
                $collection_id = $p_collection->id;
                DB::commit();
                return compact('status','collection_id');
                // all good
            } catch (\Exception $e) {
                DB::rollback();
                $status = "fail";
                return compact('status');
                // something went wrong
            }
          
        }
        public function edit(Request  $request,$c_id){
                $collection = PurchaseCollection::with('purchases','supplier','branch')->find($c_id);
            $supplier_id = $collection->supplier_id;
            $branch_id = $collection->branch_id;
            $col_sales = array();
//            dd($collection->purchases);
            foreach($collection->purchases as $p) {
                array_push($col_sales, $p->id);
            }
//            dd($col_sales);
            $sup_invoices = PurchaseInvoice::orderBy('invoice_date', 'ASC')
                ->where('supplier_id',$supplier_id)
                ->where('branch_id', $branch_id)
                ->where('payment_type', 'credit')
                ->where(function ($query) use ($col_sales){
                    $query->whereRaw('(total_amount-(pay_amount + collection_amount)) > 0')
                        ->orWhereIn('id', $col_sales);
                })
                ->get();
//            dd($cus_invoices);
            return compact('collection','sup_invoices');
        }
        public function update(Request  $request,$c_id){
            DB::beginTransaction();
            try {

                $collection = PurchaseCollection::find($c_id);
                $collection->collection_date 	= $request->collection_date;
                if($request->is_auto == true) {
                    $collection->auto_payment	= 1;
                    $collection->total_paid_amount	= $request->pay_amount;
                } else {
                    $collection->auto_payment	= 0;
                    $collection->total_paid_amount	= $request->total_pay;
                }
                $collection->branch_id = $request->branch_id;
                $collection->updated_at = time();
                $collection->updated_by = Auth::user()->id;
                $collection->save();
                $sub_account_id=config('global.credit_payment');    /*sub account id for credit payment */
                $description=$collection->collection_no.", Date ".$collection->collection_date." to " .$collection->supplier->name;
                if($collection){
                    if($collection->total_paid_amount!=0){
                        AccountTransition::where([
                            ['purchase_id',$c_id],
                            ['is_cashbook',0],
                            ['status','credit_payment']])->update([
                            'sub_account_id' => $sub_account_id,
                            'transition_date' => $request->collection_date, 
                            'purchase_id' => $collection->id,
                            'vochur_no'=>$collection->collection_no,
                            'supplier_id'=>$collection->supplier,
                            'is_cashbook' => 1,
                            'description'=>$description,
                            'credit' => $collection->total_paid_amount,
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }elseif($collection->total_paid_amount==0){
                        AccountTransition::where('purchase_id',$c_id)->delete();
                    }
                    $this->updateCreditPaymentInLedger($collection,$request);

                }

                //update collection amount for removed sales
                foreach($request->remove_pivot_id as $key => $val) {
                    //get paid amount and discount value before delete
                    $relation = DB::table('collection_purchase')
                        ->select('purchase_id','purchase_collection_id','paid_amount','discount')
                        ->where('id',$val)
                        ->first();
                    if($relation->discount == NULL) {
                        $discount = 0;
                    } else {
                        $discount = $relation->discount;
                    }

                    $cm = $relation->paid_amount + $discount;

                    //update collection amount in sale
                    $p = PurchaseInvoice::find($relation->purchase_id);
                    $collection_amount = $p->collection_amount - $cm;
                    $p->collection_amount = $collection_amount;
                    $p->save();
                }

                $collection->purchases()->detach();

                for($i=0; $i<count($request->invoices); $i++) {
                    //add invoices into pivot table
                    $pivot = $collection->purchases()->attach($request->invoices[$i],['paid_amount' => $request->payments[$i], 'discount' => $request->discounts[$i]]);

                    //Get all collection amount and update collection_amount in each sale invoice
                    $collect_qry = DB::table("collection_purchase")
                        ->select(DB::raw("SUM(paid_amount)  as total_paid, SUM(discount)  as total_discount"))
                        ->where('purchase_id', $request->invoices[$i])
                        ->groupBy('purchase_id')
                        ->first();
                    if($collect_qry) {
                        $collection_amount = $collect_qry->total_paid + $collect_qry->total_discount;
                    } else {
                        $collection_amount = 0;
                    }
                    $p = PurchaseInvoice::find($request->invoices[$i]);
                    $p->collection_amount = $collection_amount;
                    $p->save();

                }

                $status = "success";
                $collection_id = $collection->id;
                DB::commit();
                return compact('status','collection_id');
                // all good
            } catch (\Exception $e) {
                DB::rollback();
                $status = "fail";
                return compact('status');
            }
         
        }
    public function destroy($id)
    {
        $collection = PurchaseCollection::with('purchases')->find($id);
        foreach($collection->purchases as $p) {
//            dd($p);
            $relation    = DB::table('collection_purchase')
                ->select('purchase_id','purchase_collection_id','paid_amount','discount')
                ->where('id',$p->pivot->id)
                ->first();
            if($relation->discount == NULL) {
                $discount = 0;
            } else {
                $discount = $relation->discount;
            }

            $p_cm = $relation->paid_amount + $discount;

            //update collection amount in sale
            $p = PurchaseInvoice::find($relation->purchase_id);
            $collection_amount = $p->collection_amount - $p_cm;
            $p->collection_amount = $collection_amount;
            $p->save();
        }
        $collection->purchases()->detach();
        $collection->delete();
        AccountTransition::where([
            ['purchase_id',$id],
            ['status','credit_payment']
        ])->delete();

        return response(['message' => 'delete successful']);
    }
    public function getCreditPaymentReport(Request  $request){
        ini_set('memory_limit','512M');
        ini_set('max_execution_time', 240);
        $html=$this->getPaymentReport($request);
        return response(compact('html'), 200);
    }
    public function getPurchaseOutstanding(Request $request){
        // dd($request->all());
        $purchase_outstandings=$this->getPurchaseOutStandingReport($request);
        $net_inv_amt=$net_paid_amt=$net_balance_amt=0;
        foreach($purchase_outstandings as $po){
            foreach($po->out_list as $i){
                // dd($i);
                if($i->type=='paid'){
                    $net_inv_amt+=$i->total_amount; 
                    $net_paid_amt+=$i->t_paid_amount;
                    $net_balance_amt+=$i->t_balance_amount;
                }
                
            }
        }
        return compact('purchase_outstandings','net_paid_amt','net_balance_amt','net_inv_amt');
    }

}
