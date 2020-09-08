<template>
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#!"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/'">Home</a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/office'">Office Sale</a></li>
                <li class="breadcrumb-item"><router-link tag="span" to="/order" class="font-weight-normal"><a href="#">Sale Order</a></router-link></li>
                <li class="breadcrumb-item active" aria-current="page">Sale Order Form</li>

            </ol>
        </nav>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">Sale Order Form</h4>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Entry Details</h6>
            </div>
            <div class="card-body">
                <div class="d-block">
                    <!-- form start -->
                    <form class="form" method="post" @submit.prevent="onSubmit" @keydown="form.errors.clear($event.target.name)">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="order_no">Sale Order No.</label>
                                <input type="text" class="form-control" id="order_no" name="order_no" v-model="form.order_no" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="order_date">Date</label>
                                <input type="text" class="form-control datetimepicker" id="order_date" name="order_date"
                                v-model="form.order_date" required readonly>
                            </div>
                        </div>

                        <div class="row mt-3">
                             <div class="form-group col-md-4">
                                <label for="customer_id">Customer</label>
                                <select id="customer_id" class="customer_id mm-txt"
                                    name="customer_id" v-model="form.customer_id" style="width:100%" :disabled="readonly_status" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="cus in customers" :value="cus.id"  >{{cus.cus_name}}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="warehouse">Warehouse</label>
                                 <input type="text" class="form-control" id="warehouse" name="warehouse"
                                v-model="user_warehouse" readonly>
                            </div>
                           
                        </div>

                        <div class="row mt-3">
                            <div class="form-group col-md-4">
                                <label for="sale_man">Sale Man</label>
                                <input type="text" class="form-control" id="sale_man" name="sale_man"
                                v-model="sale_man" readonly>
                            </div> 
                            <div class="form-group col-md-4">
                                <label for="branch_name">Branch</label>
                                 <input type="text" class="form-control" id="branch_name" name="branch_name"
                                v-model="user_branch" readonly>
                            </div>                        
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <span class="d-none d-sm-inline-block btn-sm btn-primary shadow-sm bg-blue"><i class="fas fa-search-plus text-white"></i> Product Details</span>
                            </div>
                        </div>

                        <div class="row mt-0">
                            <div class="col-md-12 text-right mt-0">
                                <a class='blue-icon' title='Add Product' @click="addProduct()" v-if="user_role != 'admin' && (order_status == 'Draft' || order_status == '')"><i class='fas fa-plus-square' style='font-size: 30px;'></i></a>
                                <div style="display:none;">
                                 <select class="form-control txt_product"
                                    id="txt_product" style="min-width:150px;"
                                >
                                    <option value="">Select One</option>
                                    <option v-for="product in products" :data-uom="product.uom_name" 
                                    :data-price="product.product_price"
                                    :data-uomid="product.uom_id" :value="product.product_id" 
                                    data-pivotid = "0">{{product.product_name}}</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="thead-grey">
                                        <tr>
                                            <th scope="col" >Product Name</th>
                                            <th scope="col" >Quantity</th>
                                            <th scope="col" >Selling Unit</th>
                                            <th scope="col" >Relation</th>
                                            <th scope="col" >FOC</th>
                                            <th scope="col">Unit Price</th>
                                            <th scope="col" >Total Amount</th>
                                            <th scope="col" class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        <template v-if="isEdit && ex_products.length > 0">
                                        </template>
                                        <template v-else>                                      
                                        <tr>
                                            <td>                                               
                                                <select class="form-control txt_product"
                                                    name="product[]" style="min-width:150px;" required
                                                >
                                                    <option value="">Select One</option>
                                                    <option v-for="product in products" :data-uom="product.uom_name" 
                                                    :data-price="product.product_price"
                                                    :data-uomid="product.uom_id" :value="product.product_id" 
                                                    data-pivotid = "0">{{product.product_name}}</option>
                                                </select>
                                            </td>                                                
                                            <td>
                                                <input type="text" class="form-control num_txt txt_qty" style="width:100px;" name="qty[]"  @blur="checkQty($event.target)" required />
                                            </td>
                                            <td>
                                                <select class="form-control txt_uom"
                                                    name="uom[]" style="min-width:150px;" data-uom="" :disabled="readonly_status"required
                                                >
                                                    <option value="">Select One</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" style="min-width:200px;" class="form-control txt_relation" name="relation[]" readonly />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    name="chk_foc[]"
                                                    @change="checkFoc($event.target)"
                                                    :disabled="readonly_status"
                                                >
                                            </td>
                                            <td>
                                                <input type="text" class="form-control float_num"  style="width:100px;" name="unit_price[]" @blur="calTotalAmount($event.target)" required />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control num_txt" readonly style="width:100px;" name="total_amount[]" required />
                                            </td>
                                            <td class="text-center">
                                                <a class='remove-row red-icon' title='Remove' v-if="user_role != 'admin' && (order_status == 'Draft' || order_status == '')"><i class='fas fa-times-circle' style='font-size: 25px;'></i></a>
                                            </td>
                                        </tr>
                                        </template>
                                        <tr class="total_row">
                                            <td colspan="6" class="text-right">Sub Total</td>
                                            <td colspan="2">
                                                <input type="text" v-model="form.sub_total" class="form-control num_txt" readonly style="width:150px;" required />
                                            </td>
                                        </tr>                                       
                                    </tbody>
                                </table>


                            </div>                         

                        </div>
                        <div class="row mt-3">
                            <div class="form-group col-md-8 mm-txt">
                                <label for="remark">Remark</label>
                                <textarea rows='3' class="form-control mm-txt" id="remark" name="remark"
                                v-model="form.remark"></textarea>
                            </div>                           
                        </div>

                        <div class="row">
                            <div class="col-md-12" v-if="order_status == 'Draft' || order_status == ''">
                                <input type="submit" class="btn btn-primary btn-sm" value="Save Entry" :disabled="isDisabled">
                            </div>
                        </div>

                    </form>                    
                <!-- form end -->  
                </div>

            </div>
        </div>
        <div id="loading" class="text-center"><img :src="storage_path+'/image/loader_2.gif'" /></div>
    </div>

</template>

<script>
    export default {
        data() {
            return {
              form: new Form({
                order_date: "",
                order_no: "",
                warehouse: "",
                customer_id: "",
                product: [],
                uom: [],
                qty: [],
                unit_price: [],
                total_amount: [],
                sub_total: 0,
                ex_product_pivot: [],
                product_pivot: [],
                is_foc: [],
                uom_id: "",
                price_variants: [],
                sale_man_id: '',
                remark: '',
              }),
              isEdit: false,
              products: [],
              order_id: '',
              ex_products: [],
              user_warehouse: '',
              selling_uoms: [],
              customers: [],
              user_role: '',
              user_id: '',
              order_status: '',
              sale_man: '',
              readonly_status: false,
              isDisabled: false,
              user_brands: [],
              order_products: [],
              user_branch: '',
              site_path: '',
              storage_path: '',
            };
        },

        created() {
            this.user_warehouse = document.querySelector("meta[name='user-wh']").getAttribute('content');

            this.user_role = document.querySelector("meta[name='user-role']").getAttribute('content');

            this.user_branch = document.querySelector("meta[name='user-branch']").getAttribute('content');

            this.user_id = document.querySelector("meta[name='user-id-likelink']").getAttribute('content');

            this.sale_man = document.querySelector("meta[name='user-name-likelink']").getAttribute('content');

            this.form.sale_man_id = document.querySelector("meta[name='user-id-likelink']").getAttribute('content');

            this.site_path = document.querySelector("meta[name='site-path']").getAttribute('content');
            //this.site_path = this.site_path.substring(this.site_path.lastIndexOf('/')+1);
            this.storage_path = document.querySelector("meta[name='storage-path']").getAttribute('content');
            
            /***** if(this.user_role == "office_order_user" && this.user_role != "admin" && this.user_role != "system") {
                var url =  window.location.origin;
                window.location.replace(url);
            }  *****/

            if(this.user_role == "van_user" || this.user_role == "office_user") {
                var url =  window.location.origin;
                window.location.replace(url);
            }   

            if(this.$route.params.id) {
                this.isEdit = true;
                this.order_id = this.$route.params.id;
                this.getOrder(this.order_id);
            }

            this.form.order_date = moment().format("YYYY-MM-DD");

            if(this.order_status != 'Draft' && this.order_status != '') {
                this.readonly_status = "disabled";
            }
        },
        mounted() {

            $("#loading").hide();
            let app = this;

            app.initProducts();
           // app.initWarehouses();

            app.initCustomers();

            $(".txt_product").select2();

            app.initOrders();

            $("#customer_id").select2();
            $("#customer_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.form.customer_id = data.id;
            });

            $(".txt_product").on("select2:select", function(e) {

                var data = e.params.data;

                app.selling_uoms = [];

               var uom      = e.target.options[e.target.options.selectedIndex].dataset.uom;
               var uom_id   = e.target.options[e.target.options.selectedIndex].dataset.uomid;
               var price    = e.target.options[e.target.options.selectedIndex].dataset.price;

                //$(this).closest('td').next().find('.txt_uom').val(uom);
                //$(this).closest('td').next().find('.txt_uom').attr('data-id',uom_id);

                $(this).closest('td').next().next().find('.txt_uom').attr('data-uom',uom);

                //auto add new product row
                if($(this).closest('tr').next().hasClass("total_row")) {
                    app.addProduct();
                }

               //add Warehouse UOM Selling Price
                $(this).closest('td').next().next().next().next().next().find('input').val(price);

                var selectbox_id = $(this).closest('td').next().next().find('.txt_uom');

                selectbox_id.find('option').remove();

                //add pre-defined product uom 

                if(selectbox_id.find('option[value="'+uom_id+'"]').text() == "") {
                    selectbox_id.append('<option value="">Select One</option><option value="'+uom_id+'" data-relation="" data-uomqty = "1" data-productuom = "'+uom+'" data-productid="'+data.id+'" data-perprice="'+price+'" data-price="'+price+'" selected>'+uom+'</option>'); 
                }
                $(".txt_uom").select2();

                app.getSellingUomByProduct(selectbox_id, data.id);
            });

            $(".txt_uom").select2();

            $(".txt_uom").on("select2:select", function(e) {
                app.checkQty(e.target.options[e.target.options.selectedIndex]);
                var uom_relation = e.target.options[e.target.options.selectedIndex].dataset.relation;
                   //for van sale
                   // var uom_price = e.target.options[e.target.options.selectedIndex].dataset.price;

                //for office sale
                var uom_price = e.target.options[e.target.options.selectedIndex].dataset.perprice;

                $(this).closest('td').next().find('.txt_relation').val(uom_relation);
                $(this).closest('td').next().next().next().find('input').val(uom_price);

                app.calTotalAmount($(this).closest('td').next().next().next().find('input'));
            });

            $(".datetimepicker")
                .datetimepicker({
                icons: {
                        time: "fa fa-clock-o",
                        date: "fa fa-calendar",
                        up: "fa fa-chevron-up",
                        down: "fa fa-chevron-down",
                        previous: "fa fa-chevron-left",
                        next: "fa fa-chevron-right",
                        today: "fa fa-screenshot",
                        clear: "fa fa-trash",
                        close: "fa fa-remove"
                    },
                    format:"YYYY-MM-DD",
                })
                .on("dp.change", function(e) {
                    var formatedValue = e.date.format("YYYY-MM-DD");
                    //console.log(formatedValue);
                    app.form.order_date = formatedValue;
                });

            /*$(document).on('click','.add-new',function(evt){
                app.addProduct();
            });*/                


            $(document).on('click','.remove-row',function(evt) {
                if(document.getElementsByName('product[]').length <= 1) {                    
                    swal("Warning!", "At least one product must be added!", "warning")
                } else {
                    $(this).parents("tr").remove();
                    var sub_total = 0; 
                    for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                        if(document.getElementsByName('total_amount[]')[i].value != "") {
                            sub_total += parseInt(document.getElementsByName('total_amount[]')[i].value);
                        }
                    }

                    app.form.sub_total = sub_total; 
                }
            });

            $(document).on('click','.remove-exrow',function(evt) {
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true
                    }).then(willDelete => {
                    if (willDelete) {
                       if(document.getElementsByName('product[]').length <= 1) {                    
                            swal("Warning!", "At least one product must be added!", "warning")
                        } else {
                            $(this).parents("tr").remove();
                            var sub_total = 0; 
                            for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                                if(document.getElementsByName('total_amount[]')[i].value != "") {
                                    sub_total += parseInt(document.getElementsByName('total_amount[]')[i].value);
                                }
                            }

                            app.form.sub_total = sub_total; 
                        }   
                    } else {
                      //
                    }
                });
            });
           
        },

        methods: {
            initProducts() {
              axios.get("/order/products/").then(({ data }) => (this.products = data.data));
              console.log(this.products);
              $(".txt_product").select2();
            },

            initOrders() {
               if(this.isEdit) {
                //alert(this.order_id);
                axios.get("/order_products/"+this.order_id).then(({ data }) => (this.order_products = data.order_data));
               } else {
                axios.get("/order_products/").then(({ data }) => (this.order_products = data.order_data));
               }
            },

            initWarehouses() {
              axios.get("/warehouses").then(({ data }) => (this.warehouses = data.data));
              $("#warehouse").select2();
            },

            initCustomers() {
              axios.get("/customers").then(({ data }) => (this.customers = data.data));
              $("#customer_id").select2();
            },

            getSellingUomByProduct(selectbox_id,product_id) {
                let app = this;
              axios.get("/product/selling_uom/"+ product_id).then(function(response) {
                var uom_arr = response.data.s_uoms.selling_uoms;
                var uom_relation = "";
                $.each(uom_arr, function(index, value) {
                    uom_relation = "1 "+value.uom_name+" = "+ value.pivot.relation +" "+ selectbox_id.attr('data-uom');
                    if(selectbox_id.find('option[value="'+value.pivot.uom_id+'"]').text() == "") {
                        selectbox_id.append('<option value="'+value.pivot.uom_id+'" data-relation="'+uom_relation+'" data-uomqty="'+value.pivot.relation+'" data-productuom = "'+selectbox_id.attr('data-uom')+'" data-productid="'+product_id+'" data-perprice="'+value.pivot.per_warehouse_uom_price+'" data-price="'+value.pivot.product_price+'" >'+value.uom_name+'</option>');
                    }
                });

                $(".txt_uom").select2();
                  
              });
            },

            addProduct() {
                let app = this;
                var table=document.getElementById("product_table");
                var row=table.insertRow((table.rows.length)-1);
                var cell1=row.insertCell(0);

                var t1=document.createElement("select");
                    t1.name = "product[]";
                    t1.className = "form-control txt_product";
                    t1.style = "min-width:150px;";
                    $(t1).attr("required", true);

                    var option = document.createElement("option");
                    option.value = '';
                    option.text = "Select One";
                    t1.append(option);

                    /*$.each(this.products, function(index, value) {
                       var option = document.createElement("option");
                       option.value = value.product_id;
                       $(option).attr('data-uom',value.uom_name);
                       $(option).attr('data-uomid',value.uom_id);
                       $(option).attr('data-price',value.product_price);
                       $(option).attr('data-pivotid','0');
                       option.text = value.product_name;
                       t1.append(option);
                     }); */

                    var html = $('#txt_product').html();
                    $(t1).html(html);                    
                    cell1.appendChild(t1);

                var cell2=row.insertCell(1);
                var t2=document.createElement("input");
                    t2.name = "qty[]";
                    t2.style = "width:100px;";
                    t2.className ="form-control num_txt";
                    $(t2).attr("required", true);
                    t2.addEventListener('blur', function(){ app.checkQty(t2); });
                    cell2.appendChild(t2);
                   
                var cell3=row.insertCell(2);

                var t3=document.createElement("select");
                    t3.name = "uom[]";
                    t3.className = "form_control txt_uom";
                    t3.style = "min-width:150px;";
                    $(t3).attr("required", true);
                    t3.addEventListener('blur', function(){ app.checkQty(t3); });

                    var option = document.createElement("option");
                    option.value = '';
                    option.text = "Select One";
                    $(option).attr('data-relation',"");
                    $(option).attr('data-price', "");
                    $(option).attr('data-perprice', "");
                    t3.append(option);

                 cell3.appendChild(t3);


                var cell4=row.insertCell(3);
                var t4=document.createElement("input");
                    t4.type = "text";
                    t4.name = "relation[]";
                    t4.style = "min-width:200px;";
                    t4.className ="form-control txt_relation";
                    $(t4).attr('readonly', true);
                    cell4.appendChild(t4);
                

                $(".txt_product").select2();

                $(".txt_uom").select2();

                $("#customer_id").select2();
                $("#customer_id").on("select2:select", function(e) {

                    var data = e.params.data;
                    app.form.customer_id = data.id;
                });

                $(".txt_product").on("select2:select", function(e) {
                    var data = e.params.data;

                    app.selling_uoms = [];

                   var uom = e.target.options[e.target.options.selectedIndex].dataset.uom;
                   var uom_id = e.target.options[e.target.options.selectedIndex].dataset.uomid;
                   var price = e.target.options[e.target.options.selectedIndex].dataset.price;

                    //$(this).closest('td').next().find('.txt_uom').val(uom);
                    //$(this).closest('td').next().find('.txt_uom').attr('data-id',uom_id);

                    $(this).closest('td').next().next().find('.txt_uom').attr('data-uom',uom);

                    //auto add new product row
                    if($(this).closest('tr').next().hasClass("total_row")) {
                        app.addProduct();
                    }

                    //add Warehouse UOM Selling Price
                    $(this).closest('td').next().next().next().next().next().find('input').val(price);
                    

                    var selectbox_id = $(this).closest('td').next().next().find('.txt_uom');

                    selectbox_id.find('option').remove();

                    //add pre-defined product uom 
                    if(selectbox_id.find('option[value="'+uom_id+'"]').text() == "") {
                        selectbox_id.append('<option value="">Select One</option><option value="'+uom_id+'" data-relation="" data-uomqty="1" data-productuom = "'+uom+'" data-productid="'+data.id+'" data-price="'+price+'" data-perprice="'+price+'" selected>'+uom+'</option>'); 
                    }
                    $(".txt_uom").select2();

                    app.getSellingUomByProduct(selectbox_id, data.id);
                });

                $(".txt_uom").select2();

                $(".txt_uom").on("select2:select", function(e) {
                    app.checkQty(e.target.options[e.target.options.selectedIndex]);
                    var uom_relation = e.target.options[e.target.options.selectedIndex].dataset.relation;

                    //for van sale
                    //var uom_price = e.target.options[e.target.options.selectedIndex].dataset.price;
                    
                    //for office sale
                    var uom_price = e.target.options[e.target.options.selectedIndex].dataset.perprice;

                    $(this).closest('td').next().find('.txt_relation').val(uom_relation);
                    $(this).closest('td').next().next().next().find('input').val(uom_price);

                    app.calTotalAmount($(this).closest('td').next().next().next().find('input'));
                });

                var cell5=row.insertCell(4);
                    cell5.className = "text-center";
                var t5=document.createElement("input");
                    t5.type = "checkbox";
                    t5.name = "chk_foc[]";
                    t5.addEventListener('change', function(){ app.checkFoc(t5); });
                    cell5.appendChild(t5);

                var cell6=row.insertCell(5);
                var t6=document.createElement("input");
                    t6.name = "unit_price[]";
                    t6.style = "width:100px;";
                    t6.className ="form-control float_num";
                    $(t6).attr("required", true);
                    t6.addEventListener('blur', function(){ app.calTotalAmount(t6); });
                    cell6.appendChild(t6);

                var cell7=row.insertCell(6);
                var t7=document.createElement("input");
                    t7.name = "total_amount[]";
                    t7.style = "width:100px;";
                    t7.className ="form-control num_txt";
                    $(t7).attr("required", true);
                    $(t7).attr("readonly", true);
                   // t2.addEventListener('blur', function(){ app.checkQty(t2); });
                    cell7.appendChild(t7);

                var cell8=row.insertCell(7);
                cell8.className = "text-center";
                var row_action = "<a class='remove-row red-icon' title='Remove'><i class='fas fa-times-circle' style='font-size: 25px;'></i></a>";
                $(cell8).append(row_action);

            },

            getOrder(id) {
              let app = this;
              axios
                .get("/order/" + id)
                .then(function(response) {                
                    app.form.order_date = moment(response.data.order.order_date).format('YYYY-MM-DD');
                    app.ex_products = response.data.order.products;
                    console.log(response.data.order.products);
                    app.form.order_no = response.data.order.order_no;
                    app.form.remark = response.data.order.remark;
                    if(response.data.order.branch != null) {
                        app.user_branch = response.data.order.branch.branch_name;
                    } else {
                        app.user_branch = '';
                    }

                    //for edit permission (save btn)
                    if(app.user_role == 'system' || app.user_id == response.data.order.sale_man_id) {
                        app.isDisabled = false;
                    } else {
                        app.isDisabled = true;
                    }

                    if(response.data.order.sale_man_id == null) {
                        app.form.sale_man_id = '';
                        app.sale_man = '';
                    } else {
                        app.form.sale_man_id = response.data.order.sale_man_id;
                        app.sale_man = response.data.order.sale_man.name;
                    }                    

                    app.order_status = response.data.order.order_status;
                    $("#customer_id").append('<option value="'+response.data.order.customer_id+'" selected>'+response.data.order.customer.cus_name+'</option>');
                    app.form.customer_id = response.data.order.customer_id;

                    if(app.order_status != 'Draft' && app.order_status != '') {
                        app.readonly_status = "disabled";
                    }

                    app.form.sub_total  = response.data.order.total_amount;

                    $.each(app.ex_products, function( key, value ) {
                        app.form.ex_product_pivot.push(value.pivot.id);
                    });

                    //add  products dynamically
                    var subTotal = 0;
                    $.each(app.ex_products, function( key, product ) {                        
                        if(app.user_role != "Country Head" || (app.user_role == "Country Head" && response.data.access_brands.indexOf(product.brand_id) > -1)) {
                            var table=document.getElementById("product_table");
                            var row=table.insertRow((table.rows.length) - 1);
                            var cell1=row.insertCell(0);

                            var t1=document.createElement("select");
                                t1.name = "product[]";
                                t1.className = "form-control txt_product";
                                t1.style = "min-width:150px;";
                                $(t1).attr("required", true);
                                $(t1).attr('readonly', true);

                               var option = document.createElement("option");
                               option.value = product.id;
                               option.className ="form-control";
                               $(option).attr('data-uom',product.uom.uom_name);
                               $(option).attr('data-uomid',product.uom.uom_id);
                               $(option).attr('data-price','');
                               $(option).attr('data-pivotid',product.pivot.id);
                               option.text = product.product_name;
                               t1.append(option);

                                cell1.appendChild(t1);

                            var cell2=row.insertCell(1);
                            var t2=document.createElement("input");
                                t2.name = "qty[]";
                                t2.value = product.pivot.product_quantity;
                                t2.style = "width:100px;";
                                t2.className ="form-control num_txt";
                                $(t2).attr("required", true);

                                if(app.order_status != 'Draft' && app.order_status != '') {
                                    $(t2).attr('readonly', true);
                                }

                                t2.addEventListener('blur', function(){ app.checkQty(t2); });
                                cell2.appendChild(t2);                            
                               
                            var cell3=row.insertCell(2);

                            var t3=document.createElement("select");
                                t3.name = "uom[]";
                                t3.className = "form-control txt_uom";
                                t3.style = "width:200px;";
                                $(t3).attr("required", true);

                                if(app.order_status != 'Draft' && app.order_status != '') {
                                    $(t3).attr('disabled', 'disabled');
                                }
                                t3.addEventListener('blur', function(){ app.checkQty(t3); });

                                var option = document.createElement("option");
                                option.value = '';
                                option.text = "Select One";
                                $(option).attr('data-relation',"");
                                $(option).attr('data-price', "");
                                $(option).attr('data-perprice', "");
                                t3.append(option);

                                var option = document.createElement("option");
                                option.value = product.uom_id;
                                option.text = product.uom.uom_name;
                                $(option).attr("data-relation","");
                                $(option).attr("data-uomqty","1");
                                $(option).attr("data-price",product.product_price);
                                $(option).attr("data-perprice",product.product_price);
                                $(option).attr("data-productuom", product.uom.uom_name);
                                $(option).attr("data-productid", product.id);
                                if(product.pivot.uom_id == product.uom_id) {
                                    option.selected = "selected";
                                }
                                t3.append(option);

                                var relation_val = "";

                                $.each(product.selling_uoms, function( key, suom ) {
                                    var option = document.createElement("option");
                                    option.value = suom.pivot.uom_id;
                                    option.text = suom.uom_name;
                                    var uom_relation = "1 "+suom.uom_name+" = "+ suom.pivot.relation +" "+ product.uom.uom_name;
                                    if(product.pivot.uom_id == suom.pivot.uom_id) {
                                        option.selected = "selected";
                                        relation_val = "1 "+suom.uom_name+" = "+ suom.pivot.relation +" "+product.uom.uom_name;
                                    }
                                    $(option).attr("data-relation",uom_relation);
                                    $(option).attr("data-uomqty",suom.pivot.relation);
                                    $(option).attr("data-price",suom.pivot.product_price);
                                    $(option).attr("data-perprice",suom.pivot.per_warehouse_uom_price);
                                    $(option).attr("data-productuom",product.uom.uom_name);
                                    $(option).attr("data-productid", product.id);
                                    t3.append(option);
                                });

                             cell3.appendChild(t3);


                            var cell4=row.insertCell(3);
                            var t4=document.createElement("input");
                                t4.type = "text";
                                if(product.pivot.uom_id != product.uom_id) {
                                    t4.value = relation_val;
                                } else {
                                    t4.value = "";
                                }
                                t4.name = "relation[]";
                                t4.style = "min-width:200px;";
                                t4.className ="form-control txt_relation";
                                $(t4).attr('readonly', true);
                                cell4.appendChild(t4);

                            

                            $(".txt_product").select2();

                            $(".txt_uom").select2();

                            $("#customer_id").select2();
                            $("#customer_id").on("select2:select", function(e) {

                                var data = e.params.data;
                                app.form.customer_id = data.id;
                            });

                            $(".txt_product").on("select2:select", function(e) {

                                var data = e.params.data;

                                app.selling_uoms = [];

                               var uom = e.target.options[e.target.options.selectedIndex].dataset.uom;
                               var uom_id = e.target.options[e.target.options.selectedIndex].dataset.uomid;
                               var price = e.target.options[e.target.options.selectedIndex].dataset.price;

                                //$(this).closest('td').next().find('.txt_uom').val(uom);
                                //$(this).closest('td').next().find('.txt_uom').attr('data-id',uom_id);

                                $(this).closest('td').next().next().find('.txt_uom').attr('data-uom',uom);

                                //auto add new product row
                                if($(this).closest('tr').next().hasClass("total_row")) {
                                    app.addProduct();
                                }

                                //add Warehouse UOM Selling Price
                                $(this).closest('td').next().next().next().next().next().find('input').val(price);

                                var selectbox_id = $(this).closest('td').next().next().find('.txt_uom');

                                selectbox_id.find('option').remove();

                                //add pre-defined product uom 
                                if(selectbox_id.find('option[value="'+uom_id+'"]').text() == "") {
                                    selectbox_id.append('<option value="">Select One</option><option value="'+uom_id+'" data-relation="" data-uomqty="1" data-productuom = "'+uom+'" data-productid="'+data.id+'" data-price="'+price+'" data-perprice="'+price+'">'+uom+'</option>'); 
                                }
                                $(".txt_uom").select2();

                                app.getSellingUomByProduct(selectbox_id, data.id);
                            });

                            $(".txt_uom").select2();

                            $(".txt_uom").on("select2:select", function(e) {

                                app.checkQty(e.target.options[e.target.options.selectedIndex]);

                                var uom_relation = e.target.options[e.target.options.selectedIndex].dataset.relation;

                                
                                //for van sale
                                //var uom_price = e.target.options[e.target.options.selectedIndex].dataset.price;
                               
                                //for office sale
                                var uom_price = e.target.options[e.target.options.selectedIndex].dataset.perprice;

                                $(this).closest('td').next().find('.txt_relation').val(uom_relation);
                                $(this).closest('td').next().next().next().find('input').val(uom_price);

                                app.calTotalAmount($(this).closest('td').next().next().next().find('input'));
                            });

                            var cell5=row.insertCell(4);
                                cell5.className = "text-center";
                            var t5=document.createElement("input");
                                t5.type = "checkbox";
                                t5.name = "chk_foc[]";
                                if(product.pivot.is_foc == '1') {
                                    $(t5). prop("checked", true);
                                }

                                if(app.order_status != 'Draft' && app.order_status != '') {
                                    $(t5).attr('disabled','disabled');
                                }
                                t5.addEventListener('change', function(){ app.checkFoc(t5); });
                                cell5.appendChild(t5);

                            var cell6=row.insertCell(5);
                            var t6=document.createElement("input");
                                t6.name = "unit_price[]";
                                t6.style = "width:100px;";
                                if(product.pivot.price != 0 && product.pivot.price != null) {
                                    t6.value = product.pivot.price;
                                }
                                t6.className ="form-control float_num";
                                $(t6).attr("required", true);
                                if(product.pivot.is_foc == '1') {
                                    $(t6).attr("readonly", true);
                                }
                                if(app.order_status != 'Draft' && app.order_status != '') {
                                    $(t6).attr('readonly', true);
                                }
                                t6.addEventListener('blur', function(){ app.calTotalAmount(t6); });
                                cell6.appendChild(t6);

                            var cell7=row.insertCell(6);
                            var t7=document.createElement("input");
                                t7.name = "total_amount[]";
                                t7.style = "width:100px;";
                                if(product.pivot.total_amount != 0 && product.pivot.total_amount != null) {
                                    t7.value = product.pivot.total_amount;
                                    subTotal += parseInt(product.pivot.total_amount);
                                }
                                t7.className ="form-control num_txt";
                                $(t7).attr("required", true);
                                $(t7).attr("readonly", true);
                               // t2.addEventListener('blur', function(){ app.checkQty(t2); });
                                cell7.appendChild(t7);

                            var cell8=row.insertCell(7);
                            cell8.className = "text-center";
                            if(app.user_role != 'admin' && (app.order_status == 'Draft' || app.order_status == ''))
                            {
                                var row_action = "<a class='remove-row red-icon' title='Remove'><i class='fas fa-times-circle' style='font-size: 25px;'></i></a>";
                            }
                            $(cell8).append(row_action);
                        }
                    });

                    app.form.sub_total  = subTotal;

                })
                .catch(function(error) {
                  // handle error
                  console.log(error);
                })
                .then(function() {
                  // always executed
                });

                $(".txt_uom").select2();
            }, 

            checkQty(obj) { 
                let app = this;
                if(typeof obj.name !== 'undefined') {
                    //For quantity box onBlur Event

                    var product_id = $(obj).closest('td').prev().find(':selected').val();
                    var transfer_qty = obj.value;
                    var uom = $(obj).closest('td').next().find(':selected').val();   

                    var product_qty = 0; 
                    var uom_val = "";
                    var uom_name  = "";  
                    var p_uom_val = "";
                    var p_uom_name = "";
                    var p_qty = 0;            

                    if(product_id != "" && transfer_qty != "" && uom != "") {
                        //calculate same products quantity in product list
                        $(".txt_product").each(function() {
                            if(product_id == $(this).val()) {
                                p_uom_val  = $(this).closest('td').next().next().find(':selected').attr('data-uomqty');
                                p_uom_name = $(this).closest('td').next().next().find(':selected').attr('data-productuom');
                                p_qty =  $(this).closest('td').next().find('input').val();
                                if(typeof p_uom_val !== "undefined" && typeof p_uom_name != "undefined" != "" && p_qty != "") {

                                    product_qty = parseInt(product_qty) + (parseInt(p_qty) * parseInt(p_uom_val));
                                }
                            }
                        });

                        //uom_val  = $(obj).closest('td').next().find(':selected').attr('data-uomqty');
                        uom_name = $(obj).closest('td').next().find(':selected').attr('data-productuom');

                        //product_qty = parseInt(product_qty) + (parseInt(transfer_qty) * parseInt(uom_val));

                        var order_qty = app.getSO(product_id);                    

                        var key = this.products.findIndex(x => x.product_id == product_id);
                        //var available_qty = parseInt(this.products[key].in_count) - parseInt(this.products[key].out_count);
                        console.log(this.products);
                        //calculate reserve qty
                        var available_qty = parseInt(this.products[key].in_count)-(parseInt(this.products[key].direct_sale_qty) + parseInt(order_qty) + parseInt(this.products[key].transfer_qty) + parseInt(this.products[key].revise_sale_qty));

                        var min_qty = this.products[key].minimum_qty == null ? 0 : this.products[key].minimum_qty;
                        var percentage_qty = this.products[key].percentage_qty == null ? 0 : this.products[key].percentage_qty;
                        available_qty = parseInt(available_qty) - parseInt(min_qty);
                        console.log(min_qty+','+this.products[key].in_count+', '+this.products[key].direct_sale_qty+','+order_qty);
                        console.log(product_qty);

                        if(product_qty > available_qty) {
                            if(percentage_qty != 0) {
                                available_qty = (available_qty/parseInt(percentage_qty)) * 100;
                                available_qty = Math.round(available_qty)+"%";
                            }
                            
                            swal("Warning!", "Not enough quantity! Availabel quantity is "+available_qty+" "+uom_name+".", "warning");

                            $(obj).focus(); obj.value="";
                        }
                    }
                } else {
                    //For UOM box select Event

                    var product_qty = 0; 
                    var uom_val = "";
                    var uom_name  = "";  
                    var p_uom_val = "";
                    var p_uom_name = "";
                    var p_qty = 0;  

                    var product_id = $(obj).attr('data-productid');
                    var transfer_qty = $(obj).closest('td').prev().find('input').val();
                    var uom = obj.value;                    

                    if(product_id != "" && transfer_qty != "" && uom != "") {

                        //calculate same products quantity in product list
                        $(".txt_product").each(function() {
                            if(product_id == $(this).val()) {
                                p_uom_val  = $(this).closest('td').next().next().find(':selected').attr('data-uomqty');
                                p_uom_name = $(this).closest('td').next().next().find(':selected').attr('data-productuom');
                                p_qty =  $(this).closest('td').next().find('input').val();
                                if(typeof p_uom_val !== "undefined" && typeof p_uom_name != "undefined" != "" && p_qty != "") {

                                    product_qty = parseInt(product_qty) + (parseInt(p_qty) * parseInt(p_uom_val));
                                }
                            }
                        });

                        //var uom_val  = $(obj).attr('data-uomqty');
                        var uom_name = $(obj).attr('data-productuom');

                        //var product_qty = parseInt(transfer_qty) * parseInt(uom_val);

                        var order_qty = app.getSO(product_id);                    

                        var key = this.products.findIndex(x => x.product_id == product_id);
                        var available_qty = parseInt(this.products[key].in_count)-(parseInt(this.products[key].direct_sale_qty) + parseInt(order_qty) + parseInt(this.products[key].transfer_qty) + parseInt(this.products[key].revise_sale_qty));

                        var min_qty = this.products[key].minimum_qty == null ? 0 : this.products[key].minimum_qty;
                        var percentage_qty = this.products[key].percentage_qty == null ? 0 : this.products[key].percentage_qty;
                        available_qty = (parseInt(available_qty) - parseInt(order_qty)) - parseInt(min_qty);
                        if(product_qty > available_qty) {

                            if(percentage_qty != 0) {
                                available_qty = (available_qty/parseInt(percentage_qty)) * 100;
                                available_qty = Math.round(available_qty)+"%";
                            }

                            swal("Warning!", "Not enough quantity! Availabel quantity is "+available_qty+" "+uom_name+".", "warning");

                            $(obj).closest('td').prev().find('input').focus();  $(obj).closest('td').prev().find('input').val('');
                        }
                    }
                } 

                //claculate total amount
                var unit_price = $(obj).closest('td').next().next().next().next().find('input').val();
                var relation_val = $(obj).closest('td').next().find(':selected').attr('data-uomqty');
                if(obj.value != "" && unit_price != "") {
                    var total_amount = parseInt(obj.value) * parseInt(relation_val) * parseFloat(unit_price);
                    $(obj).closest('td').next().next().next().next().next().find('input').val(Math.round(total_amount));
                }

                //get all sub total amount
                var sub_total = 0;
                for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                    if(document.getElementsByName('total_amount[]')[i].value != "") {
                        sub_total += parseFloat(document.getElementsByName('total_amount[]')[i].value);
                    }
                }

                app.form.sub_total = Math.round(sub_total);
            },   

            getSO(product_id) {
                let app = this;
                //var d=moment("28-02-1999","YYYY-MM-DD");
                //d.isSame("28-02-1999");
                var qty = 0;
                var key = app.order_products.findIndex(x => x.product_id == product_id); 
                if(key != -1) {
                    qty = app.order_products[key].order_qty;
                }

                return qty;
            },        

            calTotalAmount(obj) {
               let app = this;
               var price = $(obj).val();
               var qty = $(obj).closest('td').prev().prev().prev().prev().find('input').val();

               //check price variant
              /* var product_id = $(obj).closest('td').prev().prev().prev().prev().prev().find(':selected').val();
               var key = app.products.findIndex(x => x.product_id == product_id);               
               var product_price = app.products[key].product_price; */

                //Van Sale
                //var product_price = $(obj).closest('td').prev().prev().prev().find(':selected').attr('data-price');

                //office Sale
                var product_price = $(obj).closest('td').prev().prev().prev().find(':selected').attr('data-perprice');

               if(product_price != '') {
                    var price_variant =  parseFloat(price) - parseFloat(product_price);
                    price_variant = price_variant.toFixed(2);
                    const wrapper = document.createElement('div');
                    if(price_variant < 0) {
                        wrapper.innerHTML = "Product Price Variant is <font color='red'>"+price_variant+"</font>";
                        swal({
                          icon: 'info',
                          content: wrapper,
                          timer:1000
                        });
                    }

                    if(price_variant > 0) {
                        wrapper.innerHTML = "Product Price Variant is <font color='green'>"+price_variant+"</font>";
                        swal({
                          icon: 'info',
                          content: wrapper,
                          timer:1000
                        });
                    }
               }

               if(price != "" && qty != "") {
                    var relation_val = $(obj).closest('td').prev().prev().prev().find(':selected').attr('data-uomqty');
                    var total = parseInt(qty) * parseInt(relation_val) * parseFloat(price);
                    $(obj).closest('td').next().find('input').val(Math.round(total));
               }

               //get all sub total amount
               var sub_total = 0;
               for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                    if(document.getElementsByName('total_amount[]')[i].value != "") {
                        sub_total += parseFloat(document.getElementsByName('total_amount[]')[i].value);
                    }
               }

               app.form.sub_total = Math.round(sub_total);
            },


            onSubmit: function(event){

                let app = this;
                app.form.product = [];
                app.form.uom = [];
                app.form.qty = [];
                app.form.unit_price = [];
                app.form.total_amount = [];
                $("#loading").show();

                if (!this.isEdit) {

                    for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                        app.form.product.push(document.getElementsByName('product[]')[i].value);
                        app.form.uom.push(document.getElementsByName('uom[]')[i].value);
                        app.form.qty.push(document.getElementsByName('qty[]')[i].value);
                        app.form.unit_price.push(document.getElementsByName('unit_price[]')[i].value);
                        app.form.total_amount.push(document.getElementsByName('total_amount[]')[i].value);
                        if(document.getElementsByName('chk_foc[]')[i].checked == true) {
                            app.form.is_foc.push('1');
                        } else {
                            app.form.is_foc.push('0');
                        }

                        //for  price variant
                       /* var key = app.products.findIndex(x => x.product_id == document.getElementsByName('product[]')[i].value);
                        var product_price = app.products[key].product_price;*/

                        //van sale
                        //var product_price = document.getElementsByName('uom[]')[i].options[document.getElementsByName('uom[]')[i].options.selectedIndex].dataset.price;

                        //office sale
                        var product_price = document.getElementsByName('uom[]')[i].options[document.getElementsByName('uom[]')[i].options.selectedIndex].dataset.perprice;

                        var price_variant =  parseInt(document.getElementsByName('unit_price[]')[i].value) - parseInt(product_price);

                        app.form.price_variants.push(price_variant);
                        
                    }
                    //console.log(app.form.total_amount);
                    //console.log(app.form.is_foc); return false;

                    this.form
                      .post("/order")
                      .then(function(data) {
                        if(data.status == "success") {
                            //$("#loading").hide();
                            //reset form data
                            event.target.reset();
                            $(".txt_product").select2();
                            swal({
                                title: "Success!",
                                text: 'Sale Order is saved.',
                                icon: "success",
                                button: true
                            }).then(function() {
                               // $("#loading").show();
                                //location.reload();
                                //app.$router.push("/order/edit/" + data.order_id);
                                var baseurl = window.location.origin;
                                window.open(baseurl+'/generate_order/'+data.order_id);
                                location.reload();
                            });
                        } else {
                          $.notify("Error", {
                            autoHideDelay: 3000,
                            gap: 1,
                            className: "error"
                          });
                        }
                    })
                    .catch(function (response)  {
                      // console.log(response.errors);

                    });
                } else {
                    //Edit entry details

                    app.form.product_pivot = [];


                    for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                        app.form.product.push(document.getElementsByName('product[]')[i].value);
                        app.form.uom.push(document.getElementsByName('uom[]')[i].value);
                        app.form.qty.push(document.getElementsByName('qty[]')[i].value);
                        app.form.unit_price.push(document.getElementsByName('unit_price[]')[i].value);
                        app.form.total_amount.push(document.getElementsByName('total_amount[]')[i].value);
                        if(document.getElementsByName('chk_foc[]')[i].checked == true) {
                            app.form.is_foc.push('1');
                        } else {
                            app.form.is_foc.push('0');
                        }

                        app.form.product_pivot.push(document.getElementsByName('product[]')[i].options[document.getElementsByName('product[]')[i].options.selectedIndex].dataset.pivotid);

                        //for  price variant
                        /*var key = app.products.findIndex(x => x.product_id == document.getElementsByName('product[]')[i].value);
                        var product_price = app.products[key].product_price;*/

                        //van sale
                        //var product_price = document.getElementsByName('uom[]')[i].options[document.getElementsByName('uom[]')[i].options.selectedIndex].dataset.price;

                        //office sale
                        var product_price = document.getElementsByName('uom[]')[i].options[document.getElementsByName('uom[]')[i].options.selectedIndex].dataset.perprice;

                        var price_variant =  parseInt(document.getElementsByName('unit_price[]')[i].value) - parseInt(product_price);

                        app.form.price_variants.push(price_variant);
                    }
                    //console.log(app.form.ex_product_pivot);
                    //console.log(app.form.product_pivot);

                    //return false;

                    this.form
                      .patch("/order/" + app.order_id)
                      .then(function(data) {
                        if(data.status == "success") {
                            $("#loading").hide();
                            //reset form data
                            event.target.reset();
                            $(".txt_product").select2();

                            swal({
                                title: "Success!",
                                text: 'Sale Order is updated.',
                                icon: "success",
                                button: true
                            }).then(function() {
                                location.reload();

                            });
                        } else {
                          $.notify("Error", {
                            autoHideDelay: 3000,
                            gap: 1,
                            className: "error"
                          });
                        }
                    })
                    .catch(function (response)  {
                      // console.log(response.errors);

                    });
                }
            },

            checkFoc(obj) {
                let app = this;
                var is_foc = $(obj).prop("checked");
                if(is_foc) {

                    $(obj).closest('td').next().find('input').attr('readonly',true);

                    var t1=document.createElement("input");
                    t1.name = "unit_price[]";
                    t1.style = "width:100px;";
                    t1.className ="form-control float_num";
                    $(t1).attr("readonly", true);
                    t1.addEventListener('blur', function(){ app.calTotalAmount(t1); });
                    $(obj).closest('td').next().html(t1);

                    var t2=document.createElement("input");
                    t2.name = "total_amount[]";
                    t2.style = "width:100px;";
                    t2.className ="form-control num_txt";
                    $(t2).attr("readonly", true);
                    $(obj).closest('td').next().next().html(t2);

                    $(obj).closest('td').next().find('input').attr('required',false);
                } else {
                    $(obj).closest('td').next().find('input').attr('readonly',false);
                    $(obj).closest('td').next().find('input').attr('required',true);

                    //Van Sale
                    //var product_price = $(obj).closest('td').prev().prev().find(':selected').attr('data-price');

                    //office Sale
                    var product_price = $(obj).closest('td').prev().prev().find(':selected').attr('data-perprice');

                   $(obj).closest('td').next().find('input').val(product_price);
                }

                app.calTotalAmount($(obj).closest('td').next().find('input'));
            },

            removeProduct(id) {
                let app = this;
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true
                    }).then(willDelete => {
                    if (willDelete) {
                        $("#"+id).remove();
                        var sub_total = 0; 
                        for(var i=0; i<document.getElementsByName('product[]').length; i++) {
                            if(document.getElementsByName('total_amount[]')[i].value != "") {
                                sub_total += parseInt(document.getElementsByName('total_amount[]')[i].value);
                            }
                        }

                        app.form.sub_total = sub_total;
                    } else {
                      //
                    }
                });
            },

        },
    }
</script>