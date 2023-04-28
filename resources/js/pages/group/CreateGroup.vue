<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm } from 'vee-validate';
import * as yup from 'yup';

const toastr = useToastr();
const router = useRouter();

const users = ref([]);
const groups = ref([]);
const accounts = ref([]);
const groupusers = ref([]);
const selectedUser = ref([]);

const formatted = ref();
const formatted_s = ref();
const countUser = ref(0);



const getUser = async () => {

    try {
        const response = await axios.get(`/api/userlist`)
        users.value = response.data;
        console.log('user added');

    } catch (error) {
        console.error(error);
    }
}

const count = () =>{
    countUser = selectedUser.length;
}

const createBillSchema = yup.object({
    group: yup.number().required(),
    price: yup.number().required(),
    period: yup.date().required(),
    account: yup.number().required(),
});

const createBillSchema_s = yup.object({
    user: yup.number().required(),
    price: yup.number().required(),
    period: yup.date().required(),
    account: yup.number().required(),
});

const createBill = (values, { resetForm, actions }) => {
    axios.post('/api/bill', values)
        .then((response) => {
            resetForm();
            formatted.value = null;
            toastr.success('Pay created successfully!');
            getBill();
        })
        .catch((error) => {
            console.log(error);
        })
};

const createBill_s = (values, { resetForm, actions }) => {
    axios.post('/api/bill_s', values)
        .then((response) => {
            resetForm();
            formatted_s.value = null;
            toastr.success('Pay created successfully!');
            getBill();
        })
        .catch((error) => {
            console.log(error);
        })
};


const groupchange = (event) => {
    groupusers.value = [];
    axios.get('/api/users', {
        params: {
            group_id: event.target.value
        }
    })
        .then((response) => {
            groupusers.value = response.data;
        });
}

const getAccount = () => {
    axios.get('/api/account')
        .then((response) => {
            accounts.value = response.data;
        })
}

const getGroup = () => {
    axios.get('/api/group')
        .then((response) => {
            groups.value = response.data;
        })
}

const handleChange = (event) => {
    formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}

const handleChange_s = (event) => {
    formatted_s.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}

const getBill = () => {
    if ($.fn.DataTable.isDataTable('#myTable')) {
        $('#myTable').DataTable().destroy();
    }
    axios.get(`/api/bill`)
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
                    { data: "bill_amount" },
                    { data: 'bill_remainder' },

                    {
                        data: "account_name"
                    },
                    {
                        data: "payment_status",


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
}

onMounted(() => {
    getUser();
    getBill();
    getGroup();
    getAccount();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/dashboard">Home</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/bill">Bill</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link" href="#create" data-toggle="tab">Create Group</a>
                        </li>
                        <li class="nav-item"><a class="nav-link active" href="#link" data-toggle="tab">Linking User To Group
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="create">
                            <Form @submit="createBill_s" :validation-schema="createBillSchema_s"
                                v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>User</label>
                                            <Field as="select" multiple @change="count" class="h-100 form-control"
                                                :class="{ 'is-invalid': errors.user_id }" v-model="selectedUser" name="user">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="user in users" :value="user.id">{{ user.id + "|" + user.name
                                                }}
                                                </option>

                                            </Field>
                                            <p>{{ countUser }} user selected</p>
                                            <span class="invalid-feedback">{{ errors.user }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Group</label><br>
                                            <Field as="select" class="form-control" multiple
                                                :class="{ 'is-invalid': errors.account }" name="account">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="group in groups" :value="group.id">
                                                    {{ group.id + ` | ` +
                                                        group.account_name }}
                                                </option>

                                            </Field>
                                            <span class="invalid-feedback">{{ errors.account }}</span>
                                        </div>

                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </Form>
                        </div>

                        <div class="tab-pane active" id="link">
                            <Form @submit="createBill" :validation-schema="createBillSchema" v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Group</label>
                                            <Field as="select" @change="groupchange" class="form-control"
                                                :class="{ 'is-invalid': errors.group }" name="group">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="group in groups" :value="group.id">{{ group.id + " | " +
                                                    group.group_name }}
                                                </option>

                                            </Field>
                                            <span class="invalid-feedback">{{ errors.group }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label>Account</label><br>
                                            <Field multiple as="select" class="form-control"
                                                :class="{ 'is-invalid': errors.account }" name="account">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="account in accounts" :value="account.id">
                                                    {{ account.id + ` | ` +
                                                        account.account_name }}
                                                </option>

                                            </Field>
                                            <span class="invalid-feedback">{{ errors.account }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label>Periodic</label>
                                            <Field :class="{ 'is-invalid': errors.period }" class="form-control"
                                                name="period" type="month" />
                                            <span  class="invalid-feedback">{{ errors.period }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label>Price</label>
                                            <Field :class="{ 'is-invalid': errors.price }" @keyup="handleChange"
                                                class="form-control" name="price" type="number" />
                                            <p>{{ formatted }}</p>
                                            <span class="invalid-feedback">{{ errors.price }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label> User Group
                                            </label>
                                            <table class="table table-sm text-center table-bordered">
                                                <thead>
                                                    <tr>

                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Join At</th>

                                                    </tr>
                                                </thead>
                                                <tr v-if="groupusers.length === 0">
                                                    <td colspan="3">No Record</td>
                                                </tr>
                                                <tr v-for="user in groupusers">
                                                    <td>{{ user.id }} </td>
                                                    <td>{{ user.name }}</td>
                                                    <td>{{ user.join_at }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </Form>

                        </div>

                        <div class="tab-pane" id="range">
                            Coming Soon
                        </div>

                    </div>

                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="mx text-center">List Bill</h4>
                </div>
                <div class="card-body">
                    <table id="myTable" class="display table table-bordered " style="overflow: auto;width:100%">
                        <thead>
                            <tr>
                                <th>Created</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Remainder</th>
                                <th>Account</th>
                                <th>Status</th>
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
</template>

<script>



import accounting from 'accounting';
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
