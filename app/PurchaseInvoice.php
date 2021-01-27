<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $table='purchase_invoices';
    protected $with=['products','products.uom', 'warehouse','supplier','products.selling_uoms','office_purchase_man','branch'];
//    protected $fillable=['invoice_no','reference_no','invoice_date','supplier_id']
    public function products()
    {
        return $this->belongsToMany('App\Product', 'product_purchase', 'purchase_id', 'product_id')->withPivot('id','uom_id','product_quantity','delivered_quantity','price','price_variant','total_amount','is_foc','wt');
    }   
    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse', 'warehouse_id', 'id')->select('id', 'warehouse_name');
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
    public function branch()
    {
        return $this->belongsTo('App\Branch','branch_id','id')->select('id', 'branch_name');
    }
    public function office_purchase_man()
    {
        return $this->belongsTo('App\User', 'office_purchase_man_id', 'id')->select('id', 'name');
    }
}
