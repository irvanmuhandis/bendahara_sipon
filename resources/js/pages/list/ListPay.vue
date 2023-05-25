<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate } from '../../helper.js';

const toastr = useToastr();
const wallets = ref([]);
const accounts = ref([]);

const pay = ref();
const listPay = ref([]);
const payIdBeingDeleted = ref(0);

const form = ref({
    'wallet': null,
    'account': null,
    'amount': null,
    'desc': null
});
const errors = ref({
    'wallet': null,
    'account': null,
    'amount': null,
    'desc': null
});

const createTrans = (event) => {
    event.preventDefault();

    if (valid()) {
        form.value.account = form.value.account.id;
        form.value.wallet = form.value.wallet.id;
        axios.post('/api/trans', form.value)
            .then((response) => {
                toastr.success('Pay created successfully!');
            })
            .catch((error) => {
                {
                    console.log(error)
                }
            })
    }
};


const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {
        form.value[key] = null;
    }
}

const valid = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.wallet == null) {
        errors.value.wallet = 'Pilih Dompet ';
        err += 1;
    }
    if (form.value.account == null) {
        errors.value.account = 'Pilih Akun '
        err += 1;
    }
    if (form.value.amount == null || 0) {
        errors.value.amount = 'Masukkan Jumlah Uang '
        err += 1;
    }
    if (form.value.desc == null) {
        errors.value.desc = 'Masukkan JDeskripsi '
        err += 1;
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const getWallet = () => {

    axios.get(`/api/wallet/list`)
        .then((response) => {
            wallets.value = response.data;
        })

}

const getAccount = () => {

    axios.get(`/api/account/list`)
        .then((response) => {
            accounts.value = response.data;
        })

}


const getPay = () => {

    axios.get(`/api/pay`)
        .then((response) => {
            $("#myTable").DataTable({
                destroy: true,
                columnDefs: [
                    {
                        targets: [-1, -2],
                        className: 'dt-body-center'
                    }
                ],
                dom: "Bfrtip",
                buttons: [

                    'colvis',
                    "copy",
                    "csv",
                    'pageLength'
                ],

                data: response.data,
                responsive: true,
                columns: [
                    { data: 'created_at' },
                    { data: "name" },
                    { data: "payment" },
                    { data: "wallet_name" },
                    {
                        data: "payable_type",
                        render: function (data) {
                            switch (data) {
                                case "App\\Models\\Bill":
                                    return `<span class="badge badge-primary">Bill</span>`;
                                    break;
                                case "App\\Models\\Debt":
                                    return `<span class="badge badge-danger">Debt</span>`;
                                    break;
                                default:
                                    return `<span class="badge badge-secondary">Null</span>`;
                            }
                        }
                    },
                    { data: 'updated_at' },

                    {
                        data: 'id',
                        render: (data) => {
                            return 'Muhan';
                        }
                    },
                    {
                        data: ['id'],
                        render: function (data) {
                            return `
                                <a href="#" class="mr-2" data-id="${data}" id="show_bill" >
                                    <i class="fas fa-eye "></i>
                                            </a>

                                <a href="#" data-id="${data}" id="edit" >
                                    <i class="fa fa-edit mr-2"></i>
                                </a>
                                <a href="#" data-id="${data}" id="del" >
                                    <i class="fa fa-trash text-danger"></i>
                                </a>

                                    `

                            //     <div class="delete-checkbox">
                            //     <i class="fa fa-trash text-danger"></i>
                            //     <input type="checkbox" name="delete-checkbox"  value="${data}">
                            // </div>

                        }
                    }
                ],
            });
            $('#myTable tbody').on('click', "#del", function () {
                let id = $(this).data('id');
                confirmPayDeletion(id);
            });

        })
    $('#myTable tbody').on('click', '#edit', function () {
        var row = $(this).closest('tr');
        var table = $('#myTable').DataTable();
        var rowData = table.row(row).data();
        // Do something with the row data, such as displaying it in a form for editing
        console.log(rowData);
        editPay(rowData);
    });
    // $("#myTable tbody").on("click", ".delete-checkbox", function () {
    //     $(this).toggleClass("active");
    //     $(this).find(":checkbox").prop("checked", $(this).hasClass("active"));
    // });

    // $("#delete-selected-rows").on("click", function () {
    //     var selectedRows = $(".delete-checkbox.active");
    //     if (selectedRows.length > 0) {
    //         if (confirm("Are you sure you want to delete the selected rows?")) {
    //             var ids = [];
    //             selectedRows.each(function () {
    //                 ids.push($(this).find(":checkbox").val());
    //             });
    //             deleteRows(ids);
    //         }
    //     } else {
    //         alert("No rows selected.");
    //     }
    // });
}

// const deleteRows = (ids) => {

//     $.ajax({
//         url: "/api/pay",
//         method: "POST",
//         data: { ids: ids },
//         success: function (response) {
//             if (response.success) {
//                 var table = $("#myTable").DataTable();
//                 selectedRows.each(function () {
//                     var row = $(this).closest("tr");
//                     table.row(row).remove().draw();
//                 });
//             } else {
//                 alert("Failed to delete rows.");
//             }
//         }
//     });
// }




onMounted(() => {

    getPay();
    getWallet();
    getAccount();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Pemasukkan</strong></h1>
                    <!-- <p><small>Pembayaran </small></p> -->
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Pays</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="row pb-3">
                <router-link to="/admin/pay/create-bill" class="col-md-6">
                    <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Pembayaran Tagihan</button>
                </router-link>
                <router-link to="/admin/pay/create-debt" class="col-md-6">
                    <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Pembayaran Hutang</button>
                </router-link>
            </div>
            <!-- <button @click="AddPayBill" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Pay Bill
                    </button> -->
            <!-- <button id="delete-selected-rows" class="btn btn-danger" type="button" >DELETE ROW</button> -->

            <form @submit="createTrans" class="mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Akun</label>
                            <VueMultiselect v-model="form.account" :option-height="9" :options="accounts"
                                :class="{ 'is-invalid': errors.account }" :close-on-select="true" placeholder="Pilih Satu "
                                label="account_name" track-by="id" :show-labels="false">
                                <template v-slot:option="{ option }">
                                    <div>{{ option.account_name }} - {{ option.id }} </div>
                                </template>
                            </VueMultiselect>
                            <span class="invalid-feedback">{{ errors.account }}</span>
                        </div>
                        <div class="form-group">
                            <label>Dompet</label><br>
                            <VueMultiselect v-model="form.wallet" :option-height="9" :options="wallets"
                                :class="{ 'is-invalid': errors.account }" :close-on-select="true" @select="getId"
                                placeholder="Pilih Satu " label="wallet_name" track-by="id" :show-labels="false">
                                <template v-slot:option="{ option }">
                                    <div>{{ option.wallet_name }} - {{ option.id }} </div>
                                </template>
                            </VueMultiselect>
                            <span class="invalid-feedback">{{ errors.wallet }}</span>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label><br>
                            <input :class="{ 'is-invalid': errors.amount }" @change="handleChange" class="form-control"
                                v-model="form.amount" type="number" />
                            <span class="invalid-feedback">{{ errors.amount }}</span>
                            {{ formatted }}
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label><br>
                            <textarea :class="{ 'is-invalid': errors.desc }" class="form-control"
                                v-model="form.desc"></textarea>
                            <span class="invalid-feedback">{{ errors.desc }}</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-100 btn btn-primary">Submit</button>
            </form>

            <table id="myTable" class="display table " style="overflow: auto;width:100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Pay</th>
                        <th>Wallet</th>
                        <th>Type</th>
                        <th>Update</th>
                        <th>Operator</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div>





    <div class="modal fade" id="deletePayModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Pay</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this pay ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deletePay" type="button" class="btn btn-primary">Delete Pay</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>



import accounting from 'accounting';
import { validate } from "vee-validate";
export default {


    data() {
        return {
            formatted: '',
        }
    },
    // computed property to retrieve current page number
    computed: {
        currentPage() {
            return this.listpays.current_page;
        }
    },

    // methods to handle pagination events
    methods: {
        changePage(page) {
            this.search(page);
        },
        handleChange(event) {
            this.formatted = accounting.formatMoney(event.target.value, 'Rp. ', 0);
        },

    },
}



</script>
