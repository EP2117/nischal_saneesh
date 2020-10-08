<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use App\PurchaseCollection;
use App\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PurchaseCollectionController extends Controller
{
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
//        dd($request->all());
            //auto generate collection no;
            $max_id = PurchaseCollection::max('id');
            if($max_id) {
                $max_id = $max_id + 1;
            } else {
                $max_id = 1;
            }
            $collection_no = "C".str_pad($max_id,5,"0",STR_PAD_LEFT);
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
            $description="C".$p_collection->collection_no.",Inv Date ".$p_collection->collection_date." by " .$p_collection->supplier->name;

            $sub_account_id=config('global.credit_payment');    /*sub account id for credit payment */
            if($p_collection){
                AccountTransition::create([
                    'sub_account_id' => $sub_account_id,
                    'transition_date' => $request->collection_date,
                    'purchase_id' => $p_collection->id,
                    'is_cashbook' => 1,
                    'description'=>$description,
                    'vochur_no'=>$p_collection->collection_no,
                    'credit' => $total_paid_amount,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
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
            return compact('status','collection_id');
        }
        public function edit(Request  $request,$c_id){
//        dd('aaaaaaaaaaaaa');
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
//        dd($request->all());
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
            $description="Inv ".$collection->collection_no.",Inv Date ".$collection->collection_date." to " .$collection->supplier->name;
            if($collection){
                if($collection->total_paid_amount!=0){
                    AccountTransition::where('purchase_id',$c_id)->update([
                        'sub_account_id' => $sub_account_id,
                        'transition_date' => $request->collection_date,
                        'purchase_id' => $collection->id,
                        'vochur_no'=>$collection->collection_no,
                        'is_cashbook' => 1,
                        'description'=>$description,
                        'credit' => $collection->total_paid_amount,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }elseif($collection->total_paid_amount==0){
                    AccountTransition::where('purchase_id',$c_id)->delete();
                }
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
            return compact('status','collection_id');
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
        AccountTransition::where('purchase_id',$id)
            ->where('sub_account_id',config('global.credit_payment'))
            ->delete();

        return response(['message' => 'delete successful']);
    }

}
