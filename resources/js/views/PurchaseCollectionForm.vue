<template>
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#!"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/'">Home</a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/office'">Office Sale</a></li>
                <li class="breadcrumb-item"><router-link tag="span" to="/collection" class="font-weight-normal"><a href="#">Collection</a></router-link></li>
                <li class="breadcrumb-item active" aria-current="page">Collection Form</li>

            </ol>
        </nav>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">Collection Form</h4>
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
                                <label>Collection No.</label>
                                <input type="text" class="form-control" id="collection_no" name="collection_no" v-model="form.collection_no" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <!--<div class="form-group col-md-4">
                                <label for="branch_name">Branch</label>
                                 <input type="text" class="form-control" id="branch_name" name="branch_name"
                                v-model="user_branch" readonly>
                            </div>-->
                            <div class="form-group col-md-4">
                                <label for="branch_id">Branch</label>
                                <select id="branch_id" class="form-control mm-txt"
                                        name="branch_id" v-model="form.branch_id" style="width:100%" :disabled="cusReadonly" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="branch in branches" :value="branch.id"  >{{branch.branch_name}}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="collection_date">Date</label>
                                <input type="text" class="form-control datetimepicker" id="collection_date" name="collection_date"
                                       v-model="form.collection_date" required>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="form-group col-md-4">
                                <label for="supplier_id">Supplier</label>
                                <select id="supplier_id" class="form-control"
                                        name="supplier_id" v-model="form.supplier_id" style="width:100%" :disabled="cusReadonly" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="sup in suppliers" :value="sup.id"  >{{sup.name}}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Invoice</label>
                                <select multiple class="form-control invoices"
                                        name="invoices[]" v-model="form.invoices" :required="isRequired" style="width:100%">
                                    <option v-for="p in purchase_invoices" :value="p.id">{{p.invoice_no}}-{{p.total_amount-(p.discount+p.collection_amount+p.pay_amount)}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3" >
                            <div class="col-md-4 custom-control custom-switch" style="padding-left:10px;">
                                <label style='margin-right:50px;' class="ml-0">Auto Payment</label>
                                <input type="checkbox" class="custom-control-input" id="is_auto" name="is_auto" checked @change="checkAuto($event.target)">
                                <label class="custom-control-label" for="is_auto"></label>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="pay_amount">Pay Amount</label>
                                <input type="text" class="form-control" id="pay_amount" name="pay_amount" autocomplete="off" v-model="form.pay_amount" @input="calcAutoPay()" :readonly="!form.is_auto" :required="form.is_auto">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <span class="d-none d-sm-inline-block btn-sm btn-primary shadow-sm bg-blue"><i class="fas fa-list text-white"></i> Invoice Details</span>
                            </div>
                        </div>

                        <div class="row mt-4" v-if="!isEdit">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered table_no" id="product_table">
                                    <thead class="thead-grey">
                                    <tr>
                                        <th scope="col" >No.</th>
                                        <th scope="col" >Invoice Date</th>
                                        <th scope="col" >Invoice No.</th>
                                        <th scope="col" >Invoice Amount</th>
                                        <th scope="col" >Previous Paid Amount</th>
                                        <th scope="col" >Pay Amount</th>
                                        <th scope="col" >Discount</th>
                                        <th scope="col" >Balance</th>
                                        <th scope="col"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="p in purchase_invoices">
                                        <tr :id="p.id" style="display:none;" data-pivotid="0">
                                            <td class="text-right"></td>
                                            <td>{{p.invoice_date}}</td>
                                            <td>{{p.invoice_no}}</td>
                                            <td>
                                                <input type="text" :id="'inv_amt'+p.id" class="form-control num_txt inv_amt" readonly :value="p.total_amount" />
                                            </td>
                                            <td>
                                                <input type="text" :id="'prev_amt'+p.id" class="form-control num_txt prev_amt" readonly :value="parseInt(p.pay_amount)+parseInt(p.collection_amount)" />
                                            </td>
                                            <td>
                                                <input type="text" :id="'pay_amt'+p.id" class="form-control num_txt pay_amt" :readonly="isReadonly" @input="calcPay(p.id)"/>
                                            </td>
                                            <td>
                                                <input type="text" :id="'discount_amt'+p.id" class="form-control num_txt discount_amt" @input="payDiscount(p.id)" />
                                            </td>
                                            <td>
                                                <input type="text" :id="'balance'+p.id" class="form-control num_txt balance_amt"  :data-id="p.id" readonly />
                                            </td>
                                            <td class="text-center">
                                                <a class='remove-row red-icon' title='Remove' v-if="user_role != 'admin'"><i class='fas fa-times-circle' style='font-size: 25px;'></i></a>
                                            </td>
                                        </tr>

                                    </template>
                                    <tr>
                                        <td colspan='5' class="text-right"> Total Amount</td>
                                        <td>{{total_pay}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>


                        <div class="row mt-4" v-else >
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered table_no_edit" id="product_table">
                                    <thead class="thead-grey">
                                    <tr>
                                        <th scope="col" >No.</th>
                                        <th scope="col" >Invoice Date</th>
                                        <th scope="col" >Invoice No.</th>
                                        <th scope="col" >Invoice Amount</th>
                                        <th scope="col" >Previous Paid Amount</th>
                                        <th scope="col" >Pay Amount</th>
                                        <th scope="col" >Discount</th>
                                        <th scope="col" >Balance</th>
                                        <th scope="col"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="edit_invoices">


                                    </tbody>
                                    <tr>
                                        <td colspan='5' class="text-right"> Total Amount</td>
                                        <td>{{total_pay}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
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
                collection_no: "",
                collection_date: "",
                supplier_id: "",
                is_auto: true,
                invoices: [],
                payments: [],
                discounts: [],
                pay_amount: '',
                total_pay: '',
                remove_pivot_id: [],
                branch_id: '',
            }),
            isEdit: false,
            isReadonly: true,
            isRequired: true,
            suppliers: [],
            selected_invoices: [],
            purchase_invoices: [],
            user_role: '',
            user_year: '',
            total_pay: 0,
            collection_id: '',
            cusReadonly: false,
            isDisabled: false,
            user_branch: '',
            branches: [],
            site_path: '',
            storage_path: '',
        };
    },

    created() {

        this.user_role = document.querySelector("meta[name='user-role']").getAttribute('content');
        //this.user_branch = document.querySelector("meta[name='user-branch']").getAttribute('content');
        this.site_path = document.querySelector("meta[name='site-path']").getAttribute('content');
        //this.site_path = this.site_path.substring(this.site_path.lastIndexOf('/')+1);
        this.storage_path = document.querySelector("meta[name='storage-path']").getAttribute('content');
        //for save entry button
        /*if(this.user_role == "admin" && !this.isEdit) {
            this.isDisabled = true;
        }*/

        this.user_year = document.querySelector("meta[name='user-year-likelink']").getAttribute('content');

        /*if(this.user_role == "office_order_user")*/
        if(this.user_role != "admin" && this.user_role != "system")
        {
            var url =  window.location.origin;
            window.location.replace(url);
        }

        if(this.$route.params.id) {
            this.isEdit = true;
            this.collection_id = this.$route.params.id;
            this.getCollection(this.collection_id);
            this.cusReadonly = true;
        }
    },
    mounted() {
        $("#loading").hide();
        let app = this;
        app.initSuppliers();
        app.initBranches();
        $("#branch_id").on("select2:select", function(e) {
            app.selected_invoices = [];
            var data = e.params.data;
            app.form.branch_id = data.id;
            app.purchase_invoices = [];
            $('.invoices').val(app.selected_invoices).trigger('change');
            if(app.form.supplier_id != '') {
                var search ="&branch_id=" + app.form.branch_id;
                axios.get("/purchase_collection/supplier_credit_purchase/"+app.form.supplier_id+"?"+search).then(({ data }) => (app.purchase_invoices = data.data));
                $(".invoices").select2();
            }
        });
        $("#supplier_id").select2();
        $("#supplier_id").on("select2:select", function(e) {
            app.selected_invoices = [];
            var data = e.params.data;
            app.form.supplier_id = data.id;

            app.purchase_invoices = [];
            $('.invoices').val(app.selected_invoices).trigger('change');
            var search ="&branch_id=" + app.form.branch_id;
            axios.get("/purchase_collection/supplier_credit_purchase/"+data.id+"?"+search).then(({ data }) => (app.purchase_invoices = data.data));
            $(".invoices").select2();
        });
        $(".invoices").select2();
        $(".invoices").on("select2:select", function(e) {
            var data = e.params.data;
            data.text=data.text.slice(0,11);
            app.isRequired = false;
            app.selected_invoices.push(data.id);
            var unique_invoices = app.selected_invoices.filter((a, b) => app.selected_invoices.indexOf(a) === b);
            // console.log(unique_invoices);
            app.selected_invoices = unique_invoices;
            $('.invoices').val(app.selected_invoices).trigger('change');
            $("#"+data.id).show();
            if(app.form.is_auto) {
                $('.pay_amt').attr('required',false);
            } else {
                $('.pay_amt:visible').attr('required',true);
            }

            if($("#"+data.id).attr('data-pivotid') != 0) {
                var pindex = app.form.remove_pivot_id.indexOf($("#"+data.id).attr('data-pivotid'));
                console.log(pindex);
                if (pindex > -1) {
                    app.form.remove_pivot_id.splice(pindex, 1);
                }
            }
            app.calcPay(data.id);

            app.calcAutoPay();

            app.calcTotalPay();
        });

        $(".invoices").on("select2:unselect", function(e) {
            var data = e.params.data;
            var unique_invoices = app.selected_invoices.filter((a, b) => app.selected_invoices.indexOf(a) === b);
            app.selected_invoices = unique_invoices;
            const index = app.selected_invoices.indexOf(data.id);
            if (index > -1) {
                app.selected_invoices.splice(index, 1);
            }
            $('.invoices').val(app.selected_invoices).trigger('change');
            if(app.selected_invoices.length > 0) {
                app.isRequired = false;
            } else {
                app.isRequired = true;
            }

            $("#pay_amt"+data.id).attr('required', false);

            $("#"+data.id).hide();

            if($("#"+data.id).attr('data-pivotid') != 0) {
                app.form.remove_pivot_id.push($("#"+data.id).attr('data-pivotid'));
            }

            app.calcAutoPay();

            app.calcTotalPay();

        });

        $(document).on('click','.remove-row',function(evt) {
            var unique_invoices = app.selected_invoices.filter((a, b) => app.selected_invoices.indexOf(a) === b);
            app.selected_invoices = unique_invoices;

            var unselect_id =  $(this).parents("tr").attr('id');
            $("#pay_amt"+unselect_id).attr('required', false);
            $(this).parents("tr").hide();

            var unselect_option = $('.invoices option[value="'+ unselect_id +'"]');
            unselect_option.prop('selected', false);
            const index = app.selected_invoices.indexOf(unselect_id);
            if (index > -1) {
                app.selected_invoices.splice(index, 1);
            }

            if(app.selected_invoices.length > 0) {
                app.isRequired = false;
            } else {
                app.isRequired = true;
            }

            $('.invoices').val(app.selected_invoices).trigger('change');
            //$('.invoices').trigger('change.select2');

            if($("#"+unselect_id).attr('data-pivotid') != 0) {
                app.form.remove_pivot_id.push($("#"+unselect_id).attr('data-pivotid'));
            }

            app.calcAutoPay();

            app.calcTotalPay();
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
                minDate: app.user_year+"-01-01",
                maxDate: app.user_year+"-12-31",
            })
            .on("dp.show", function(e) {
                var y = new Date().getFullYear();
                if(app.user_year < y) {
                    if(app.form.collection_date == app.user_year+"-12-31" ||  app.form.collection_date == '') {
                        app.form.collection_date = app.user_year+"-12-31";
                    }
                }
            })
            .on("dp.change", function(e) {
                var formatedValue = e.date.format("YYYY-MM-DD");
                //console.log(formatedValue);
                app.form.collection_date = formatedValue;
            });

        $(document).on('input',  '.pay-change',function () {
            var sale_id = $(this).attr('data-id');
            app.calcPay(sale_id);
        });

        $(document).on('keyup',  '.discount-change',function () {
            var purchase_id = $(this).attr('data-id');
            app.payDiscount(purchase_id);
        });

    },

    methods: {
        initBranches() {
            axios.get("/branches_byuser").then(({ data }) => (this.branches = data.data));
            $("#branch_id").select2();
        },
        initSuppliers() {
            axios.get("/supplier").then(({ data }) => (this.suppliers = data.data));
            $("#supplier_id").select2();
        },

        checkAuto(obj) {
            let app = this;
            var is_auto = $(obj).prop("checked");
            if(app.selected_invoices.length > 0) {
                app.isRequired = false;
            }else{
                app.isRequired = true;
            }
            if(is_auto){
                app.form.is_auto = true;
                app.isReadonly = true;
                $('.pay_amt').attr('required',false);
                if(app.isEdit) {
                    $('.pay_amt:visible').attr('readonly',true);
                }
            } else {
                $('.pay_amt:visible').attr('required',true);
                app.form.pay_amount = "";
                app.form.is_auto = false;
                app.isReadonly = false;

                if(app.isEdit) {
                    $('.pay_amt:visible').attr('readonly',false);
                    $('.pay_amt:visible').attr('required',true);
                }
            }
        },

        calcAutoPay() {
            let app = this;
            if(app.form.is_auto) {
                if((app.purchase_invoices.length > 0 && app.selected_invoices.length > 0) || app.isEdit) {
                    var payment = $("#pay_amount").val();
                    var total_balance = 0;
                    // this.calcPay(p_id);

                    $(".balance_amt:visible").each(function() {
                        var bal_purchase_id = $(this).attr('data-id');

                        total_balance += parseInt($('#inv_amt'+bal_purchase_id).val()) - (parseInt($('#prev_amt'+bal_purchase_id).val()));
                        //total_balance = parseInt(total_balance) + parseInt($(this).val());
                        // console.log('Total Balance is' +total_balance);
                    });
                    console.log('Payment is '+ payment);
                    if(parseInt(payment) >  parseInt(total_balance)) {
                        swal("Warning!", "Your payment is more than total balance."+ total_balance, "warning");
                        console.log('T is' +total_balance);
                        this.form.pay_amount='';
                        // $('#pay_amount').val('');
                        // document.getElementById('pay_amount').value = '';
                        // $('#pay_amount').focus();
                        // console.log('Pay is '+$('#pay_amount').val());
                        // return false;
                    } else {
                        // alert('a');
                        var purchase_id = '';
                        var balance = 0;
                        //auto  payment
                        $(".balance_amt:visible").each(function() {

                            purchase_id = $(this).attr('data-id');
                            // alert($('#discount_amt'+purchase_id).val());
                            var dsc=$('#discount_amt'+purchase_id).val()
                            if(dsc==''){
                                dsc=0;
                            }
                            balance = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val())+parseInt(dsc));
                            console.log('Balances is '+ balance);
                            if(payment == 0)  {
                                var dsc=$('#discount_amt'+purchase_id).val()
                                if(dsc==''){
                                    dsc=0;
                                }
                                $('#pay_amt'+purchase_id).val('');
                                payment = 0;

                                var invoice_bal = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt(dsc)+parseInt(payment));
                                $(this).val(invoice_bal);
                            } else {
                                if(parseInt(payment) <= parseInt(balance)) {
                                    console.log('a');
                                    var dsc=$('#discount_amt'+purchase_id).val();
                                    if(dsc==''){
                                        dsc=0;
                                    }
                                    $('#pay_amt'+purchase_id).val(payment);
                                    var invoice_bal = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt(payment)+parseInt(dsc));
                                    payment = 0;

                                    $(this).val(invoice_bal);
                                } else {
                                    console.log('b');
                                    payment = parseInt(payment) - parseInt(balance);
                                    $('#pay_amt'+purchase_id).val(balance);
                                    var invoice_bal = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt(balance));
                                    console.log('Invoice VAl is '+invoice_bal);
                                    $(this).val(invoice_bal);

                                }
                            }
                        });
                    }


                } else {
                    /*swal("Warning!", "There is no invoice to pay.", "warning");
                    $('#pay_amount').val('');
                    $('#pay_amount').focus();
                    return false;*/
                }
            }
            // app.calcPay(data.id);
            app.calcTotalPay();
        },

        payDiscount(purchase_id) {
            //var balance  = parseInt($("#balance"+sale_id).val());
            if($("#discount_amt"+purchase_id).val() != "") {
                var discount = parseInt($("#discount_amt"+purchase_id).val());
            } else {
                var discount = 0;
            }
            // console.log('DSC is'+discount);
            var balance = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt($('#pay_amt'+purchase_id).val()));
            if(discount != "") {
                // if(discount > balance){
                //     alert(balance);
                // }else{
                //     alert(balance);
                // }
                if(discount > balance) {
                    swal("Warning!", "Discount amount is greater than balance.", "warning");
                    $("#discount_amt"+purchase_id).val('');
                    // $("#balance"+purchase_id).val(parseInt($('#inv_amt'+purchase_id).val()));
                    $("#balance"+purchase_id).val('');
                    $("#discount_amt"+purchase_id).focus();
                } else {
                    balance = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt($('#pay_amt'+purchase_id).val()) + parseInt(discount));
                    $("#balance"+purchase_id).val(balance);
                }
            }else{
                balance = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt($('#pay_amt'+purchase_id).val()) + parseInt(discount));
                $("#balance"+purchase_id).val(balance);            }
        },
        calcPay(purchase_id) {

            let app = this;

            var discount = $("#discount_amt"+purchase_id).val();
            if(discount == "") {
                discount = 0;
            }
            var pay = $('#pay_amt'+purchase_id).val();
            if(pay == "") {
                pay = 0;
            }
            var purchase_bal = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt($('#prev_amt'+purchase_id).val()) + parseInt(discount));
            if(parseInt(pay) > parseInt(purchase_bal)) {
                // alert('aaa');
                swal("Warning!", "Payment is greater than balance.", "warning");
                document.getElementById('pay_amt'+purchase_id).value = '';

            } else {
                var prev_pay = $('#prev_amt'+purchase_id).val();
                if(prev_pay == "") {
                    prev_pay = 0;
                }
                var balance = parseInt($('#inv_amt'+purchase_id).val()) - (parseInt(prev_pay) + parseInt(pay) + parseInt(discount));

                $("#balance"+purchase_id).val(balance);

                app.calcTotalPay();
            }
        },
        calcTotalPay() {
            let app = this;
            app.total_pay = 0;
            var total = 0;
            $(".pay_amt:visible").each(function() {
                if($(this).val() != "")
                    total = parseInt(total) + parseInt($(this).val());
            });

            app.total_pay = total;
        },

        getCollection(id) {
            let app = this;
            axios
                .get("/purchase_collection/edit/" + id)
                .then(function(response) {
                    //for save button permission
                    if(app.user_role == "admin" || app.user_role == "system") {
                        app.isDisabled = false;
                    } else {
                        app.isDisabled = true;
                    }

                    //app.sale_invoices = response.data.cus_invoices;
                    app.form.collection_no      = response.data.collection.collection_no;
                    app.form.collection_date    = response.data.collection.collection_date;
                    app.form.supplier_id        = response.data.collection.supplier_id;
                    $('#supplier_id').val(app.form.supplier_id).trigger('change');

                    if(response.data.collection.branch != null) {
                        app.form.branch_id = response.data.collection.branch.id;
                    } else {
                        app.form.branch_id = '';
                    }
                    $('#branch_id').val(app.form.branch_id).trigger('change');
                    app.total_pay = response.data.collection.total_paid_amount;
                    if(response.data.collection.auto_payment == 1) {
                        app.form.is_auto = true;
                        $("#is_auto").prop("checked", true);
                        app.form.pay_amount     = response.data.collection.total_paid_amount;

                    } else {
                        app.form.is_auto = false;
                        $("#is_auto").prop("checked", false);
                    }

                    var s2 = $(".invoices").select2({
                        tags: true
                    });
                    var purchases_arr = [];
                    $.each(response.data.collection.purchases, function( key, value ) {
                        purchases_arr.push(value.id);
                    });
                    var index = '';
                    var html = "";
                    $.each(response.data.sup_invoices, function( key, value ) {
                        index = purchases_arr.indexOf(value.id);
                        if (index > -1) {
                            app.selected_invoices.push(String(value.id));
                            html += '<tr id="'+value.id+'" data-pivotid = "'+response.data.collection.purchases[index].pivot.id+'">';
                        } else {
                            html += '<tr id="'+value.id+'" style="display:none;" data-pivotid="0">';
                        }

                        //invoice lists
                        html += '<td class="text-right"></td><td>'+value.invoice_date+'</td><td>'+value.invoice_no+'</td>';

                        html += '<td><input type="text" id="inv_amt'+value.id+'" class="form-control num_txt inv_amt" readonly value="'+value.total_amount+'" /></td>';

                        //console.log(response.data.collection.sales);
                        var key = response.data.collection.purchases.findIndex(x => x.id == value.id);
                        if(key > -1) {
                            var paid = parseInt(response.data.collection.purchases[key].pivot.paid_amount);
                            // console.log('Paid is'+paid);
                            if(response.data.collection.purchases[key].pivot.discount != null) {
                                var discount = parseInt(response.data.collection.purchases[key].pivot.discount);
                            } else {
                                var discount = 0;
                            }
                            // console.log('Discount is '+discount);
                            // console.log('Pay amount is '+value.pay_amount);
                            // console.log('Pay amount is '+value.collection_amount);
                        } else {
                            var paid = 0;
                            var discount = 0;
                        }

                        var prev_pay = (parseInt(value.pay_amount)+parseInt(value.collection_amount)) - (parseInt(paid) + parseInt(discount));
                        var bal = (parseInt(value.total_amount) - parseInt(prev_pay)) - (parseInt(paid) + parseInt(discount));

                        html += '<td><input type="text" id="prev_amt'+value.id+'" class="form-control num_txt prev_amt" readonly value="'+prev_pay+'" /></td>';

                        if(response.data.collection.auto_payment == 1) {
                            html += '<td><input type="text" id="pay_amt'+value.id+'" class="form-control num_txt pay_amt pay-change" readonly value="'+paid+'" data-id= "'+value.id+'" /></td>';
                        } else {40
                            html += '<td><input type="text" id="pay_amt'+value.id+'" class="form-control num_txt pay_amt pay-change" value="'+paid+'" data-id= "'+value.id+'" required /></td>';
                        }
                        html += '<td><input type="text" id="discount_amt'+value.id+'" class="form-control num_txt discount_amt discount-change" value="'+discount+'" data-id= "'+value.id+'" /></td>';

                        html += '<td><input type="text" id="balance'+value.id+'" class="form-control num_txt balance_amt" value="'+bal+'" data-id= "'+value.id+'" readonly /></td>';

                        html += '<td class="text-center">';
                        if(app.user_role != 'admin') {
                            html += '<a class="remove-row red-icon" title="Remove"><i class="fas fa-times-circle" style="font-size: 25px;"></i></a>';
                        }
                        html += '</td></tr>';
                        if(!s2.find('option[value="'+value.id+'"]').length) {
                            console.log(s2.find('option[value="'+value.id+'"]').length);
                            s2.append($('<option value="'+value.id+'">').text(value.invoice_no));
                        }
                    });


                    s2.val(app.selected_invoices).trigger("change");

                    $("#edit_invoices").html(html);

                    if(app.selected_invoices.length > 0) {
                        app.isRequired = false;
                    } else {
                        app.isRequired = true;
                    }

                })
                .catch(function(error) {
                    // handle error
                    console.log(error);
                })
                .then(function() {
                    // always executed
                });
        },

        onSubmit: function(event) {
            let app = this;
            $("#loading").show();
            app.form.total_pay = app.total_pay;
            if (!this.isEdit) {
                app.form.invoices = app.selected_invoices.filter((a, b) => app.selected_invoices.indexOf(a) === b);
                for(var i=0; i<app.form.invoices.length; i++) {
                    app.form.payments.push($("#pay_amt"+app.form.invoices[i]).val());
                    app.form.discounts.push($("#discount_amt"+app.form.invoices[i]).val());
                }
                this.form
                    .post("/purchase_collection/store")
                    .then(function(data) {
                        console.log(data.data);
                        if(data.status == "success") {
                            $("#loading").hide();
                            swal({
                                title: "Success!",
                                text: 'Collection is saved.',
                                icon: "success",
                                button: true
                            }).then(function() {
                                // location.reload();
                                app.$router.push({name:'purchase_collection'})

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
            else {
                //Edit entry details

                app.form.total_pay = app.total_pay;

                app.form.invoices = app.selected_invoices.filter((a, b) => app.selected_invoices.indexOf(a) === b);
                for(var i=0; i<app.form.invoices.length; i++) {
                    app.form.payments.push($("#pay_amt"+app.form.invoices[i]).val());
                    app.form.discounts.push($("#discount_amt"+app.form.invoices[i]).val());
                }

                //console.log(app.form.invoices);

                this.form
                    .patch("/purchase_collection/update/" + app.collection_id)
                    .then(function(data) {
                        if(data.status == "success") {
                            //reset form data
                            $('#loading').hide();
                            swal({
                                title: "Success!",
                                text: 'Collection is updated.',
                                icon: "success",
                                button: true
                            }).then(function() {
                                app.$router.push({name:'purchase_collection'})
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

        removeProduct(id) {
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover!",
                icon: "warning",
                buttons: true,
                dangerMode: true
            }).then(willDelete => {
                if (willDelete) {
                    $("#"+id).remove();
                } else {
                    //
                }
            });
        },

    }
}
</script>

<style scoped>

</style>
