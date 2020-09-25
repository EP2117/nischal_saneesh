<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function getAll(Request $request){
        $payment=Payment::orderBy('id','desc');
        if($request->cash_payment_no!=null){
            $payment->where('cash_payment_no',$request->cash_payment_no);
        }
        if($request->debit!=null){
            $payment->where('debit_id',$request->debit);
        }
        if($request->credit!=null){
            $payment->where('credit_id',$request->credit);
        }
        $payment=$payment->paginate(30);
        return response(compact('payment'));
    }
    public function store(Request  $request){
        $latest = Payment::orderBy('id','desc')->first();
        if($latest==null){
            $no=0;
        }else{
            $no=$latest->id;
        }
        $invoice_no = "INV".str_pad((int)$no + 1,8,"0",STR_PAD_LEFT);

        $cash_payment_no = str_pad($invoice_no,5,"0",STR_PAD_LEFT);
        Payment::create([
            'cash_payment_no'=>$cash_payment_no,
            'debit_id'=>$request->debit,
            'credit_id'=>$request->credit,
            'remark'=>$request->remark,
            'amount'=>$request->amount,
            'date'=>$request->date,
            'created_by'=>Auth::user()->id,
            'updated_by'=>Auth::user()->id,
        ]);
        return response()->json([
            'status'=>'success',
        ]);
    }
    public function edit($id){
        $payment=Payment::findOrfail($id);
        return compact('payment');
    }
    public function update($id,Request $request){
//        dd($id);
//        dd($request->all());
        $validatedData = $request->validate([
            'cash_payment_no' => 'max:255|unique:payments,cash_payment_no,'.$id,
        ]);
        Payment::whereId($id)->update([
            'cash_payment_no'=>$request->cash_payment_no,
            'debit_id'=>$request->debit,
            'credit_id'=>$request->credit,
            'remark'=>$request->remark,
            'amount'=>$request->amount,
            'date'=>$request->date,
            'created_by'=>Auth::user()->id,
            'updated_by'=>Auth::user()->id,
        ]);
        return response()->json([
            'status'=>'success',
        ]);
    }
    public function destroy($id){
        Payment::whereId($id)->delete();
    }
}
