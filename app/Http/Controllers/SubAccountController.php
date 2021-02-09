<?php

namespace App\Http\Controllers;

use App\AccountHead;
use App\AccountType;
use App\SubAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubAccountController extends Controller
{
    public function getAll(Request $request){
//        dd($request->all());
        $sub_account=SubAccount::orderBy('sub_account_name','asc');
//        $sub_account=SubAccount::paginate(1);
        if($request->sub_account_name!=''){
            $sub_account->where('sub_account_name', 'LIKE', '%'.$request->sub_account_name.'%');

        }if($request->account_head!=''){
            $sub_account->where('account_head_id',$request->account_head);
        }if($request->account_type!=''){
            $sub_account->where('account_type_id',$request->account_type);
        }
        $sub_account=$sub_account->paginate(30);
        return response(compact('sub_account'),200);
    }
    public function getAccountType(){
        $account_type=AccountType::orderBy('name','asc')->get();
        return compact('account_type');
    }
    public function getAccountHead(){
        $account_head=AccountHead::orderBy('name','asc')->get();
        return compact('account_head');
    }
    public function getAllSubAccount(){
        $sub_account=SubAccount::orderBy('sub_account_name','asc')->get();
        return compact('sub_account');
    }
    public function getSubAccountByAccountHead($id){
        $sub_account=SubAccount::where('account_head_id',$id)->get();
        return response()->json($sub_account);
    }
    public function store(Request  $request){
        SubAccount::create([
            'sub_account_name'=>$request->sub_account_name,
            'account_head_id'=>$request->account_head,
            'account_type_id'=>$request->account_type,
            'created_by'=>Auth::user()->id,
        ]);
        return response()->json(['status'=>'success']);
    }
    public function edit($id){
        $sub_account=SubAccount::find($id);
        return compact('sub_account');
    }
    public function update($id,Request  $request){
        SubAccount::whereId($id)->update([
            'sub_account_name'=>$request->sub_account_name,
            'account_head_id'=>$request->account_head,
            'account_type_id'=>$request->account_type,
            'updated_by'=>Auth::user()->id,
        ]);
        return response()->json(['status'=>'success']);

    }
    public function ChangeStatus($id, $status)
    {
        $active = $status == "active" ? '1' : '0';
        $sub_account=SubAccount::whereId($id)->update(['is_active'=>$active]);
        return response(compact('sub_account'), 200);
    }
    public function destroy($id){
        SubAccount::whereId($id)->delete();
    }
    public function getSubAccount($type){
        if($type==="debit"){
            $sub_account=SubAccount::wherehas('account_type',function ($q){
                $q->where('name','Cash & Bank');
            })->get();
        }
        if($type==="credit"){
            $sub_account=SubAccount::get();
            // wherehas('account_type',function ($q){
            //     $q->where('name','!=','Cash & Bank');
            // })->
        }
//        dd($sub_account);
        return compact('sub_account');
    }
}
