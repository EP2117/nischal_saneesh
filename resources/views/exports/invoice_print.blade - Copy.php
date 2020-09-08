<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
    @font-face {
    font-family: 'ZawgyiOne2008';
      src: url({{ storage_path('fonts/ZawgyiOne2008.ttf') }}) format("truetype");
  }
  .body {
    font-family: 'ZawgyiOne2008' !important;
  }
  .title {
    font-size: 35px;
    text-align:center;  
  }
  .mm-title {
    text-align:center;
    font-size: 25px;
  }
  .mm-txt{
    font-family: 'ZawgyiOne2008' !important;  
    font-size:21px;
  }
  .box {
    float:left;
    width: 100px;
    height: 25px;
    border: 1px #000 solid;
  }

  table#t01 {
    width:100%;
   font-size:21px;
   /* margin-top:20px; */
  } 
  table#t01 tr.tr_heigh td{
    height: 50px;
  }
  td {
    border: 1px solid black;
  }
  th, td {
    padding: 5px;
    text-align: left;
  }
  /*table#t01 tr:nth-child(even) {
    background-color: #eee;
  }
  table#t01 tr:nth-child(odd) {
   background-color: #fff;
  }*/
  table#t01 tr:first-child{
    color: #000;
  }

  header {
  }

  table#t01 tr:first-child td{
    text-align: center;
  }

  table#t02 {
    width:100%;
    font-size:21px;
  }

  table#t02 td{
    border:0;
    margin-top:30px;
  }

  div.absolute {
    position: absolute;
    top: 70px;
    right: 0;
    width: 200px;
  }
  .serial_no {
    width:5%;
  }
  </style>
</head>
<body>
  <?php
    function getUomName($product,$uom_id) {
      $key = -1;
      foreach ( $product->selling_uoms as $k => $v ) {
        if ( $v->pivot->uom_id == $uom_id ) {
          $key = $k;
          break;
        }
      }

      if($key == -1) {
          return $product->uom->uom_name;
      } else {
          return $product->selling_uoms[$key]->uom_name;   
      }
    }

    function getUomRelation($product,$uom_id) {
      $key = -1;
      foreach ( $product->selling_uoms as $k => $v ) {
        if ( $v->pivot->uom_id == $uom_id ) {
          $key = $k;
          break;
        }
      }
      return $product->selling_uoms[$key]->pivot->relation;
    }

  ?>
  <!--<div>    
    <div class = "mm-title mm-txt" style="display: inline-block; margin-left:270px;margin-right:50px; font-size: 25px; vertical-align: top;">အေရာင္းေျပစာ</div>
    <div style="display: inline-block; padding-top:20px; line-height: 0">
      <div style="line-height: 0"><span>aa</label><input type="text" style="border:solid 1px #000; height:25px;line-height: 0" value"aa" /></div>
      <div style="line-height: 0"><label>aa</label><input type="text" style="border:solid 1px #000; height:25px;line-height: 0" value"aa" /></div>
    </div>
  </div>-->
  <!--<div>-->
    <table id="t01" cellpadding="0" cellspacing="0" style="width:100%">
      <thead>
        <tr style="border:none;">
          <td colspan="5" style="border:none;height:330px;">
              &nbsp;
          </td>
        </tr>
        <tr>
          <td colspan="5">
            <table cellpadding="0" cellspacing="0" style="border:none; width:100%;">
                <tr>
                    <td class="mm-txt" style="border:none;">၀ယ္သူ</td>
                    <td class="mm-txt" style="border:none;">{{$sale->customer->cus_name}}</td>
                    <td class="mm-txt" style="text-align:right;border:none;">ရက္စဲြ</td>
                    <td class="mm-txt" style="text-align:right;border:none;">
                      <?php
                        $date_arr = explode('-',$sale->invoice_date);
                      ?>
                      {{$date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0]}}
                    </td>
                </tr>
                <tr>
                    <td class="mm-txt" style="border:none;">လိပ္စာ</td>
                    <td class="mm-txt" style="border:none;">{{$sale->customer->cus_shipping_address}}</td>
                    <td style="text-align:right;border:none;" class="mm-txt">Invoice No.</td>
                    <td style="text-align:right; border:none;" class="mm-txt">{{$sale->invoice_no}}</td>
                </tr>
            </table>
            <!--<div style="float:left;">
              <div class="mm-txt" style="float:left;">
                ၀ယ္သူ <br />
                လိပ္စာ 
              </div>
              <div class="mm-txt" style="margin-left:100px;">
                Aung Aung <br />
                Yangon 
              </div>
            </div>
            <div style="text-align: right;">
              <div class="mm-txt">
                ၀ယ္သူ s   Aung Aung<br />
                လိပ္စာ s   Yangon
              </div>
            </div>-->
          </td>
        </tr>
        <tr class="tr_heigh">
          <td class='mm-txt serial_no' style="text-align: center;">စဥ္</td>
          <td class='mm-txt' style="text-align: center;width:400px;">အမ်ိဳးအမည္</td>
          <td class='mm-txt' style="text-align: center;">အေရအတြက္</td>
          <td class='mm-txt' style="text-align: center;">ေစ်းႏႈန္း</td>
          <td class='mm-txt' style="text-align: center;">သင့္ေငြ</td>
        </tr>
      </thead>
      <?php
        $count = count($sale->products);
        $extra_count = $count < 7 ? 7-$count : 0;
        $k = 0;
        foreach($sale->products as $product) {
          $k++;
      ?>
        <tr class="tr_heigh">
          <td style="text-align: center;">{{$k}}</td>
          <td class="mm-txt">{{$product->product_name}}</td>

          @if($product->pivot->uom_id == $product->uom_id)
          <td style="text-align: right;">
              {{$product->pivot->product_quantity}} {{getUomName($product,$product->pivot->uom_id)}}
          </td>
          @else
          <td style="text-align: right;">
              {{$product->pivot->product_quantity}} {{getUomName($product,$product->pivot->uom_id)}} x {{getUomRelation($product,$product->pivot->uom_id)}} {{getUomName($product,$product->uom_id)}}
          </td>
          @endif

          @if($product->pivot->is_foc == 0)
            <td style="text-align: right;">{{$product->pivot->price}}</td>
            <td style="text-align: right;">{{number_format($product->pivot->total_amount)}}</td>
          @else
            <td style="text-align: right">FOC</td>
            <td></td>
          @endif

        </tr>
      <?php
        }
        for($i=0; $i<$extra_count; $i++) {
          $k++;
      ?>

      <tr class="tr_heigh">
        <td style="text-align: right;"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>      
      <?php
        }
      ?>

      <tr class="tr_heigh">
        <td colspan="4" class="mm-txt" style="text-align: right;">စုစုေပါင္း</td>
        <td style="text-align: right;">{{number_format($sale->total_amount)}}</td>
      </tr>
      <tr>
        <td colspan="2"><br /><br />Check By</td>
        <td></td>
        <td colspan="2" style="text-align: right;"><br /><br />Receive By</td>
      </tr>
      <!--<tfoot>
        <tr style="border:none;">
          <td colspan="5" style="border:none;">
              <div style="height:30px;">&nbsp;</div>
          </td>
        </tr>
      </tfoot>-->
    </table>
</body>
</html>