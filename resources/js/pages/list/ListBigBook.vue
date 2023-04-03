<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate } from '../../helper.js';

const toastr = useToastr();
const listbook = ref([]);
const users = ref([]);
const wallets = ref([]);
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);


const confirmDataDeletion = (id) => {
    payIdBeingDeleted.value = id;
    console.log('click del');
    $('#deleteDataModal').modal('show');
};



const deleteData = () => {
    console.log(payIdBeingDeleted.value);
    axios.delete(`/api/pay/${payIdBeingDeleted.value}`, {
        data: {
            id: payIdBeingDeleted.value
        }
    })
        .then((response) => {
            $('#deleteDataModal').modal('hide');
            toastr.success('Data deleted successfully!');
            getData();
            //payDeleted(payIdBeingDeleted.value)
        });
};


const createDataSchema = yup.object({
    user_id: yup.string().required(),
    status: yup.number().required(),
    pay: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required()
});

const editDataSchema = yup.object({
    user_id: yup.string().required(),
    pay: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required(),
    status: yup.number().when((status, schema) => {
        return status ? schema.required() : schema;
    }),
});

const createData = (values, { resetForm, setErrors }) => {
    axios.post('/api/pay', values)
        .then((response) => {
            listpays.value.data.unshift(response.data);
            $('#payFormModal').modal('hide');
            resetForm();
            toastr.success('Data created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const AddDataBill = () => {
    editing.value = false;
    $('#payFormModal').modal('show');
};


const editData = (pay) => {
    editing.value = true;
    console.log('click edit');
    form.value.resetForm();
    $('#payFormModal').modal('show');
    formValues.value = {
        id: pay.id,
        remainder: pay.remainder,
        user_id: pay.user_id,
        status: pay.status,
        title: pay.title,
        pay: pay.pay
    };
};

const updateData = (values, { setErrors }) => {
    axios.put('/api/pay/' + formValues.value.id, values)
        .then((response) => {
            // const index = pays.value.data.findIndex(pay => pay.id === response.id);
            // listpays.value.data[index] = response.data;
            getData();
            $('#payFormModal').modal('hide');
            toastr.success('Data updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateData(values, actions);
    } else {
        createData(values, actions);
    }
}

// const selectAllDatas = () => {
//     if (selectAll.value) {
//         selectedData.value = listpays.value.data.map(pay => pay.id);
//     } else {
//         selectedData.value = [];
//     }
//     console.log(selectedData.value);
// }

// watch(searchQuery, debounce(() => {
//     search();
// }, 300));


// const pay_count = computed(() => {
//     return pay_status.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
// })


const getData = (page = 1) => {

    axios.get(`/api/pay?page=${page}`)
        .then((response) => {
            listpays.value = response.data;
            selectedData.value = [];
            selectAll.value = false;
        })

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
const getDataBook = () => {

    axios.get(`/api/bigbook`)
        .then((response) => {
            listbook.value = response.data;
            $("#myTable").DataTable({
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
                ],

                data: listbook.value,
                responsive: true,
                columns: [

                    { data: "created_at" },
                    { data: "account_name" },
                    { data: "in" },
                    { data: "out" },
                    { data: "wallet_name" },
                    {
                        data: "id",
                        render: function (data) {
                            return `<a href="#" data-id="${data}" id="edit" >
                                                <i class="fa fa-edit mr-2"></i>
                                            </a>`
                        }
                    },
                    {
                        data: "id",
                        render: function (data) {
                            return `<a href="#" data-id="${data}" id="del">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>`
                        }
                    }
                ],
            });
            $(document).on('click', "#del", function () {
                let id = $(this).data('id');
                confirmDataDeletion(id);
            });
            $('#myTable tbody').on('click', '#edit', function () {
                var row = $(this).closest('tr');
                var table = $('#myTable').DataTable();
                var rowData = table.row(row).data();
                // Do something with the row data, such as displaying it in a form for editing
                console.log(rowData);
                editData(rowData);

            });
        })

}


onMounted(() => {
    getDataBook();
    getWallet();
    getUser();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Big Book | Buku Besar</strong></h1>
                    <p><small>Buku pencatatan untuk semua transaksi keuangan</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Big Book</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <button @click="AddDataBill" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Data Bill
                    </button>


                    <table id="myTable" class="table table-bordered display hover " style="overflow: auto;width:100%">
                        <thead>
                            <tr>
                                <th>Date Time</th>
                                <th>Title</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Saldo</th>
                                <th>Edit</th>
                                <th>Del</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Data</span>
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
                    <button @click.prevent="deleteData" type="button" class="btn btn-primary">Delete Data</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Data</span>
                        <span v-else>Add New Data</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editDataSchema : createDataSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-pay">
                            <label>Name User</label>
                            <Field name="userId" as="select" class="form-control" :class="{ 'is-invalid': errors.userId }"
                                id="userId" aria-describedby="nameHelp" placeholder="Enter full name">
                                <option value="" disabled>Select a Name</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>

                            </Field>
                            <span class="invalid-feedback">{{ errors.user_id }}</span>
                        </div>
                        <div class="form-pay">
                            sds
                        </div>
                        <div class="form-pay">
                            <label for="desc">Datament</label>
                            <Field name="title" type="number" class="form-control " :class="{ 'is-invalid': errors.title }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.title }}</span>
                        </div>

                        <div class="form-pay">
                            <label>Wallet</label>
                            <Field name="walletId" as="select" class="form-control" :class="{ 'is-invalid': errors.userId }"
                                id="walletId" aria-describedby="nameHelp" placeholder="Enter full name">
                                <option value="" disabled>Select a Name</option>
                                <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}
                                </option>

                            </Field>
                            <span class="invalid-feedback">{{ errors.walletId }}</span>
                        </div>

                        <div class="form-pay">
                            <label>Data At</label>
                            <Field name="pay_at" type="date" class="form-control " :class="{ 'is-invalid': errors.pay_at }"
                                id="pay_at" aria-describedby="nameHelp" placeholder="Enter Data Date" />
                            <span class="invalid-feedback">{{ errors.pay_at }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
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


    },
}



</script>
