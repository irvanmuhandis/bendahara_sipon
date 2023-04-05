<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm } from 'vee-validate';
import * as yup from 'yup';

const toastr = useToastr();
const router = useRouter();
const form = reactive({
    title: '',
    client_id: '',
    client_date: '',
    start_time: '',
    end_time: '',
    desc: ''
})
const formValues = ref(0);
const users = ref([]);
const user = ref(0);
const wallets = ref([]);
const wallet = ref(0);
const checked = ref([]);
const total = ref(0);
const pay = ref([]);
const remainder = ref([]);
const userbill = ref([]);
const pay_status = ref([]);
const formatted = ref();


const totalize = () => {

    remainder.value = [];
    total.value = 0;
    console.log("bill clicked");
    console.log(checked.value)
    checked.value.forEach((id) => {
        const item = userbill.value.find((item) => item.id === id);
        total.value = total.value + item.bill_remainder;
        remainder.value.push(item.bill_remainder);
    });
}

const getUser = async () => {

    try {
        const response = await axios.get(`/api/userlist`)
        users.value = response.data;
        console.log('user added');

    } catch (error) {
        console.error(error);
    }
}

const getWallet = async () => {

    try {
        const response = await axios.get(`/api/wallet`)
        wallets.value = response.data;
        console.log('wallet added');
    } catch (error) {
        console.error(error);
    }
}

const getUserbill = async () => {
    checked.value = [];

    try {
        const response = await axios.get(`/api/user/bill/${user.value}`);
        userbill.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const createPayBillSchema = yup.object({
    user_id: yup.number().required(),
    payment: yup.number().required(),
    date: yup.date().required(),
    wallet_id: yup.number().required(),
    bill_id: yup.array().min(1, 'You must select at least one bill option').of(
        yup.number()).required(),
    // bill_id: yup.number().required()
}).test('payment-more-than-total', 'Payment should not more than the total', function (values) {
    const ttl = total.value;
    const py = values.payment;
    if (py > ttl) {
        throw new yup.ValidationError('Payment should not more than the total', null, 'payment');
    }
    if (checked == []) {
        throw new yup.ValidationError('Insert atleast 1 bill ', null, 'bill_id');
    }
    return true;
});


const createPay = (values, { resetForm, actions }) => {
    values.remainder = remainder.value;

    axios.post('/api/pay', values)
        .then((response) => {
            resetForm();
            userbill.value = [];
            total.value = 0;
            formatted.value = [];
            toastr.success('Pay created successfully!');
            getPayBill();
        })
        .catch((error) => {
            console.log(error);
        })
};

const walletchange = (event) => {
    wallet.value = event.target.value;
    console.log(wallet.value);
}
const userchange = (event) => {
    user.value = event.target.value;
    getUserbill();
}
const getPayStatus = () => {
    axios.get('/api/pay/status')
        .then((response) => {
            pay_status.value = response.data;
        })
}

const handleChange = (event) => {
    formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}


const getPayBill = () => {
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
}

onMounted(() => {
    getUser();
    getWallet();
    getPayBill();
    getPayStatus();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Payment Bill</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/dashboard">Home</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/pay">Pay</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Create Pay Bill</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 card">
                    <div class="card-body">
                        <Form @submit="createPay" :validation-schema="createPayBillSchema" v-slot:default="{ errors }">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User</label>
                                        <Field as="select" @change="userchange" class="form-control"
                                            :class="{ 'is-invalid': errors.user_id }" name="user_id">
                                            <option disabled>Pilih Salah Satu</option>

                                            <option v-for="user in users" :value="user.id">{{ user.id + "|" + user.name }}
                                            </option>

                                        </Field>
                                        <span class="invalid-feedback">{{ errors.user_id }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Wallet</label>
                                        <Field as="select" class="form-control" :class="{ 'is-invalid': errors.wallet_id }"
                                            name="wallet_id">
                                            <option disabled>Pilih Salah Satu</option>

                                            <option v-for="wallet in wallets" :value="wallet.id">{{ wallet.wallet_name }}
                                            </option>

                                        </Field>
                                        <span class="invalid-feedback">{{ errors.wallet_id }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label>Bill</label>
                                        <div class="row mb-1" v-for="bill in userbill" :key="bill.id">
                                            <Field  name="bill_id" type="checkbox" @change="totalize" v-model="checked"
                                                class="col-md-1" :value="bill.id"/>
                                            <label class="col-md-11">
                                                {{ bill.account_name }}
                                                <span class="text-right text-monospace">{{
                                                    accounting.formatMoney(
                                                        bill.bill_remainder, "Rp", 0) }}
                                                    ({{
                                                        (pay_status.find(obj => obj.value === bill.payment_status)).name
                                                    }})
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment</label><br>
                                        <span>Total Bill : {{ accounting.formatMoney(total, "Rp", 0) }}</span>
                                        <Field type="number" name="payment" @keyup="handleChange"
                                            :class="{ 'is-invalid': errors.payment }" class="form-control" id="time" />
                                        <span>{{ formatted }}</span><br>
                                        <span class="invalid-feedback">{{ errors.bill_id }}</span>
                                        <span class="invalid-feedback">{{ errors.payment }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Date</label>
                                <Field :class="{ 'is-invalid': errors.date }" class="form-control" name="date"
                                    type="date" />
                                <span class="invalid-feedback">{{ errors.date }}</span>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            {{ errors }}
                        </Form>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-12 card">
                    <div class="card-body">
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
