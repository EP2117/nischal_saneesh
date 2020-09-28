<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductTransition;
use App\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function index(Request  $request){
        $data=PurchaseInvoice::with('products','products.uom', 'warehouse','supplier','products.selling_uoms','office_purchase_man','branch');
        if($request->invoice_no != "") {
            $data->where('invoice_no', $request->invoice_no);
        }

        if($request->from_date != '' && $request->to_date != '')
        {
            $data->whereBetween('invoice_date', array($request->from_date, $request->to_date));
        } else if($request->from_date != '') {
            $data->whereDate('invoice_date', '>=', $request->from_date);

        }else if($request->to_date != '') {
            $data->whereDate('invoice_date', '<=', $request->to_date);
        } else {}

        if($request->supplier_id != "") {
            $data->where('supplier_id', $request->supplier_id);
        }

        if(isset($request->branch_id) && $request->branch_id != "") {
            $data->where('branch_id', $request->branch_id);
        }

        if($request->office_purchase_man_id != "") {
            $data->where('office_sale_man_id', $request->office_purchase_man_id);
        }

        if($request->ref_no != "") {
            $data->where('reference_no','LIKE','%'.$request->ref_no.'%');
        }
        $data=$data->orderBy('id','desc')->paginate(15);
        return compact('data');
    }
    public function store(Request  $request){
        if(!empty($request->reference_no) && $request->duplicate_ref_no == false) {
            $validatedData = $request->validate([
                'reference_no' => 'max:255|unique:purchase_invoices',
            ]);
        }
        $p = new PurchaseInvoice();

        //auto generate invoice no;
        $latest = PurchaseInvoice::orderBy('id','desc')->first();
        if($latest==null){
            $no=0;
        }else{
            $no=$latest->id;
        }
        $invoice_no = "P-INV".str_pad((int)$no + 1,8,"0",STR_PAD_LEFT);
        $p->invoice_no = $invoice_no;
        $p->branch_id = Auth::user()->branch_id;
        $p->reference_no = $request->reference_no;
        $p->invoice_date = $request->invoice_date;
        $p->warehouse_id = Auth::user()->warehouse_id;
        $p->supplier_id = $request->supplier_id;
        $p->office_purchase_man_id = Auth::user()->id;
        $p->total_amount = $request->sub_total;
        $p->discount = $request->discount;
        $p->pay_amount = $request->pay_amount;
        $p->balance_amount = $request->balance_amount;
        if($request->payment_type == 'credit') {
            $p->payment_type = 'credit';
            $p->due_date = $request->due_date;
            $p->credit_day = $request->credit_day;
        } else {
            $p->payment_type = 'cash';
        }
        $p->created_by = Auth::user()->id;
        $p->updated_by = Auth::user()->id;
        $p->save();
        for($i=0; $i<count($request->product); $i++) {
            $product_result = Product::select('uom_id')->find($request->product[$i]);
            $main_uom_id = $product_result->uom_id;
            $pivot = $p->products()->attach($request->product[$i],['uom_id' => $request->uom[$i], 'product_quantity' => $request->qty[$i], 'price' => $request->unit_price[$i], 'price_variant' => $request->price_variants[$i], 'total_amount' => $request->total_amount[$i]]);
            //get last pivot insert id
            $last_row=DB::table('product_purchase')->orderBy('id', 'DESC')->first();
            $pivot_id = $last_row->id;
            //calculate quantity for product pre-defined UOM
//            $uom_relation = DB::table('product_selling_uom')
//                ->select('relation')
//                ->where('product_id',$request->product[$i])
//                ->where('uom_id',$request->uom[$i])
//                ->first();
//            if($uom_relation) {
//                $relation_val = $uom_relation->relation;
//            } else {
//                //for pre-defined product uom
//                $relation_val = 1;
//            }
            //add products in transition table=> transition_type = out (for sold out)
            $obj = new ProductTransition;
            $obj->product_id            = $request->product[$i];
            $obj->transition_type       = "in";
            $obj->transition_purchase_id   = $p->id;
            $obj->transition_product_pivot_id = $pivot_id;
            $obj->branch_id  = Auth::user()->branch_id;
            $obj->warehouse_id          = Auth::user()->warehouse_id;
            $obj->transition_date       = $request->invoice_date;
            $obj->transition_product_uom_id        = $request->uom[$i];
            $obj->transition_product_quantity      = $request->qty[$i];
            $obj->product_uom_id        = $main_uom_id;
            $obj->product_quantity      = $request->qty[$i] ;
            $obj->created_by = Auth::user()->id;
            $obj->updated_by = Auth::user()->id;
            $obj->save();
        }
        $status = "success";
        $purchase_id = $p->id;
        return compact('status','purchase_id');
    }
    public function edit($id){
//        $access_brands = array();
        $purchase = PurchaseInvoice::with('products','warehouse','supplier','products.brand','products.category','products.uom','products.selling_uoms','office_purchase_man','branch')->find($id);
//        if(Auth::user()->role->id == 6) {
//            //for Country Head User
//            foreach(Auth::user()->brands as $brand) {
//                array_push($access_brands, $brand->id);
//            }
//        }
        $supplier_id = $purchase->supplier_id;
        $previous_balance = 0;
        $chk_balance = DB::table("purchase_invoices")
            ->select(DB::raw("SUM(CASE  WHEN balance_amount IS NOT NULL THEN balance_amount  ELSE 0 END)  as previous_balance"))
            ->where('supplier_id','=',$supplier_id)
            ->where('id', '!=', $id)
            ->groupBy('supplier_id')
            ->first();
        if($chk_balance) {
            $previous_balance = $chk_balance->previous_balance;
        }
        return compact('purchase', 'previous_balance');
    }
    public function update(Request $request,$id){
//        dd($request->all());
        if(!empty($request->reference_no) && $request->duplicate_ref_no == false) {
            $validatedData = $request->validate([
                'reference_no' => 'max:255|unique:purchase_invoices,reference_no,'.$id,
            ]);
        }
        $p = PurchaseInvoice::find($id);
        $p->invoice_no = $request->invoice_no;
        $p->reference_no = $request->reference_no;
        $p->invoice_date = $request->invoice_date;
        $p->warehouse_id = Auth::user()->warehouse_id;
        $p->office_purchase_man_id = Auth::user()->id;
        $p->supplier_id = $request->supplier_id;
        $p->total_amount = $request->sub_total;
        $p->discount = $request->discount;
        $p->pay_amount = $request->pay_amount;
        $p->balance_amount = $request->balance_amount;
//        $sale->sale_type  = $request->sale_type;
        if($request->payment_type == 'credit') {
            $p->payment_type = 'credit';
            $p->due_date = $request->due_date;
            $p->credit_day = $request->credit_day;
        } else {
            $p->payment_type = 'cash';
        }

        $p->updated_at = time();
        $p->updated_by = Auth::user()->id;
        $p->save();

        $ex_pivot_arr = $request->ex_product_pivot;
        //remove id in pivot that are removed in sale Form
        $results =array_diff($ex_pivot_arr,$request->product_pivot); //get id that are not in request pivot array
        foreach($results as $key => $val) {
            //delete in pivot
            DB::table('product_purchase')
                ->where('id', $val)
                ->delete();
            //delete in transition
            DB::table('product_transitions')
                ->where('transition_product_pivot_id', $val)
                ->where('transition_purchase_id', $id)
                ->delete();
        }

        //update in product pivot table
        for($i=0; $i<count($request->product); $i++) {

            //check product already exist or not
            if($request->product_pivot[$i] != '0' && in_array($request->product_pivot[$i], $ex_pivot_arr)) {
                //update existing product in pivot and transition tables
                DB::table('product_purchase')
                    ->where('id', $request->product_pivot[$i])
                    ->update(array('uom_id' => $request->uom[$i], 'product_quantity' => $request->qty[$i], 'price' => $request->unit_price[$i], 'price_variant' => $request->price_variants[$i], 'total_amount' => $request->total_amount[$i]));

                //get product pre-defined UOM
                $product_result = Product::select('uom_id')->find($request->product[$i]);
                $main_uom_id = $product_result->uom_id;
                //calculate quantity for product pre-defined UOM
//                $uom_relation = DB::table('product_selling_uom')
//                    ->select('relation')
//                    ->where('product_id',$request->product[$i])
//                    ->where('uom_id',$request->uom[$i])
//                    ->first();
//                if($uom_relation) {
//                    $relation_val = $uom_relation->relation;
//                } else {
//                    //for pre-defined product uom
//                    $relation_val = 1;
//                }
//                $product_qty = $request->qty[$i] * $relation_val;

                DB::table('product_transitions')
                    ->where('transition_product_pivot_id', $request->product_pivot[$i])
                    ->where('transition_purchase_id', $id)
                    ->update(array('product_uom_id' => $main_uom_id,'product_quantity'=>$request->qty[$i], 'transition_product_uom_id' => $request->uom[$i], 'transition_product_quantity' => $request->qty[$i]));
            } else {
                //add product into pivot table if not exist
                //get product pre-defined UOM
                $product_result = Product::select('uom_id')->find($request->product[$i]);
                $main_uom_id = $product_result->uom_id;
                //add product into pivot table
                $pivot = $p->products()->attach($request->product[$i],['uom_id' => $request->uom[$i], 'product_quantity' => $request->qty[$i], 'price' => $request->unit_price[$i], 'price_variant' => $request->price_variants[$i], 'total_amount' => $request->total_amount[$i]]);

                //get last pivot insert id
                $last_row=DB::table('product_purchase')->orderBy('id', 'DESC')->first();

                $pivot_id = $last_row->id;

                //calculate quantity for product pre-defined UOM
//                $uom_relation = DB::table('product_selling_uom')
//                    ->select('relation')
//                    ->where('product_id',$request->product[$i])
//                    ->where('uom_id',$request->uom[$i])
//                    ->first();
//                if($uom_relation) {
//                    $relation_val = $uom_relation->relation;
//                } else {
//                    //for pre-defined product uom
//                    $relation_val = 1;
//                }

                //add products in transition table=> transfer_type = out (for sold out)
                $obj = new ProductTransition;
                $obj->product_id            = $request->product[$i];
                $obj->transition_type       = "in";
                $obj->transition_purchase_id   = $id;
                $obj->transition_product_pivot_id   = $pivot_id;
                $obj->branch_id  = Auth::user()->branch_id;
                $obj->warehouse_id          = Auth::user()->warehouse_id; // for Main Warehouse Entry
                $obj->transition_date       = $request->invoice_date;
                $obj->transition_product_uom_id        = $request->uom[$i];
                $obj->transition_product_quantity      = $request->qty[$i];
                $obj->product_uom_id        = $main_uom_id;
                $obj->product_quantity      = $request->qty[$i];
                $obj->created_by = Auth::user()->id;
                $obj->updated_by = Auth::user()->id;
                $obj->save();
            }
        }

        $status = "success";
        return compact('status');
    }
    public function destroy($id){
        $p = PurchaseInvoice::find($id);
        $p->products()->detach();
        DB::table('product_transitions')
            ->where('transition_type','in')
            ->where('transition_purchase_id', $id)
            ->delete();


        $p->delete();
        return response(['message' => 'delete successful']);
    }
    public function getPreviousBalance($id)
    {
        $previous_balance = 0;
        $chk_balance = DB::table("purchase_invoices")

            ->select(DB::raw("SUM(CASE  WHEN balance_amount IS NOT NULL THEN balance_amount  ELSE 0 END)  as previous_balance"))
            ->where('customer_id','=',$id)
            ->groupBy('supplier_id')
            ->first();
        if($chk_balance) {
            $previous_balance = $chk_balance->previous_balance;
        }
        return compact('previous_balance');

    }
}
