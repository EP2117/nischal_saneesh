<template>
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#!"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/'">Home</a></li>
                <li class="breadcrumb-item"><a :href="site_path+'/master'">Master</a></li>
                <li class="breadcrumb-item"><router-link tag="span" to="/customer" class="font-weight-normal"><a href="#">Customer</a></router-link></li>
                <li class="breadcrumb-item active" aria-current="page">Customer Form</li>

            </ol>
        </nav>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800" v-if="!isEdit">Create New Customer</h4>
            <h4 class="mb-0 text-gray-800" v-else>Edit Customer</h4>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Details</h6>
            </div>
            <div class="card-body">
                <div class="d-block">
                    <!-- form start -->
                    <form class="form" method="post" @submit.prevent="onSubmit" @keydown="form.errors.clear($event.target.name)">

                        <div class="row mt-3">
                            <div class="form-group col-md-4">
                                <label for="cus_code">Customer Code</label>
                                 <input type="text" class="form-control" id="cus_code" name="cus_code"
                                v-model="form.cus_code" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="cus_name">Customer Name</label>
                                 <input type="text" class="form-control" id="cus_name" name="cus_name"
                                v-model="form.cus_name" required>
                            </div> 

                            <div class="form-group col-md-4">
                                <label for="cus_type">Customer Type</label>
                                <select id="cus_type" class="form-control"
                                    name="cus_type" v-model="form.cus_type" style="width:100%" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="ctype in cus_types" :value="ctype.id"  >{{ctype.customer_type_name}}</option>
                                </select>
                            </div>                           
                        </div>

                        <div class="row mt-3">
                            <div class="form-group col-md-4">
                                <label for="country_id">Country</label>
                                <select id="country_id" class="form-control"
                                    name="country_id" v-model="form.country_id" style="width:100%" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="country in countries" :value="country.id"  >{{country.country_name}}</option>
                                </select>
                            </div> 

                            <div class="form-group col-md-4">
                                <label for="state_id">State</label>
                                <select id="state_id" class="form-control"
                                    name="state_id" v-model="form.state_id" style="width:100%" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="state in states" :value="state.id"  >{{state.state_name}}</option>
                                </select>
                            </div>  

                            <div class="form-group col-md-4">
                                <label for="township_id">Township</label>
                                <select id="township_id" class="form-control"
                                    name="township_id" v-model="form.township_id" style="width:100%" required
                                >
                                    <option value="">Select One</option>
                                    <option v-for="township in townships" :value="township.id"  >{{township.township_name}}</option>
                                </select>
                            </div>                           
                        </div>

                        <div class="row mt-3">
                            <div class="form-group col-md-4">
                                <label for="cus_phone">Phone</label>
                                 <input type="text" class="form-control" id="cus_phone" name="cus_phone"
                                v-model="form.cus_phone" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="billing_address">Billing Address</label>
                                 <textarea class="form-control" id="billing_address" name="billing_address"
                                v-model="form.billing_address" rows='3' required></textarea>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="shipping_address">Shipping Address</label>
                                 <textarea class="form-control" id="shipping_address" name="shipping_address"
                                v-model="form.shipping_address" rows='3' required></textarea>
                            </div>
                        </div>

                        <div class="form-group row text-right">
                            <label class="col-lg-3 col-form-label form-control-label"></label>
                            <div class="col-lg-12">
                                <input type="reset" class="btn btn-secondary btn-sm" value="Cancel" v-if="!isEdit">
                                <input type="submit" class="btn btn-primary btn-sm" value="Save Changes">
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
                cus_name: '',
                cus_code: '',
                cus_type: '',
                country_id: '',
                state_id: "",
                township_id : '',
                cus_phone: '',
                billing_address: '', 
                shipping_address: '',
              }),
              cus_types: [],
              countries: [],
              states: [],
              townships: [],
              isEdit: false,
              customer_id: '',
              user_role: '',
              site_path: '',
              storage_path: '',
            };
        },

        created() {
            this.user_role = document.querySelector("meta[name='user-role']").getAttribute('content');

            this.site_path = document.querySelector("meta[name='site-path']").getAttribute('content');
            //this.site_path = this.site_path.substring(this.site_path.lastIndexOf('/')+1);
            this.storage_path = document.querySelector("meta[name='storage-path']").getAttribute('content');
            
            if(this.user_role != "admin" && this.user_role != "system" && this.user_role != "office_user") {
                var url =  window.location.origin;
                window.location.replace(url);
            }

            if(this.$route.params.id) {
                this.isEdit = true;
                this.customer_id = this.$route.params.id;
                this.getCustomer(this.customer_id);
            }
            //console.log(this.isEdit);
        },
        mounted() {
            //console.log('Component mounted.')
            $("#loading").hide();
            let app = this;

            $("#cus_type").select2();
            $("#cus_type").on("select2:select", function(e) {

                var data = e.params.data;
                app.form.cus_type = data.id;
            });

            $("#country_id").select2();
            $("#country_id").on("select2:select", function(e) {  
                app.states = [];
                app.townships = [];      
                var data = e.params.data;
                app.form.country_id = data.id;
                axios.get("/state_by_country/"+ data.id).then(({ data }) => (app.states = data.data));
            });

            $("#state_id").select2();
            $("#state_id").on("select2:select", function(e) { 
                app.townships = [];      
                var data = e.params.data;
                app.form.state_id = data.id;
                axios.get("/township_by_state/"+ data.id).then(({ data }) => (app.townships = data.data));
            });

            $("#township_id").select2();
            $("#township_id").on("select2:select", function(e) { 
                var data = e.params.data;
                app.form.township_id = data.id;
            });

            app.initTypes();
            app.initCountries();
            app.initStates();
            app.initTownships();
        },

        methods: {
            initTypes() {
              axios.get("/customer_type").then(({ data }) => (this.cus_types = data.data));
              //console.log(this.cus_types);
              $("#cus_type").select2();
            },

            initCountries() {
              axios.get("/country").then(({ data }) => (this.countries = data.data));
              $("#country_id").select2();
            },

            initStates() {
                if(this.form.country_id != "") {
                  axios.get("/state_by_country/" + this.form.country_id).then(({ data }) => (this.states = data.data));
                  console.log(this.states);
                  $("#state_id").select2();
                }
            },

            initTownships() {
                if(this.form.state_id != "") {
                  axios.get("/township_by_state/" + this.form.state_id).then(({ data }) => (this.townships = data.data));
                  $("#township_id").select2();
                }
            },

            getCustomer(id) {
              let app = this;
              axios
                .get("/customer/" + id)
                .then(function(response) {
                    app.form.cus_name = response.data.customer.cus_name;
                    app.form.cus_code = response.data.customer.cus_code;
                    app.form.cus_type = response.data.customer.customer_type_id;
                    $('#cus_type').val(app.form.cus_type).trigger('change');
                    app.form.country_id = response.data.customer.country_id;
                    $('#country_id').val(app.form.country_id).trigger('change');
                    app.form.state_id = response.data.customer.state_id;
                    $('#state_id').val(app.form.state_id).trigger('change');
                    app.form.township_id = response.data.customer.township_id;
                    $('#township_id').val(app.form.township_id).trigger('change');
                    app.form.cus_phone = response.data.customer.cus_phone;
                    app.form.shipping_address = response.data.customer.cus_shipping_address;
                    app.form.billing_address = response.data.customer.cus_billing_address;

                    axios.get("/state_by_country/" + app.form.country_id).then(({ data }) => (app.states = data.data));
                    $("#state_id").select2();

                    axios.get("/township_by_state/" + app.form.state_id).then(({ data }) => (app.townships = data.data));
                    $("#township_id").select2();
                    
                })
                .catch(function(error) {
                  // handle error
                  console.log(error);
                })
                .then(function() {
                  // always executed
                });
            },

            onSubmit: function(event){
                let app = this; 
                $("#loading").show();               

                if (!this.isEdit) {

                this.form
                  .post("/customer")
                  .then(function(data) {
                    console.log(data);
                    if(data.status == "success") {
                        //reset form data
                        event.target.reset();
                        $("#cus_type").select2();
                        $("#country_id").select2();
                        $("#state_id").select2();
                        $("#township_id").select2();
                        $("#loading").hide();

                        swal({
                            title: "Success!",
                            text: 'Customer Added.',
                            icon: "success",
                            button: true
                        }).then(function() {
                           // location.reload();
                           

                        });
                                                  


                        /* location.reload();
                          $.notify("Success", {
                            autoHideDelay: 3000,
                            gap: 1,
                            className: "success"
                          });*/
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
                        var obj = $.parseJSON(response.errors);
                        var errmsg = "";
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                               errmsg += obj[key][0]+"\n";
                            }
                        }
                       $.notify(errmsg, {
                        autoHideDelay: 3000,
                        gap: 1,
                        className: "error"
                      });

                    });
                    
                  } else {

                    //Edit Customer                    
                    this.form
                      .patch("/customer/" + app.customer_id)
                      .then(function(data) {
                        console.log(data);
                        //reset form data
                        event.target.reset();
                        $("#cus_type").select2();
                        $("#country_id").select2();
                        $("#state_id").select2();
                        $("#township_id").select2();  
                        if(data.status == "success") {
                            swal({
                                title: "Success!",
                                text: 'Customer is updated.',
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
                            var obj = $.parseJSON(response.errors);
                            var errmsg = "";
                            for (var key in obj) {
                                if (obj.hasOwnProperty(key)) {
                                   errmsg += obj[key][0]+"\n";
                                }
                            }
                           $.notify(errmsg, {
                            autoHideDelay: 3000,
                            gap: 1,
                            className: "error"
                          });

                        });
                    //End Edit Customer
                  }
            },
        }
    }
</script>