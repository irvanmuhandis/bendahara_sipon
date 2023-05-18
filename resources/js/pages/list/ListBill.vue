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
const listBill = ref([]);
const users = ref([]);
const wallets = ref([]);
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);
const types = [{
    id: 1,
    name: 'Satu Jam Terakhir',
},
{
    id: 2,
    name: '12 Jam Terakhir'
},
{
    id: 3,
    name: 'Satu Hari Terakhir'
},
{
    id: 4,
    name: '30 Hari Terakhir'
},
{
    id: 5,
    name: '10 Data Terakhir'
},
{
    id: 6,
    name: '100 Data Terakhir'
}
];
const destroyType = ref(null);

const delPrompt = (values, action) => {
    const modal = document.getElementById("myModal");

    // Show the modal
    $(modal).modal('show');
    destroyType.value = values.delType;
    console.log(destroyType);
}

const delMass = () => {
    switch (destroyType.value) {
        case 1:
            axios.delete('/api/bill/delHour?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 2:
            axios.delete('/api/bill/delHour?type=' + 12)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 3:
            axios.delete('/api/bill/delDay?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 4:
            axios.delete('/api/bill/delDay?type=' + 30)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 5:
            axios.delete('/api/bill/del?type=' + 10)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 6:
            axios.delete('/api/bill/del?type=' + 100)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        default:
            console.log('errror');
            break;
    }
    const modal = document.getElementById("myModal");

    // Show the modal
    $(modal).modal('hide');


};

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

const AddDataExpense = () => {
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

const getData = () => {

    axios.get(`/api/bill`)
        .then((response) => {
            listBill.value = response.data;
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

                data: listBill.value,
                responsive: true,
                destroy:true,
                columns: [

                    { data: "id" },
                    { data: "account_name" },
                    { data: "name" },
                    { data: "bill_amount" },
                    { data: 'bill_remainder' },
                    { data: 'payment_status' },
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
                confirmPayDeletion(id);
            });
            $('#myTable tbody').on('click', '#edit', function () {
                var row = $(this).closest('tr');
                var table = $('#myTable').DataTable();
                var rowData = table.row(row).data();
                // Do something with the row data, such as displaying it in a form for editing
                console.log(rowData);
                editPay(rowData);

            });
        })

}

onMounted(() => {
    getData();
    getWallet();
    getUser();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Bill | Tagihan </strong></h1>
                    <p><small>List tagihan di pondok pesantren</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Bills</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row pb-3">
                        <div class="col-md-3">
                            <button class="w-100 btn btn-primary" type="button" data-toggle="collapse"
                                data-target="#collapseWidthExample" aria-expanded="false"
                                aria-controls="collapseWidthExample">
                                Hapus Per Waktu <i class="ml-1 right fas fa-trash"></i>
                            </button>

                            <div class="collapse mt-2" id="collapseWidthExample">

                                <Form @submit="delPrompt" class="row">
                                    <div class="col-md">

                                        <Field as="select" class="form-control" name="delType">
                                            <option disabled>Pilih Salah Satu</option>
                                            <option v-for="t in types" :value="t.id">
                                                {{ t.name }}
                                            </option>
                                        </Field>


                                    </div>
                                    <div class="col-md">
                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                    </div>

                                </Form>
                            </div>
                            <!-- Modal -->
                            <div class="modal" tabindex="-1" role="dialog" id="myModal">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modal title</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Kamu Yakin Ingin Menghapus Data {{
                                                destroyType == null ? "---" : types.filter(item => item.id ==
                                                    destroyType).map(item => item.name)[0] }} ?</p>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" @click="delMass">Save
                                                changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <router-link to="/admin/bill/create" class="col-md">
                            <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Add New Bill
                            </button>
                        </router-link>


                    </div>


                    <table id="myTable" class="table table-bordered display hover ">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Account Name</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Remainder</th>
                                <th>Status</th>
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

    <div class="modal fade" id="deleteBillModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Bill</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this bill ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteBill" type="button" class="btn btn-primary">Delete Bill</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="billFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Bill</span>
                        <span v-else>Add New Bill</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editBillSchema : createBillSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-bill">
                            <label>Name User</label>
                            <Field name="title" type="text" class="form-control " :class="{ 'is-invalid': errors.title }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />

                            <span class="invalid-feedback">{{ errors.user_id }}</span>
                        </div>

                        <div class="form-bill">
                            <label for="desc">Title</label>
                            <Field name="title" type="text" class="form-control " :class="{ 'is-invalid': errors.title }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.title }}</span>
                        </div>

                        <div class="form-bill">
                            <label>Pay At</label>
                            <Field name="bill" type="number" class="form-control " :class="{ 'is-invalid': errors.bill }"
                                id="pay_at" aria-describedby="nameHelp" placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.bill }}</span>
                        </div>

                        <div class="form-bill">
                            <label>Remainder</label>
                            <Field name="remainder" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.remainder }" id="pay_at" aria-describedby="nameHelp"
                                placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.remainder }}</span>
                        </div>

                        <div class="form-bill">
                            <label>Status</label>

                            <span class="invalid-feedback">{{ errors.status }}</span>
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
            return this.listbills.current_page;
        }
    },

    // methods to handle pagination events
    methods: {
        changePage(page) {
            this.search(page);
        }
    }
}
</script>
