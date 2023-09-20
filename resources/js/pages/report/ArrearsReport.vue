<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate, formatDiff, formatMoney, formatClass, formatStatus } from '../../helper.js';

const toastr = useToastr();
const searchQuery = ref();
const accounts = ref([]);
const billing = ref([]);
const debts = ref({
    'data': []
});

const form = ref({
    'start': null,
    'end': null,
    'length': 0,
    'account': []
});
const errors = ref({
    'start': null,
    'end': null,
    'length': null,
    'account': null
});

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
    if (form.value.start == null) {
        errors.value.start = 'Pilih awal periode !';
        err += 1;
    }
    if (form.value.end == null) {
        errors.value.end = 'Pilih akhir periode !'
        err += 1;
    }

    if (form.value.length == 0) {
        errors.value.length = 'Pilih banyak tunggakan yang dimuat !'
        err += 1;
    }

    if (new Date(form.value.start) > new Date(form.value.end)) {
        errors.value.start = 'Periode awal harus lebih kecil dari periode akhir !'
        err += 1;
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const billing_form = (event) => {
    console.log('form submitted');
    event.preventDefault();
    if (valid()) {
        search_bill();
    }
}

const search_bill = (page = 1) => {
    // buat api call untuk dapetin anak yg beum bayar
    axios.get(`/api/ledger/billing/search?page=${page}`, {
        params: {
            start: form.value.start,
            end: form.value.end,
            size: form.value.size,
            search: searchQuery.value,
            length: form.value.length,
            account: JSON.stringify(form.value.account)
        }
    })
        .then((response) => {
            billing.value = response.data;
        })

}



const getAccount = () => {

    axios.get(`/api/account/only`, {
        params: {
            type: 2
        }
    }).then((response) => {
        accounts.value = response.data;
        console.log(accounts.value);
    })
}


const getDebt = (page = 1) => {
    axios.get(`/api/debt?page=${page}`)
        .then((response) => {
            debts.value = response.data;
        })

}

watch(searchQuery, debounce(() => {
    search_bill();
}, 300));


onMounted(() => {
    getDebt();
    getAccount();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Tunggakan</strong></h1>
                    <p><small>Laporan tunggakan </small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/">Beranda</router-link></li>
                        <li class="breadcrumb-item active">Tunggakan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 border-right">
                    <h4 class="mt-3">Tagihan</h4>
                    <form @submit="billing_form">
                        <div class="row">
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <label>Periode Awal</label>
                                    <input :class="{ 'is-invalid': errors.start }" v-model="form.start" type="month"
                                        class="form-control" placeholder="Periode Awal">
                                    <span class="invalid-feedback">{{ errors.start }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <label>Periode Akhir</label>
                                    <input :class="{ 'is-invalid': errors.end }" v-model="form.end" type="month"
                                        class="form-control" placeholder="Periode Akhir">
                                    <span class="invalid-feedback">{{ errors.end }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <label>Menunggak berapa bulan</label>
                                    <select :class="{ 'is-invalid': errors.length }" v-model="form.length"
                                        class="form-control">
                                        <option value="10" disabled>Pilih berapa bulan menunggak</option>
                                        <option v-for="i in 12" :value="i">{{ i }} Bulan</option>
                                    </select>
                                    <span class="invalid-feedback">{{ errors.length }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 ">
                                <div v-for="account in accounts" :key="account.id" class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" :value="account.id"
                                        v-model="form.account">
                                    <label class="form-check-label" for="inlineCheckbox1">{{ account.account_name }}</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mr-md-1 w-100">
                            <i class="fa fa-plus-circle mr-1"></i>
                            Buat Laporan
                        </button>
                    </form>
                    <div class="mt-2 mb-2 w-25">
                        <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                    </div>
                    <table class="display table w-100 table-hover " style="overflow: auto;width:100%">
                        <!-- <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Tunggakan</th>
                                <th>Sisa Tunggakan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead> -->
                        <tbody v-if="billing.length != 0">
                            <template v-for="(data) in billing" :key="data.id">

                                <tr data-widget="expandable-table" aria-expanded="false">
                                    <td> <i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
                                        {{ data.fullname + ' [ ' +
                                            data.bill_count + ' Bulan ] :' + formatMoney(data.sum_remain) }}
                                    </td>

                                </tr>
                                <tr class="expandable-body d-none">
                                    <td>
                                        <div class="p-0" style="display: none;">
                                            <table class="w-100">
                                                <tr>
                                                    <th>Periode</th>
                                                    <th>Tagihan</th>
                                                    <th>Sisa</th>
                                                </tr>
                                                <tr v-for="bill in data.bill">
                                                    <td>{{ bill.due_date }}</td>
                                                    <td>{{ formatMoney(bill.sum_amount) }}</td>
                                                    <td>{{ formatMoney(bill.sum_remain) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>

                                </tr>
                            </template>

                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="text-center">Data Tidak Ditemukan...</td>
                            </tr>
                        </tbody>
                    </table>


                </div>
                <div class="col-md-6">

                    <h4 class="mt-3">Hutang</h4>
                    <p>Debitur : Yang berhutang</p>
                    <table class="text-navy table-sm table-bordered table-hover w-100">
                        <thead>
                            <tr>

                                <th>Debitur</th>
                                <th>Jumlah Hutang</th>
                                <th>Sisa Hutang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody v-if="debts.data.length > 0">
                            <tr v-for="(data) in debts.data" class="text-center" :key="data.id">

                                <td>{{ data.santri.fullname }}</td>
                                <td>{{ formatMoney(data.amount) }}</td>
                                <td>{{ formatMoney(data.remainder) }}</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="text-center">Data tidak ditemukan...</td>
                            </tr>
                        </tbody>
                    </table>
                    <Bootstrap4Pagination :data="debts" @pagination-change-page="getDebt" />
                </div>

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
