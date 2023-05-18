<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import accounting from 'accounting';

const toastr = useToastr();
const router = useRouter();
const users = ref([]);
const wallets = ref([]);
const userdebt = ref({ 'data': [] });
const total = ref(0);
const isLoading = ref(true);
const init = ref(true);
const formatted = ref();
const pay_status = ref([]);
const remainder = ref([]);
const errors = ref({
    'user': null,
    'debt': null,
    'wallet_id': null,
    'payment': null
});
const form = ref({
    'user': null,
    'debt': [],
    'wallet_id': null,
    'payment': null
});


const handleChange = (event) => {
    formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}

const checkLength = computed(() => {
    return form.value.debt.length;
});
const totalize = (event, id) => {
    const item = userdebt.value.data.find((item) => item.id === id);
    if (event.target.checked) {
        total.value += item.remainder;
        remainder.value.push(item.remainder);
    }
    else {
        total.value -= item.remainder;
        remainder.value.unshift(item.remainder);
    }
    console.log("bill clicked");
    console.log(remainder.value);
}
const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {
        form.value[key] = null;
    }
}

const createPay = (event) => {
    event.preventDefault();
    form.value.remainder = remainder.value;
    if (valid()) {
        axios.post('/api/pay/debt', form.value)
            .then((response) => {
                clearForm();
                isLoading.value = true;
                userdebt.value = [];
                total.value = 0;
                formatted.value = null;
                toastr.success('Pay created successfully!');
                getData();
            })
            .catch((error) => {
                console.log(error);
                toastr.error(error);
            })
    }
};


const valid = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.user == null) {
        errors.value.user = 'Pilih User ';
        err += 1;
    }
    if (form.value.debt.length == 0) {
        errors.value.bill = 'Pilih Bill '
        err += 1;
    }
    if (form.value.payment == null) {
        errors.value.payment = 'Masukkan jumlah pembayaran '
        err += 1;
    }
    if (form.value.wallet_id == null) {
        errors.value.wallet_id = 'Pilih dompet '
        err += 1;
    }
    if (form.value.payment > total.value) {
        errors.value.payment = 'Pembayaran tidak boleh lebih dari total '
        err += 1;
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
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

const getUserdebt = async (page = 1) => {
    try {
        const response = await axios.get(`/api/user/debt/${form.value.user.id}?page=${page}`);
        userdebt.value = response.data;
        isLoading.value = false;
    } catch (error) {
        console.error(error);
    }
};

const getUser = async () => {

    try {
        const response = await axios.get(`/api/userlist`)
        users.value = response.data;
        console.log('user added');
    } catch (error) {
        console.error(error);
    }
}

const getPayStatus = () => {
    axios.get('/api/pay/status')
        .then((response) => {
            pay_status.value = response.data;
        })
}

const userchange = (event) => {
    isLoading.value = true;
    init.value = false;
    remainder.value = [];
    getUserdebt();
}

const getData = () => {
    axios.get(`/api/paydebt`)
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
                    { data: 'title' },
                    { data: "wallet_name" },
                    { data: "payment" },
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
    getWallet();
    getUser();
    getData();
    getPayStatus();
});
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <RouterLink to="/admin/pay"><i class="fa fa-arrow-left"></i><strong> Kembali</strong></RouterLink>

                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <router-link to="/admin/dashboard">Home</router-link>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/pay">Pay</router-link>
                        </li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 card">
                    <div class="text-center card-header">
                        <h3 class="m-0">Create Payment Debt</h3>
                    </div>
                    <div class="card-body">
                        <form @submit="createPay">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Debt</label>
                                        <div v-if="checkLength != 0">
                                            <i> {{ checkLength }} debt checked</i>
                                        </div>
                                        <div v-if="isLoading" class="text-center">
                                            <div class="spinner-border row text-primary mx-auto" role="status"></div>
                                            <div v-if="init" class="h6">Pilih User Terlebih Dahulu</div>
                                        </div>
                                        <div v-else>
                                            <div class="" v-for="debt in userdebt.data" :key="debt.id">
                                                <input v-model="form.debt" type="checkbox"
                                                    @change="totalize($event, debt.id)" :value="debt.id" />
                                                <label class="ml-2">
                                                    {{ debt.title }} | {{ debt.due_date }}
                                                    <span class="text-right text-monospace">
                                                        <div :class="'badge badge-' + debt.color">{{
                                                            (pay_status.find(obj => obj.value === debt.payment_status)).name
                                                        }}</div>
                                                        {{
                                                            accounting.formatMoney(
                                                                debt.remainder, "Rp", 0) }}
                                                    </span>
                                                </label>
                                            </div>
                                            <Bootstrap4Pagination :data="userdebt" @pagination-change-page="getUserdebt" />
                                            <input type="text" class="d-none is-invalid">
                                            <span class="invalid-feedback">{{ errors.debt_id }}</span>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User</label>
                                        <VueMultiselect v-model="form.user" :option-height="9" @input="userchange"
                                            @remove="userchange" @select="userchange" :options="users"
                                            :class="{ 'is-invalid': errors.user }" :close-on-select="true"
                                            placeholder="Pilih Satu / Lebih" label="name" track-by="id"
                                            :show-labels="false">
                                            <template v-slot:option="{ option }">
                                                <div>{{ option.name }} - {{ option.id }} </div>
                                            </template>
                                        </VueMultiselect>
                                        <span class="invalid-feedback">{{ errors.user }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Wallet</label>
                                        <VueMultiselect v-model="form.wallet_id" :option-height="9" :options="wallets"
                                            :class="{ 'is-invalid': errors.wallet_id }" :close-on-select="true"
                                            placeholder="Pilih Satu / Lebih" label="wallet_name" track-by="id"
                                            :show-labels="false">
                                            <template v-slot:option="{ option }">
                                                <div>{{ option.wallet_name }} - {{ option.id }} </div>
                                            </template>
                                        </VueMultiselect>

                                        <span class="invalid-feedback">{{ errors.wallet_id }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Payment</label><br>
                                        <span>Total Debt : {{ accounting.formatMoney(total, "Rp", 0) }}</span>
                                        <input type="number" v-model="form.payment" @keyup="handleChange"
                                            :class="{ 'is-invalid': errors.payment }" class="form-control" id="time" />
                                        <span class="invalid-feedback">{{ errors.payment }}</span>
                                        <span>{{ formatted }}</span><br>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                                </div>
                            </div>
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
                                    <th>Title</th>
                                    <th>Wallet</th>
                                    <th>Pay</th>
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
