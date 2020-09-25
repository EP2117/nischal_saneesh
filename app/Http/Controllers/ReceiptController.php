<?php

namespace App\Http\Controllers;

use App\Recepit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function getAll(Request $request){
        $receipt=Recepit::orderBy('id','desc');
        if($request->cash_receipt_no!=null){
            $receipt->where('cash_receipt_no',$request->cash_receipt_no);
        }
        if($request->debit!=null){
            $receipt->where('debit_id',$request->debit);
        }
        if($request->credit!=null){
            $receipt->where('credit_id',$request->credit);
        }
        $receipt=$receipt->paginate(30);
        return response(compact('receipt'));
    }
    public function store(Request  $request){
        $latest = Recepit::orderBy('id','desc')->first();
        if($latest==null){
            $no=0;
        }else{
            $no=$latest->id;
        }
        $invoice_no = "INV".str_pad((int)$no + 1,8,"0",STR_PAD_LEFT);

        $cash_receipt_no = str_pad($invoice_no,5,"0",STR_PAD_LEFT);
        Recepit::create([
            'cash_receipt_no'=>$cash_receipt_no,
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
        $receipt=Recepit::findOrfail($id);
        return compact('receipt');
    }
    public function update($id,Request $request){
//        dd($id);
//        dd($request->all());
        $validatedData = $request->validate([
            'cash_receipt_no' => 'max:255|unique:receipts,cash_receipt_no,'.$id,
        ]);
        Recepit::whereId($id)->update([
            'cash_receipt_no'=>$request->cash_receipt_no,
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
        Recepit::whereId($id)->delete();
    }
}
