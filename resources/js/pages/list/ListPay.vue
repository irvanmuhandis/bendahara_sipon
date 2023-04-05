<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm, useField } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate } from '../../helper.js';

const toastr = useToastr();
const listpays = ref([]);
const listdebt = ref([]);
const userbill = ref([]);
const listbill = ref({ 'data': [] });
const users = ref([]);
const wallets = ref([]);
const editing = ref(false);
const form = ref(null);
const wallet = ref();
const user = ref();
const checked = ref([]);
const total = ref(0);
const pay = ref();
const remainder = ref([]);

const payIdBeingDeleted = ref(0);

const totalize = () => {


    remainder.value = [];
    total.value = 0;
    checked.value.forEach((id) => {
        const item = userbill.value.find((item) => item.id === id);
        total.value = total.value + item.bill_remainder;
        remainder.value.push(item.bill_remainder);
    });
}


const formValues = ref(null);
const month = ref({
    month: new Date().getMonth(),
    year: new Date().getFullYear()
});


const confirmPayDeletion = (id) => {
    payIdBeingDeleted.value = id;
    console.log('click del');
    $('#deletePayModal').modal('show');
};



const deletePay = () => {
    console.log(payIdBeingDeleted.value);
    axios.delete(`/api/pay/${payIdBeingDeleted.value}`, {
        data: {
            id: payIdBeingDeleted.value
        }
    })
        .then((response) => {
            $('#deletePayModal').modal('hide');
            toastr.success('Pay deleted successfully!');
            getPay();
            //payDeleted(payIdBeingDeleted.value)
        });
};

const createPaySchema = yup.object({
    userId: yup.number().required(),
    payment: yup.number().required(),
    pay_at: yup.date().required(),
    walletId: yup.number().required(),
    billsId: yup.array().min(1, 'You must select at least one option').of(
        yup.number()),
}).test('payment-more-than-total', 'Payment should not more than the total', function (values) {
    const ttl = total.value;
    const py = values.payment;
    if (py > ttl) {
        throw new yup.ValidationError('Payment should not more than the total', null, 'payment');
    }
    return true;
});


const createPay = (values, { resetForm, setErrors }) => {
    values.remainder = remainder.value;

    axios.post('/api/pay', values)
        .then((response) => {
            // listpays.value.data.unshift(response.data);
            $('#payFormModal').modal('hide');
            resetForm();
            toastr.success('Pay created successfully!');
            getPayBill();
            getPayDebt();
        })
        .catch((error) => {
            {
                console.log(error)
            }
        })
};

const editPaySchema = yup.object({
    userId: yup.number().required(),
    payment: yup.number().required(),
    pay_at: yup.date().required(),
    walletId: yup.number().required(),
    billsId: yup.array().min(1, 'You must select at least one option').of(
        yup.number()),
}).test('payment-more-than-total', 'Payment should not more than the total', function (values) {
    const ttl = total.value;
    const py = values.payment;
    if (py > ttl) {
        throw new yup.ValidationError('Payment should not more than the total', null, 'payment');
    }
    return true;
});


const AddPayBill = () => {
    editing.value = false;
    $('#payFormModal').modal('show');
};


const editPay = (pay) => {
    editing.value = true;
    console.log('click edit');
    // form.value.resetForm();
    $('#payFormModal').modal('show');
    formValues.value = {
        userId: pay.user_id,
        payment: pay.payment,
        pay_at: pay.created_at,
        walletId: pay.wallet_id,
        billsId: pay.payable_id
    };
};

const updatePay = (values, { setErrors }) => {

    axios.put('/api/pay/' + formValues.value.id, values)
        .then((response) => {
            // const index = pays.value.data.findIndex(pay => pay.id === response.id);
            // listpays.value.data[index] = response.data;
            getPay();
            $('#payFormModal').modal('hide');
            toastr.success('Pay updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    console.log(values);
    console.log(editing.value);

    var pays = pay.value;



    if (editing.value) {
        updatePay(values, actions);
    } else {
        createPay(values, actions);
    }
}


const getWallet = () => {

    axios.get(`/api/wallet`)
        .then((response) => {
            wallets.value = response.data;
        })

}

const getUser = () => {

    axios.get(`/api/userlist`)
        .then((response) => {
            users.value = response.data;
        })

}

const walletchange = (event) => {
    wallet.value = event.target.value;
    console.log(wallet.value);
}
const userchange = (event) => {
    user.value = event.target.value;
    getUserbill();
}

const getUserbill = () => {
    checked.value = [];
    axios.get(`/api/user/bill/` + user.value)
        .then((response) => {
            userbill.value = response.data;
        })

}

const getPay = () => {

    axios.get(`/api/pay`)
        .then((response) => {
            listbill.value = response.data;
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

                data: listbill.value,
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
                                case "App\\Models\\Debts":
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
    getUser();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Payment | Pembayaran</strong></h1>
                    <p><small>Pembayaran untuk hutang dan tagihan santri</small></p>
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
            <div class="card">
                <div class="card-head row p-3">
                    <router-link to="/admin/pay/create-bill" class="col-md-6">
                        <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Add New
                            Add Bill Payment</button>
                    </router-link>
                    <router-link to="/admin/pay/create-debt" class="col-md-6">
                        <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Add New
                            Add Debt Payment</button>
                    </router-link>

                </div>
                <div class="card-body">
                    <!-- <button @click="AddPayBill" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Pay Bill
                    </button> -->
                    <!-- <button id="delete-selected-rows" class="btn btn-danger" type="button" >DELETE ROW</button> -->


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
