<?php

namespace App\Http\Controllers;

use App\AccountTransition;
use App\Http\Traits\Report\GetReport;
use App\Product;
use App\ProductTransition;
use App\PurchaseInvoice;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    use GetReport;
    public function index(Request  $request){
        $data=PurchaseInvoice::where('is_opening',0);
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
//
//        if($request->office_purchase_man_id != "") {
//            $data->where('office_sale_man_id', $request->office_purchase_man_id);
//        }

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
        $invoice_no = "PI".str_pad((int)$no + 1,5,"0",STR_PAD_LEFT);
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
            $sub_account_id=config('global.purchase_advance');       /*Credit Payment Sub account ID */
            if($request->pay_amount!=0){
                $amount=$request->pay_amount;
            }else{
                $amount=null;
            }
            $p->payment_type = 'credit';
            $p->due_date = $request->due_date;
            $p->credit_day = $request->credit_day;
        } else {
            $sub_account_id=config('global.purchase');        /*Purchase Sub account ID */
            $amount=$request->pay_amount;
            $p->payment_type = 'cash';
        }
        $p->created_by = Auth::user()->id;
        $p->updated_by = Auth::user()->id;
        $p->save();
        $description="Inv ".$p->invoice_no.",Inv Date ".$p->invoice_date." to " .$p->supplier->name;

        if($p){
            if($request->payment_type =='cash' || ($request->payment_type=='credit' && $request->pay_amount!=0)){
                AccountTransition::create([
                    'sub_account_id' => $sub_account_id,
                    'transition_date' => $p->invoice_date,
                    'purchase_id' => $p->id,
                    'vochur_no'=>$invoice_no,
                    'description'=>$description,
                    'is_cashbook' => 1,
                    'credit' => $amount,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);

            }
        }
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
            if($request->pay_amount != 0){
                $amount=$request->pay_amount;
            }
            $sub_account_id=config('global.purchase_advance');
            $p->payment_type = 'credit';
            $p->due_date = $request->due_date;
            $p->credit_day = $request->credit_day;
        } else {
            $p->payment_type = 'cash';
            $amount=$request->sub_total;
            $sub_account_id=config('global.purchase');        /*Purchase Sub account ID */
        }

        $p->updated_at = time();
        $p->updated_by = Auth::user()->id;
        $p->save();
        $description="Inv ".$p->invoice_no.",Inv Date ".$p->invoice_date." to " .$p->supplier->name;
        if($p){
            if($request->payment_type =='cash' || ($request->payment_type=='credit' && $request->pay_amount!=0)) {
                    AccountTransition::where('purchase_id',$id)->update([
                        'sub_account_id' => $sub_account_id,
                        'transition_date' => $p->invoice_date,
                        'purchase_id' => $p->id,
                        'is_cashbook' => 1,
                        'description'=>$description,
                        'vochur_no'=>$request->invoice_no,
                        'credit' => $amount,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
            }elseif($request->payment_type=='credit' && $request->pay_amount==0){
                AccountTransition::where('purchase_id',$id)->delete();
            }

        }
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
        if($p->payment_type=='cash'){
            $sub_account_id=config('global.purchase');
        }else{
            $sub_account_id=config('global.purchase_advance');

        }
        AccountTransition::where('purchase_id',$id)
            ->where('sub_account_id',$sub_account_id)
            ->delete();

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
    public function getDailyPurchaseProductReport(Request $request)
    {
        ini_set('memory_limit','512M');
        ini_set('max_execution_time', 240);

        //$sales =    Sale::with('products','order','order.sale_man','customer','warehouse','products.selling_uoms','products.uom','office_sale_man');
        $purchases = DB::table("product_purchase")
            ->select(DB::raw("product_purchase.*, purchase_invoices.invoice_date, purchase_invoices.invoice_no, purchase_invoices.order_id, purchase_invoices.branch_id, purchase_invoices.warehouse_id, purchase_invoices.supplier_id, products.product_code, products.product_name, products.brand_id, brands.brand_name, suppliers.name, uoms.uom_name,branches.branch_name"))

            ->leftjoin('purchase_invoices', 'purchase_invoices.id', '=', 'product_purchase.purchase_id')
//            ->leftjoin('orders', 'orders.id', '=', 'sales.order_id')
            ->leftjoin('products', 'products.id', '=', 'product_purchase.product_id')

            ->leftjoin('brands', 'brands.id', '=', 'products.brand_id')

            ->leftjoin('suppliers', 'suppliers.id', '=', 'purchase_invoices.supplier_id')

            ->leftjoin('uoms', 'uoms.id', '=', 'product_purchase.uom_id')

//            ->leftjoin('users as u1', 'u1.id', '=', 'sales.office_sale_man_id')
//
//            ->leftjoin('users as u2', 'u2.id', '=', 'orders.sale_man_id')

            ->leftjoin('branches', 'branches.id', '=', 'purchase_invoices.branch_id');

        if($request->invoice_no != "") {
            $purchases->where('purchase_invoices.invoice_no', $request->invoice_no);
        }

        if($request->from_date != '' && $request->to_date != '')
        {
            $purchases->whereBetween('purchase_invoices.invoice_date', array($request->from_date, $request->to_date));
        } else if($request->from_date != '') {
            $purchases->whereDate('purchase_invoices.invoice_date', '>=', $request->from_date);

        }else if($request->to_date != '') {
            $purchases->whereDate('purchase_invoices.invoice_date', '<=', $request->to_date);
        } else {}

        if($request->warehouse_id != "") {
            $purchases->where('purchase_invoices.warehouse_id', $request->warehouse_id);
        }

        if(isset($request->branch_id) && $request->branch_id != "") {
            $purchases->where('purchase_invoices.branch_id', $request->branch_id);
        }

        //for Country Head and Admin roles (can access multiple branch)
        if(Auth::user()->role->id == 6 || Auth::user()->role->id == 2) {
            $branches = Auth::user()->branches;
            $branch_arr = array();
            foreach($branches as $branch) {
                array_push($branch_arr, $branch->id);
            }
            $purchases->whereIn('purchase_invoices.branch_id',$branch_arr);
        } else {
            //other roles can access only one branch
            if(Auth::user()->role->id != 1) { //system can access all branches
                $branch = Auth::user()->branch_id;
                $purchases->where('purchase_invoices.branch_id',$branch);
            }
        }
        if($request->supplier_id != "") {
            $purchases->where('purchase_invoices.supplier_id', $request->supplier_id);
        }

        if($request->product_name != "") {
            //$products->where('products.product_name', 'LIKE', "%$request->product_name%");
            $binds = array(strtolower($request->product_name));
            $purchases->whereRaw('lower(products.product_name) like lower(?)', ["%{$request->product_name}%"]);
        }

        /*if($request->brand_id != "") {
            $sales->whereHas('products', function ($query) use ($request) {
                $query->where('brand_id', $request->brand_id);
            });
        }*/
        if($request->brand_id != "") {
            $purchases->where('products.brand_id', $request->brand_id);
        } else {
            if(Auth::user()->role->id == 6) {
                //for Country Head User
                $access_brands = array();
                foreach(Auth::user()->brands as $brand) {
                    array_push($access_brands, $brand->id);
                }

                $purchases->whereIn('products.brand_id',$access_brands);
            }
        }
        /*if($request->sale_man_id != "") {
            $sales->whereHas('order', function ($query) use ($request) {
                $query->where('sale_man_id', $request->sale_man_id);
            });
        }*/
//        if($request->sale_man_id != "") {
//            $sales->where('orders.sale_man_id', $request->sale_man_id);
//        }
//
//        if($request->office_sale_man_id != "") {
//            $sales->where('sales.office_sale_man_id', $request->office_sale_man_id);
//        }

        if(Auth::user()->role->id == 6) {
            //for Country Head User
            $access_users = array();
//            $office_sale_man_arr = array();
            foreach(Auth::user()->country_head_children as $ls) {
                array_push($access_users, $ls->id);
                $ls_query = User::with('local_supervisor_children')->find($ls->id);
                foreach($ls_query->local_supervisor_children as $sm) {
                    array_push($access_users, $sm->id);
                }
            }
//            foreach(Auth::user()->office_sale_mans as $osm) {
//                array_push($office_sale_man_arr, $osm->id);
//            }

            //get order user's order id
            $orders = DB::table('orders')
                ->whereIn('created_by',$access_users)
                ->pluck('id')->toArray();

            // $sales->whereIn('order_id',$orders);
//            $sales->where(function($query) use ($orders, $office_sale_man_arr) {
//                $query->whereIn('order_id',$orders)
//                    ->orWhereIn('office_sale_man_id',$office_sale_man_arr);
//            });
        }

        if(Auth::user()->role->id == 7) {
            //for Local Supervisor user
            $ls_access_users = array();
            array_push($ls_access_users, Auth::user()->id);
            foreach(Auth::user()->local_supervisor_children as $sm) {
                array_push($ls_access_users, $sm->id);
            }

            //get order user's order id
            $orders = DB::table('orders')
                ->whereIn('created_by',$ls_access_users)
                ->pluck('id')->toArray();

            $purchases->whereIn('order_id',$orders);
        }

        if(Auth::user()->role->id == 4) {
            //for office order user
            //get order user's order id
            $orders = DB::table('orders')
                ->where('created_by',Auth::user()->id)
                ->pluck('id')->toArray();

            $purchases->whereIn('order_id',$orders);
        }

        if($request->order == "") {
            $order = "ASC";
        } else {
            $order = $request->order;
        }
        if($request->sort_by != "") {
            if($request->sort_by == "invoice_no") {
                $purchases->orderBy('purchase_invoices.invoice_no', $order);
            }
            else {}
        } else {
            $purchases->orderBy('purchase_invoices.invoice_date', 'DESC');
        }
        // $data    =  $sales->orderBy('invoice_date', 'DESC')->get();
        $data = $purchases->get();
//        dd($data);
        /*$access_brands = array();

        if(Auth::user()->role->id == 6) {
            //for Country Head User
            foreach(Auth::user()->brands as $brand) {
                array_push($access_brands, $brand->id);
            }
        }

        $brands = DB::table('brands')
                    ->whereIn('id',$access_brands)
                    ->pluck('id')->toArray();*/

        $total = 0;
        $i = 1;
        $html = '';
        foreach($data as $purchase) {
            $html .= '<tr><td class="text-right"></td><td>'.$purchase->invoice_no.'</td><td>'.$purchase->invoice_date.'</td>';
            $html .= '<td class="mm-txt">'.$purchase->branch_name.'</td>';
            $html .= '<td class="mm-txt">'.$purchase->name.'</td>';
            $html .= '<td>'.$purchase->product_code.'</td>';
            $html .= '<td>'.$purchase->product_name.'</td>';
            $html .= '<td>'.$purchase->product_quantity.'</td>';
            $html .= '<td>'.$purchase->uom_name.'</td>';
            if($purchase->is_foc == 0) {
                $html .= '<td class="text-right">'.$purchase->price.'</td>';
            }
            else {
                $html .= '<td>FOC</td>';
            }

            $html .='<td class="text-right">'.$purchase->total_amount.'</td>';
            $html .= '</tr>';

            if($purchase->is_foc == 0){
                $total = $total + $purchase->total_amount;
            }

            $i++;

        }

        $html .= '<tr><td colspan ="10" style="text-align: right;"><strong>Total</strong></td><td class="text-right">'.number_format($total).'</td></tr>';

        return response(compact('html'), 200);
    }
    public function getCreditPaymentReport(Request  $request){
        ini_set('memory_limit','512M');
        ini_set('max_execution_time', 240);
        $html=$this->getPaymentReport($request);
        return response(compact('html'), 200);

    }

}
