<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Imports\CustomerImport;
use App\Exports\CustomerExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $limit = 30;
        if ($request->has('limit')) {
            $limit = $request->limit;
        }
        //$data = Customer::with('customer_type','state','township','country');
        /*if($request->customer_id != "") {
            $sales->where('customer_id', $request->customer_id);
        }*/
        $data = Customer::select([
                  'customers.*',
                  'customer_types.customer_type_name',
                  'countries.country_name',
                  'states.state_name',
                  'townships.township_name'
                ]);
        $data->leftjoin('customer_types', 'customer_types.id','customers.customer_type_id');
        $data->leftjoin('countries', 'countries.id','customers.country_id');
        $data->leftjoin('states', 'states.id','customers.state_id');
        $data->leftjoin('townships', 'townships.id','customers.township_id');
        if($request->cus_name != "") {
            $data->where('cus_name', 'LIKE', '%'.$request->cus_name.'%');
        }

        if($request->cus_code != "") {
            $data->where('cus_code', $request->cus_code);
        }

        if($request->cus_type != "") {
            $data->where('customer_type_id', $request->cus_type);
        }

        if($request->township_id != "") {
            $data->where('township_id', $request->township_id);
        }

        if($request->state_id != "") {
            $data->where('customers.state_id', $request->state_id);
        }

        if($request->country_id != "") {
            $data->where('country_id', $request->country_id);
        }

        if($request->status != "") {
            $data->where('is_active', $request->status);
        }
        if($request->order == "") {
            $order = "ASC";
        } else {
            $order = $request->order;
        }
        if($request->sort_by != "") {
            if($request->sort_by == "name") {
                $data->orderBy('cus_name', $order);
            }
            else if($request->sort_by == "code") {
                $data->orderBy('cus_code', $order);
            }
            else if($request->sort_by == "phone") {
                $data->orderBy('cus_phone', $order);
            }
            else if($request->sort_by == "cus_type") {
                $data->orderBy('customer_types.customer_type_name', $order);
            }
            else if($request->sort_by == "address") {
                $data->orderBy('townships.township_name', $order);
                $data->orderBy('states.state_name', $order);
                $data->orderBy('countries.country_name', $order);
            }
            else {}
        } else {
            $data = $data->orderBy('customers.id', 'DESC');
        }
        $data = $data->paginate($limit);

        //$data = $data->orderBy('id', 'DESC')->paginate($limit);
        return response(compact('data'), 200);
    }

    public function allCustomers()
    {
        $data = Customer::orderBy('cus_name', 'ASC')->where('is_active',1)->get();
        return response(compact('data'), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Customer::find($id);
        return compact('customer');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $obj = new Customer;
        //auto generate customer code;
        $max_id = Customer::withTrashed()->max('id');
        if($max_id) {
            $max_id = $max_id + 1;
        } else {
            $max_id = 1;
        }
        $cus_code = "C".str_pad($max_id,5,"0",STR_PAD_LEFT);
        $obj->cus_code = $cus_code;
        $obj->cus_name = $request->cus_name;
        $obj->customer_type_id = $request->cus_type;
        $obj->country_id    = $request->country_id;
        $obj->state_id      = $request->state_id;
        $obj->township_id   = $request->township_id;
        $obj->cus_phone  = $request->cus_phone;
        $obj->cus_billing_address  = $request->billing_address;
        $obj->cus_shipping_address = $request->shipping_address;
        $obj->is_active = 1;
        $obj->created_by = Auth::user()->id;
        $obj->updated_by = Auth::user()->id;
        $obj->save();

        $status = "success";
        return compact('status');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $obj = Customer::find($id);
        $obj->cus_name = $request->cus_name;
        $obj->customer_type_id = $request->cus_type;
        $obj->country_id    = $request->country_id;
        $obj->state_id      = $request->state_id;
        $obj->township_id   = $request->township_id;
        $obj->cus_phone  = $request->cus_phone;
        $obj->cus_billing_address  = $request->billing_address;
        $obj->cus_shipping_address = $request->shipping_address;
        $obj->updated_by = Auth::user()->id;
        $obj->save();

        $status = "success";
        return compact('status');

    }

    public function import()
    {
        $path1 = request()->file('file')->store('temp');
        $path=storage_path('app').'/'.$path1;

        $import = new CustomerImport();
        Excel::import($import,$path);
        //return duplicate chessi no count
        return ["message" => "success"];
    }

    public function updateStatus($id, $status)
    {
        $data = Customer::find($id);
        $active = $status == "active" ? '1' : '0';
        $data->is_active = $active;
        $data->save();
        return response(compact('data'), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $obj = Customer::find($id);
        $obj->delete();
        return response(['message' => 'delete successful']);
    }

    public function exportCustomer(Request $request)
    {
        $export = new CustomerExport($request);
        $fileName = 'customer_export_'.Carbon::now()->format('Ymd').'.xlsx';

        return Excel::download($export, $fileName);
    }
}
