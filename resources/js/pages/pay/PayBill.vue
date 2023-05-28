<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm } from 'vee-validate';

import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import accounting from 'accounting';
import { formatDate } from '../../helper';

const toastr = useToastr();

const users = ref([]);
const wallets = ref([]);
const total = ref(0);

const remainder = ref([]);
const userbill = ref({ 'data': [] });
const pay_status = ref([]);
const formatted = ref();
const isLoading = ref(true);
const init = ref(true);
const errors = ref({
    'user': null,
    'bill': null,
    'date': null,
    'wallet': null,
    'payment': null
});
const form = ref({
    'user': null,
    'bill': [],
    'date': new Date(new Date().getTime() + 7 * 60 * 60 * 1000).toISOString().slice(0, 16),
    'wallet': null,
    'payment': null
});



const checkLength = computed(() => {
    return form.value.bill.length;
});
const totalize = (event, id) => {

    const item = userbill.value.data.find((item) => item.id === id);
    if (event.target.checked) {
        total.value += item.bill_remainder;
        remainder.value.push(item.bill_remainder);
    }
    else {
        total.value -= item.bill_remainder;
        remainder.value.unshift(item.bill_remainder);
    }

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
        const response = await axios.get(`/api/wallet/list`)
        wallets.value = response.data;
        console.log('wallet added');
    } catch (error) {
        console.error(error);
    }
}

const getUserbill = async (page = 1) => {
    console.log(form.value.bill);
    try {
        const response = await axios.get(`/api/user/bill/${form.value.user.id}?page=${page}`);
        userbill.value = response.data;
        isLoading.value = false;
    } catch (error) {
        console.error(error);
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

const validateBill = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.user == null) {
        errors.value.user = 'Pilih User ';
        err += 1;
    }
    if (form.value.bill.length == 0) {
        errors.value.bill = 'Pilih Bill '
        err += 1;
    }
    if (form.value.payment == null) {
        errors.value.payment = 'Masukkan jumlah pembayaran '
        err += 1;
    }
    if (form.value.wallet == null) {
        errors.value.wallet = 'Pilih dompet '
        err += 1;
    }
    if (form.value.date == null) {
        errors.value.date = 'Pilih tanggal '
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

const createPay = (event) => {
    event.preventDefault();
    form.value.remainder = remainder.value;
    form.value.user = form.value.user.id;
    form.value.wallet = form.value.wallet.id;
    if (validateBill()) {
        axios.post('/api/pay/bill', form.value)
            .then(() => {
                isLoading.value = true;
                userbill.value = [];
                total.value = 0;
                form.value.bill = [];
                form.value.payment = null;
                formatted.value = null;
                toastr.success('Pay created successfully!');
                getPayBill();
            })
            .catch((error) => {
                console.log(error);
                toastr.error(error);
            })
    }
};


const userchange = (event) => {
    isLoading.value = true;
    init.value = false;
    console.log('user changed');
    total.value = 0;
    form.value.bill = [];
    remainder.value = [];
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
    axios.get(`/api/paybill`)
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
                    {
                        data: 'created_at',
                        render: function (data) {
                            return formatDate(data);
                        }
                    },
                    { data: "name" },
                    { data: "payment" },
                    { data: "wallet_name" },
                    { data: "account_name" },
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
                    <RouterLink to="/admin/income"><i class="fa fa-arrow-left"></i><strong> Kembali</strong></RouterLink>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/dashboard">Home</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/income">Income</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Bayar Tagihan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 ">

                    <form @submit="createPay">
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>Bill</label>
                                    <div v-if="checkLength != 0">
                                        <i> {{ checkLength }} bill dipilih</i>
                                    </div>
                                    <div v-if="isLoading" class="text-center">
                                        <div class="spinner-border row text-primary mx-auto" role="status"></div>
                                        <div v-if="init" class="h6">Pilih User Terlebih Dahulu</div>
                                    </div>
                                    <div v-else>
                                        <div v-for="bill in userbill.data" :key="bill.id">
                                            <input type="checkbox" @change="totalize($event, bill.id)" v-model="form.bill"
                                                :value="bill.id" />
                                            <label class="ml-2">
                                                {{ bill.account_name }} | {{ bill.due_date }}
                                                <span class="text-right text-monospace">
                                                    <div :class="'badge badge-' + bill.color">{{
                                                        (pay_status.find(obj => obj.value === bill.payment_status)).name
                                                    }}</div>
                                                    {{
                                                        accounting.formatMoney(
                                                            bill.bill_remainder, "Rp", 0) }}
                                                </span>
                                            </label>
                                        </div>
                                        <Bootstrap4Pagination :data="userbill" @pagination-change-page="getUserbill" />
                                        <input type="text" class="d-none is-invalid">
                                        <span class="invalid-feedback">{{ errors.bill }}</span>
                                    </div>
                                </div>


                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>User</label>
                                    <VueMultiselect v-model="form.user" :option-height="9" @input="userchange" :multiple="false"
                                        @remove="userchange" @select="userchange" :options="users"
                                        :class="{ 'is-invalid': errors.user }" :close-on-select="true"
                                        placeholder="Pilih Satu" label="name" track-by="id" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.name }} - {{ option.id }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.user }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Wallet</label>
                                    <VueMultiselect v-model="form.wallet" :option-height="9" :options="wallets" :multiple="false"
                                        :class="{ 'is-invalid': errors.wallet }" :close-on-select="true"
                                        placeholder="Pilih Satu" label="wallet_name" track-by="id"
                                        :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.wallet_name }} - {{ option.id }} </div>
                                        </template>
                                    </VueMultiselect>

                                    <span class="invalid-feedback">{{ errors.wallet }}</span>
                                </div>
                                <div class="form-group">
                                    <label for="description">Date</label>
                                    <input :class="{ 'is-invalid': errors.date }" class="form-control" v-model="form.date"
                                        type="datetime-local" />
                                    <span class="invalid-feedback">{{ errors.date }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Payment</label><br>
                                    <span>Total Bill : {{ accounting.formatMoney(total, "Rp", 0) }}</span>

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

            <div class="row">
                <div class="col-12 ">
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
</template>


