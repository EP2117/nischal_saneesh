<?php

namespace App\Http\Controllers;

use stdClass;
use App\AccountHead;
use App\AccountTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Report\GetReport;
use App\Http\Traits\AccountReport\Ledger;
use App\Http\Traits\AccountReport\Cashbook;

class AccountTransitionController extends Controller
{
    use Cashbook;
    use Ledger;
    use GetReport;
    public function __construct(){
        $this->total_revenue=0;
        $this->total_cor=0;
        $this->account_head_name=null;
    }
    public function getAllCashbook(Request $request){
        $date_arr=$this->getDateArr($request);
        $cashbook=$this->getCashBookList($request);
        return response(compact('cashbook'));
    }
    public function getAllLedger(Request $request){
        $ledger=$this->getLedgerList($request);
        return response()->json([
            'ledger'=>$ledger,
            'account_type'=>$request->type,
        ]);
    }
    public function getProfitAndLossReport(Request $request){
        $month = Carbon::now()
        ->year($request->year)
        ->month($request->month);
        // dd($targetDate);
        // dd($request->all());
        // $profit_and_loss=$this->getProfitAndLoss($request);
        $account_head=AccountHead::where('name','Revenue')
        ->orWhere('name','Cost Of Revenue')->get();
        // whereHas('financial_type1',function($q){
        //     $q->where('name','P&L');
        // })->get();
       
       foreach($account_head as $key=>$ah){
                   $pl=AccountTransition::where('is_cashbook',0);
                   $request->to_date= $request->to_date !=null ? $request->to_date : now()->today();
                   $pl->when(!is_null($request->from_date),function($q)use($request){
                       return  $q->whereDate('transition_date','>=',$request->from_date);
                   });
                   $pl->when(!is_null($request->from_date),function($q){
                       return  $q->whereBetween('transition_date',[request('from_date'),request('to_date')]);
                   });
                   if($request->month){
                    $pl->whereMonth('transition_date','=',$request->month);
                }
                $request->year=$request->year==null ? Carbon::now()->year :  $request->year;
                if($request->year){
                    $pl->whereYear('transition_date','=',$request->year);
                }
               if($ah->name=='Revenue'){
                   $pl=$pl->whereHas('sub_account.account_head',function($q)use($ah){
                    $q->whereId($ah->id);
                })->selectRaw('*, sum(credit) as sale_amount')->groupBy('sub_account_id')->get();
                   if($pl->isNotEmpty()){
                    $profitAndLoss[$ah->name]=new stdClass();

                       foreach($pl as $k=>$p){
                           $revenue=new stdClass();
                           $revenue->name=$p->sub_account->sub_account_name;
                           $revenue->amount=(int)$p->sale_amount;
                           $this->total_revenue=$p->sale_amount;
                       }
                       $profitAndLoss[$ah->name]->revenue=$revenue;
                   }
                //    else{
                //     $profitAndLoss=null;
                //    }
                   // *********** for cost of revenue *********
               }
               else if($ah->name=='Cost of Revenue'){
                // $pl->selectRaw('*, sum(credit) as sale_amount')->groupBy('sub_account_id')->get();
                   $sale=config('global.sale');
                   $pl=AccountTransition::where('sub_account_id',$sale)->where('is_cashbook',0);
                   // $pl->whereHas('sale.products',function($q){
                   //     $q->
                   // });
                   $request->to_date= $request->to_date !=null ? $request->to_date : now()->today();
                   $pl->when(!is_null($request->from_date),function($q)use($request){
                       return  $q->whereDate('transition_date','>=',$request->from_date);
                   });
                   $pl->when(!is_null($request->from_date),function($q){
                    return  $q->whereBetween('transition_date',[request('from_date'),request('to_date')]);
                });
                   if($request->month){
                    // $pla->whereIn(DB::raw('MONTH(transition_date)'),[$request->month]);
                    $pl->whereMonth('transition_date','=',$request->month);
                }
                 $request->year=$request->year==null ? Carbon::now()->year :  $request->year;
                if($request->year){
                    $pl->whereYear('transition_date','=',$request->year);
                }
                
                   $pl=$pl->get();
                //    dd($pl);
                   $amount=0;
                   if($pl->isNotEmpty()){
                    $profitAndLoss[$ah->name]=new stdClass();
                    foreach($pl as $p){
                        // $product_sale=DB::table('product_sale')->where('sale_id',$p->sale_id)->get();
                        $product_sale=DB::table('product_transitions')
                        ->where('transition_sale_id',$p->sale_id)->get();
                        foreach($product_sale as $ps){
                            // $product=Product::find($ps->product_id);
                            // $amount+=(int)$product->cost_price* (int)$ps->product_quantity;
                            $amount+=(int)$ps->cost_price;
                        }
                    }
                    $cost_of_revenue=new stdClass();
                    $cost_of_revenue->name='Cost Of Good Sold';
                    $cost_of_revenue->amount=$amount;
                    $this->total_cor=$amount;
                    $profitAndLoss[$ah->name]->cost_of_revenue=$cost_of_revenue;
                   }
                //    else{
                //     $profitAndLoss=null;
                //    }
                  
               }
        }

        $total_income=$total_expense=0;
        $ah_income= $account_head=AccountHead::whereHas('financial_type2',function($q){
            $q->where('name','Income');
            //    ->orWhere('name','Expense');
        })
        // ->orderBy('id','desc')
        ->get();
        $ah_expense= $account_head=AccountHead::whereHas('financial_type2',function($q){
            $q->where('name','Expense');
        })->get();
        foreach($ah_income as $key=>$ai){
            $pla=AccountTransition::where('is_cashbook',0);
            $request->to_date= $request->to_date !=null ? $request->to_date : now()->today();
            $pla->when(!is_null($request->from_date),function($q)use($request){
                return  $q->whereDate('transition_date','>=',$request->from_date);
            });
            $pla->when(!is_null($request->from_date),function($q){
                return  $q->whereBetween('transition_date',[request('from_date'),request('to_date')]);
            });
            if($request->month){
                // $pla->whereIn(DB::raw('MONTH(transition_date)'),[$request->month]);
                $pla->whereMonth('transition_date','=',$request->month);
            }
            $request->year=$request->year==null ? Carbon::now()->year :  $request->year;
            if($request->year){
                $pla->whereYear('transition_date','=',$request->year);
            }
          
            $pla->whereHas('sub_account.account_head',function($q)use($ai){
                $q->where('id',$ai->id);
            });
            $pla->selectRaw('*, sum(credit) as amount,sum(debit) as deb_amount')->groupBy('sub_account_id');
            $pla=$pla->get();
            // dd($pla);
            // if($ai->name==='Other Income'){
                $total=0;
                if($pla->isNotEmpty()){
                    foreach($pla as $k=>$p){
                        $index_income[$key]=new stdClass();
                        // if($pla[$k]->sub_account->account_head->id==$ai->id){
                            $income[$key][$k]=new stdClass();
                            $income[$key][$k]->sub_account_name=$pla[$k]->sub_account->sub_account_name;
                            $income[$key][$k]->sub_account_id=$pla[$k]->sub_account_id;
                            $income[$key][$k]->amount=$pla[$k]->amount;
                            $this->account_head_name=$pla[$k]->sub_account->account_head->name;
                            $total+=$p->amount;
                        // }
                    }
                    // if($this->account_head_name==$ai->name){
                        $index_income[$key]->account_head_name=$ai->name;
                        $index_income[$key]->income=$income[$key];
                        $index_income[$key]->total=$total;
                        $total_income+=$total;
                    // }
                }
                // else{
                //     $index_income[$key]=null;
                //     // $index_income[$key]=new stdClass();
                // }
        }
      
        foreach($ah_expense as $key=>$ae){
            $ple=AccountTransition::where('is_cashbook',0);
            $request->to_date= $request->to_date !=null ? $request->to_date : now()->today();
            $ple->when(!is_null($request->from_date),function($q)use($request){
                return  $q->whereDate('transition_date','>=',$request->from_date);
            });
            $ple->when(!is_null($request->from_date),function($q){
                return  $q->whereBetween('transition_date',[request('from_date'),request('to_date')]);
            });
            if($request->month){
                $ple->whereMonth('transition_date','=',$request->month);
            }
            $request->year=$request->year==null ? Carbon::now()->year :  $request->year;
            if($request->year){
                $ple->whereYear('transition_date','=',$request->year);
            }
            $ple->whereHas('sub_account.account_head',function($q)use($ae){
                $q->whereId($ae->id);
            })->selectRaw('*, sum(debit) as amount')->groupBy('sub_account_id');
            $ple=$ple->get();
            // if($ai->name==='Other Income'){
                if($ple->isNotEmpty()){
                    $total=0;
                    foreach($ple as $k=>$p){
                        $index_expense[$key]=new stdClass();
                        if($p->sub_account->account_head->name==$ae->name){
                            $expense[$key][$k]=new stdClass();
                            $expense[$key][$k]->sub_account_name=$p->sub_account->sub_account_name;
                            $expense[$key][$k]->amount=$p->amount;
                            $this->account_head_name=$p->sub_account->account_head->name;
                            $total_expense+=$p->amount;
                            $total+=$p->amount;
                        }
                    }
                    // if($this->account_head_name==$ae->name){
                        $index_expense[$key]->account_head_name=$ae->name;
                        $index_expense[$key]->expense=$expense[$key];
                        $index_expense[$key]->total=$total;
                    // }
                }
                // else{
                //     $index_expense=null;
                //     // $index_expense=new stdClass();
                // }
        }
        
        $gross_profit=$this->total_revenue-$this->total_cor;
        $net_profit=($gross_profit+$total_income)- $total_expense;
        // return compact('index_income');
        return response()->json([
            'profit_and_loss'=>isset($profitAndLoss) ? $profitAndLoss : '',
            'gross_profit'=>$gross_profit,
            'total_income'=>$total_income,
            'income'=>isset($index_income) ? $index_income :'',
            'expense'=>isset($index_expense) ? $index_expense :'',
            'total_expense'=>$total_expense,
            'net_profit'=>$net_profit,
            'status'=>200,
        ]);
    }
}
