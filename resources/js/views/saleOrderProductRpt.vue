<template>
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#!"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/'">Home</a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/report'">Report</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sale Order Product Wise Report</li>

            </ol>
        </nav>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">Sale Order Product Wise Report</h4>
           <!-- <router-link to="/inventory/transfer/new" class="d-sm-inline-block btn btn-primary shadow-sm inventory">
                <i class="fas fa-plus"></i> Add New Transfer
            </router-link>-->
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Search By</h6>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="from_date">From Date</label>
                        <input type="text" class="form-control datetimepicker" id="from_date" name="from_date"
                        v-model="search.from_date">
                    </div>

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="to_date">To Date</label>
                        <input type="text" class="form-control datetimepicker" id="to_date" name="to_date"
                        v-model="search.to_date">
                    </div>

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="order_no">Order No.</label>
                        <input type="text" class="form-control" id="order_no" name="order_no" v-model="search.order_no">
                    </div>

                    <div class="form-group col-md-4 col-lg-3 mm-txt">
                        <label for="entry_date">Customer</label>
                        <select id="customer_id" class="form-control mm-txt"
                            name="customer_id" v-model="search.customer_id" style="width:100%" required
                        >
                            <option value="">Select One</option>
                            <option v-for="cus in customers" :value="cus.id"  >{{cus.cus_name}}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4 col-lg-3 mm-txt">
                        <label for="branch_id">Branch</label>
                        <select id="branch_id" class="form-control mm-txt"
                            name="branch_id" v-model="search.branch_id" style="width:100%"
                        >
                            <option value="">Select One</option>
                            <option v-for="branch in branches" :value="branch.id"  >{{branch.branch_name}}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="warehouse_id">Warehouse</label>
                        <select id="warehouse_id" class="form-control"
                            name="warehouse_id" v-model="search.warehouse_id" style="width:100%" required
                        >
                            <option value="">Select One</option>
                            <option v-for="warehouse in warehouses" :value="warehouse.id"  >{{warehouse.warehouse_name}}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="product_code">Product Code</label>
                        <input type="text" class="form-control" id="product_code" name="product_code" v-model="search.product_code">
                    </div>                    

                    <div class="form-group col-md-4 col-lg-3">
                        <label for="product_name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name"
                        v-model="search.product_name">
                    </div>

                    <div class="form-group col-md-4 col-lg-3 mm-txt">
                        <label for="brand_id">Brand</label>
                        <select id="brand_id" class="form-control mm-txt"
                            name="brand_id" v-model="search.brand_id" style="width:100%" required
                        >
                            <option value="">Select One</option>
                            <option v-for="brand in brands" :value="brand.id"  >{{brand.brand_name}}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4 col-lg-3 mm-txt">
                        <label for="sale_man_id">Sale Man</label>
                        <select id="sale_man_id" class="form-control mm-txt"
                            name="sale_man_id" v-model="search.sale_man_id" style="width:100%" required
                        >
                            <option value="">Select One</option>
                            <option v-for="sale_man in sale_mans" :value="sale_man.id"  >{{sale_man.name}}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3 col-lg-2">
                        <label class="small" for="search">&nbsp;</label>
                        <button
                          class="form-control btn btn-primary font-weight-bold"
                          @click="getSOProducts(1)"
                        ><i class="fas fa-search"></i> &nbsp;&nbsp;Search </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- table start -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Sale Order Product List</h6>
            </div>
            <div class="card-body">

                <!-- sort by -->
                <div class="form-group float-left pr-2" v-if="orders.length > 0">
                    <label for="sort_by">Sort By</label>
                    <select id="sort_by" class="form-control"
                        name="sort_by" v-model="search.sort_by" style="width:200px" @change="getSOProducts(1)"
                    >
                        <option value="">Select One</option>
                        <option value="order_no">Sale Order No.</option>
                    </select>
                </div>
                <div v-if="orders.length > 0">

                    <div class="form-group float-left">
                        <select id="order" class="form-control mt-2"
                            name="order" v-model="search.order" style="width:150px; margin-left:10px;" @change="getSOProducts(1)"
                        >
                            <option value="">Select One</option>
                            <option value="ASC">Ascending</option>
                            <option value="DESC">Descending</option>
                        </select>
                    </div>
                    <div class="text-right form-group mt-4" >
                        <div class="text-right mb-2" v-if="orders.length > 0">
                            <button class="btn btn-primary btn-icon btn-sm" @click="exportExcel()"><i class="fas fa-file-excel"></i> &nbsp;Export to Excel</button>
                        </div>
                    </div>
                </div>
                <!-- end sort by -->

                <!--<div class="text-right mb-2" v-if="orders.length > 0">
                    <button class="btn btn-primary btn-icon btn-sm" @click="exportExcel()"><i class="fas fa-file-excel"></i> &nbsp;Export to Excel</button>
                </div>-->

               <div class="table-responsive">
                    <table class="table table-bordered table-striped table_no" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">SO No.</th>
                                <th class="text-center">Sale Man</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Branch</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Product Code</th>
                                <th class="text-center">Product Name</th>
                                <th class="text-center">QTY</th>
                                <th class="text-center">Selling UOM</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="order in orders">
                                <tr>
                                    <td class="text-right"></td>
                                    <td>{{order.order_no}}</td>
                                    <td>{{order.sale_man}}</td>
                                    <td>{{order.order_date}}</td>
                                    <td>{{order.branch_name}}</td>
                                    <td class="mm-txt">{{order.cus_name}}</td>
                                    <td>{{order.brand_name}}</td>
                                    <td>{{order.product_code}}</td>
                                    <td class="mm-txt">{{order.product_name}}</td>
                                    <td>{{order.product_quantity}}</td>
                                    <td>{{order.uom_name}}</td>
                                    <td class="text-right">{{order.is_foc == 0 ? order.price : 'FOC'}}</td>
                                    <td class="text-right">{{order.total_amount == null || order.total_amount == '' ? 0 : order.total_amount}}</td>
                                </tr>
                            </template>
                            <tr>
                                <th colspan="12" class="text-right">Total</th>
                                <th class="text-right">{{priceTotal}}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- table end -->
        <div id="loading" class="text-center"><img :src="storage_path+'/image/loader_2.gif'" /></div>
    </div>

</template>

<script>
    export default {

        data() {
            return {
                search: {
                    from_date: "",
                    to_date: "",
                    order_no: "",
                    customer_id: "",
                    product_code: "",
                    product_name: "",
                    brand_id: "",
                    sale_man_id: "",
                    sort_by: '',
                    order: '',
                    warehouse_id: "",
                    branch_id: "",
                },
                orders: [],
                customers:[],
                brands: [],
                user_year: '',
                user_role: '',
                sale_mans: [],
                warehouses:[],
                branches: [],
                site_path: '',
                storage_path: '',
            };
        },

        created() {
            this.user_year = document.querySelector("meta[name='user-year-likelink']").getAttribute('content');

            this.user_role = document.querySelector("meta[name='user-role']").getAttribute('content');

            this.site_path = document.querySelector("meta[name='site-path']").getAttribute('content');
            //this.site_path = this.site_path.substring(this.site_path.lastIndexOf('/')+1);
            this.storage_path = document.querySelector("meta[name='storage-path']").getAttribute('content');
        },

        mounted() {
            $("#loading").hide();
            let app = this;

            app.initCustomers();
            app.initBrands();
            //app.initWarehouses();
            app.initBranches();

            app.initSaleMan();

            $("#sale_man_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.sale_man_id = data.id;
            });

            $("#branch_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.branch_id = data.id;
                app.warehouses = []; 
                if(data.id != "") {
                    axios.get("warehouses_bybranch/"+ data.id).then(({ data }) => (app.warehouses = data.data));
                } else {
                    axios.get("warehouses_bybranch/null").then(({ data }) => (app.warehouses = data.data));
                }
            });

            $("#warehouse_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.warehouse_id = data.id;
            });

            $("#from_date")
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
                app.search.from_date = moment().format('YYYY-MM-DD');
                if(app.user_year < y) { 
                  if(app.search.from_date == app.user_year+"-12-31" || app.search.from_date == '') {
                    app.search.from_date = app.user_year+"-12-31";
                  }
                }
            })
            .on("dp.change", function(e) {
                var formatedValue = e.date.format("YYYY-MM-DD");
                //console.log(formatedValue);
                app.search.from_date = formatedValue;
            });

            $("#to_date")
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
                app.search.to_date = moment().format('YYYY-MM-DD');
                if(app.user_year < y) { 
                  if(app.search.to_date == app.user_year+"-12-31" || app.search.to_date == '') {
                    app.search.to_date = app.user_year+"-12-31";
                  }
                }
            })
            .on("dp.change", function(e) {
                var formatedValue = e.date.format("YYYY-MM-DD");
                //console.log(formatedValue);
                app.search.to_date = formatedValue;
            });

            $("#customer_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.customer_id = data.id;
            });

            /*** $("#warehouse_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.warehouse_id = data.id;
            }); ***/

            $("#brand_id").on("select2:select", function(e) {

                var data = e.params.data;
                app.search.brand_id = data.id;
            });
        },

        methods: {

            initSaleMan() {
              axios.get("/sale_man").then(({ data }) => (this.sale_mans = data.data));
              $("#sale_man_id").select2();
            },

            initBranches() {
              axios.get("/branches_byuser").then(({ data }) => (this.branches = data.data));
              $("#branch_id").select2();
            },

            initWarehouses() {
              axios.get("/warehouses").then(({ data }) => (this.warehouses = data.data));
              $("#warehouse_id").select2();
            },

            initCustomers() {
              axios.get("/customers").then(({ data }) => (this.customers = data.data));
              $("#customer_id").select2();
            },

            initBrands() {
              axios.get("/report_brands").then(({ data }) => (this.brands = data.data));
              $("#brand_id").select2();
            },

            getSOProducts(page = 1) {

                if(this.search.from_date == "") {                  
                    swal("Warning!", "From Date must be added!", "warning")
                    return false;
                } 

                $("#loading").show();
                let app = this;



                var search =
                    "&from_date=" +
                    app.search.from_date +
                    "&to_date=" +
                    app.search.to_date +
                    "&order_no=" +
                    app.search.order_no +
                    "&product_code=" +
                    app.search.product_code +
                    "&customer_id=" +
                    app.search.customer_id +
                    "&brand_id=" +
                    app.search.brand_id +
                    "&sale_man_id=" +
                    app.search.sale_man_id +
                    "&product_name=" +
                    app.search.product_name +
                    "&order=" +
                    app.search.order +
                    "&warehouse_id=" +
                    app.search.warehouse_id +
                    "&branch_id=" +
                    app.search.branch_id +
                    "&sort_by=" +
                    app.search.sort_by;

                axios.get("/so_product_report?" + search).then(({ data }) => (app.orders = data.data))
                .then(function() {
                    $("#loading").hide();
                });
            },

            exportExcel() {    

                let app = this;
                if(this.search.from_date == "") {                  
                    swal("Warning!", "From Date must be added!", "warning")
                    return false;
                } 

              var search =
                    "&from_date=" +
                    app.search.from_date +
                    "&to_date=" +
                    app.search.to_date +
                    "&order_no=" +
                    app.search.order_no +
                    "&product_code=" +
                    app.search.product_code +
                    "&customer_id=" +
                    app.search.customer_id +
                    "&brand_id=" +
                    app.search.brand_id +
                    "&sale_man_id=" +
                    app.search.sale_man_id +
                    "&product_name=" +
                    app.search.product_name +
                    "&order=" +
                    app.search.order +
                    "&warehouse_id=" +
                    app.search.warehouse_id +
                    "&branch_id=" +
                    app.search.branch_id +
                    "&sort_by=" +
                    app.search.sort_by;

                /*axios.get("/so_product_export?" + search)
                .then(function(response) {
                  console.log(response);
                })
                .catch(error => {
                  console.log(error);
                })
                .finally(() => loading.hide());*/

                var baseurl = window.location.origin;
                //window.open(baseurl+'/so_product_export?'+search);
                window.open(this.site_path+'/so_product_export?'+search);
            },

            dateFormat(d) {
                return moment(d).format('YYYY-MM-DD');
            },

            getSellingUom(product,uom_id) {
                var key = product.selling_uoms.findIndex(x => x.pivot.uom_id == uom_id);
                if(key == -1) {
                    return product.uom.uom_name;
                } else {
                    return product.selling_uoms[key].uom_name;
                }
            },

            numberWithCommas(x) {
                if(x != null) {
                  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                } else {
                  return x;
                }
            },
        },

        computed: {
          priceTotal: function(){

            let sum = 0;
            let app = this;
              app.totalcount = 0;
              app.orders.forEach(function(order) {
                if(order.total_amount != null && order.total_amount != '') {
                    sum += (parseFloat(order.total_amount));
                }

              });


           return this.numberWithCommas(sum);
        },
      }, 
        

    }
</script>