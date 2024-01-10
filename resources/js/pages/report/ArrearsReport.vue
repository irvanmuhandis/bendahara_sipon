<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import moment from "moment";
import { useToastr } from '../../toastr.js';
import { debounce, forEach, now } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate, formatDiff, formatMoney, formatClass, formatStatus } from '../../helper.js';
import { jsPDF } from "jspdf";
import html2canvas from 'html2canvas';

const toastr = useToastr();
const searchQuery = ref();
const searchQuery_debt = ref();
const searchQuery_billnonperiod = ref();
const accounts = ref([]);
const santris = ref([]);
const billing = ref({
    'data': [],
    'sum': null
});
const debts = ref({
    'data': [],
    'sum': null
});
const bills_nonperiod = ref({
    'data': [],
    'sum': null
});
const isLoading = ref(false);
const date = ref({
    'word': null,
    'month': null,
    'number': null,
    'year': null
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
const formPdf = ref({
    'santri': null,
    'num': null,
    'billing': {
        'sum': null
    },
    'start': null,
    'end': null
});
const errorsFormPdf = ref({
    'santri': null,
    'num': null,
    'billing': null,
    'start': null,
    'end': null
});


const getDate = () => {
    const today = new Date();
    let options = { year: 'numeric', month: 'long', day: 'numeric' };
    date.value.word = today.toLocaleDateString('IN', options);

    const dates = moment().format('YYYY-MM');

    options = { year: 'numeric', month: 'long' };
    let months = today.toLocaleDateString('IN', options);
    let now = today.toLocaleDateString('IN', options);

    date.value.number = dates;
    formPdf.value.start = dates;
    formPdf.value.end = dates;
    date.value.month = months;

    date.value.year = moment().format('YYYY');
    console.log(date.value);
}


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
    isLoading.value = true;

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
        }).finally(() => {
            isLoading.value = false;
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


const getDebt = () => {
    isLoading.value = true;
    axios.get(`/api/debt/santri`, {
        params: {
            search: searchQuery_debt.value
        }
    })
        .then((response) => {
            debts.value = response.data;
        }).finally(() => {
            isLoading.value = false;
        })

}

const getBillNonPeriod = () => {
    isLoading.value = true;
    axios.get(`/api/bill/santri`, {
        params: {
            search: searchQuery_billnonperiod.value
        }
    })
        .then((response) => {
            bills_nonperiod.value = response.data;
        }).finally(() => {
            isLoading.value = false;
        })

}

const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
    } catch (error) {
        console.error(error);
    }
}

const generateBilling = () => {
    axios.get(`/api/pdf/santri`, {
        params: {
            santri: formPdf.value.santri==null?null:formPdf.value.santri.nis,
            start: formPdf.value.start,
            end: formPdf.value.end,
        }
    })
        .then((response) => {
            formPdf.value.billing = response.data;
        });

}

const generatePdf = () => {
    const doc = new jsPDF({
        orientation: 'p',
        unit: 'px',
        format: 'a4',
        hotfixes: ['px_scaling'],
    });

    const a4Width = doc.internal.pageSize.getWidth();
    const a4Height = doc.internal.pageSize.getHeight();



    // Set the scale factor to achieve A4 size (adjust as needed)
    const scale = a4Width / document.getElementById('pdf-content').offsetWidth;

    html2canvas(document.querySelector('#pdf-content'), {
        width: a4Width,
        height: a4Height,
    }).then((canvas) => {
        let img = canvas.toDataURL('image/PNG', 1);
        // Calculate A4 size in pixels
        const pdfWidth = a4Width;
        const pdfHeight = a4Height;


        doc.addImage(img, 'PNG', 10, 10, pdfWidth, pdfHeight);
        doc.save("Tunggakan_"+((formPdf.value.santri==null||formPdf.value.santri=="")?"Kosong":formPdf.value.santri.fullname)+"_"+formPdf.value.start+"_"+formPdf.value.end+".pdf");
    });



}


watch(searchQuery, debounce(() => {
    search_bill();
}, 300));
watch(searchQuery_billnonperiod, debounce(() => {
    getBillNonPeriod();
}, 300));
watch(searchQuery_debt, debounce(() => {
    getDebt();
}, 300));


//watch(billing, debounce(() => {
//    formFill();
//}, 300));

let json_fields = {
    'NIS': 'nis',
    'Nama': 'fullname',
    'Jumlah Bulan': 'bill_count',
    'Sisa Tagihan': 'sum_remain',
    'Tagihan Total': 'sum_amount',
};

onMounted(() => {
    getDate();
    getDebt();
    getBillNonPeriod();
    getAccount();
    getSantri();
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
                <div class="col p-0 m-0 shadow-none card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                            <li class="nav-item">
                                <a class=" nav-link active" id="custom-tabs-four-period-tab" data-toggle="pill"
                                    href="#custom-tabs-four-period" role="tab" aria-controls="custom-tabs-four-period"
                                    aria-selected="false">Tagihan Periodik &#x1F4C5;</a>
                            </li>
                            <li class="nav-item">
                                <a class="border-left border-gray nav-link" id="custom-tabs-three-nonperiod-tab"
                                    data-toggle="pill" href="#custom-tabs-three-nonperiod" role="tab"
                                    aria-controls="custom-tabs-four-nonperiod" aria-selected="false">Tagihan Non Periodik
                                    &#x1F4CC;</a>
                            </li>
                            <li class="nav-item">
                                <a class="border-left border-small border-gray nav-link" id="custom-tabs-two-debt-tab"
                                    data-toggle="pill" href="#custom-tabs-two-debt" role="tab"
                                    aria-controls="custom-tabs-four-debt" aria-selected="false">Pinjaman &#x1F4B8;</a>
                            </li>
                            <li class="border-left border-gray nav-item">
                                <a class="nav-link" id="custom-tabs-one-pdf-tab" data-toggle="pill"
                                    href="#custom-tabs-one-pdf" role="tab" aria-controls="custom-tabs-four-debt"
                                    aria-selected="false">Export PDF &#x1F4D5;</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-four-tabContent">

                            <div class="tab-pane fade active show" id="custom-tabs-four-period" role="tabpanel"
                                aria-labelledby="custom-tabs-four-period-tab">
                                <form @submit="billing_form">
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <label>Periode Awal</label>
                                                <input :class="{ 'is-invalid': errors.start }" v-model="form.start"
                                                    type="month" class="form-control" placeholder="Periode Awal">
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
                                                <label>Minimal Menunggak</label>
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
                                            <div v-for="account in accounts" :key="account.id"
                                                class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" :value="account.id"
                                                    v-model="form.account">
                                                <label class="form-check-label" for="inlineCheckbox1">{{
                                                    account.account_name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-md-1 w-100">
                                        <i class="fa fa-plus-circle mr-1"></i>
                                        Buat Rekapan
                                    </button>
                                </form>

                                <div class="mt-2 mb-2 w-100" v-if="billing.data.length != 0">
                                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />

                                    <div
                                        class="justify-content-between rounded mt-2 mb-2 p-0 d-inline-flex border-gray border w-100">
                                        <div class="d-flex text-center text-bold ml-2">
                                            Total Tagihan : {{ formatMoney(billing.sum) }}
                                        </div>
                                        <div class="bg-success  p-2 h-100">
                                            <ExcelVue type="xlsx" :export-fields="json_fields" worksheet="My Worksheet"
                                                :name="'Tunggakan_Putri_' + form.start + '_' + form.end + '.xlsx'"
                                                :data="billing.data">

                                                <a href="#" class="text-light m-0 text-decoration-none">
                                                    Download Excel
                                                    &#x1F4C3;</a>
                                            </ExcelVue>
                                        </div>
                                    </div>
                                </div>
                                <table class="display table w-100 table-hover " style="overflow: auto;width:100%">
                                    <tbody v-if="isLoading">
                                        <tr>
                                            <td class="text-center m-2">
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <template v-else>
                                        <tbody v-if="billing.data.length != 0">

                                            <template v-for="(data) in billing.data" :key="data.nis">

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
                                                                    <td>{{ bill.month }}</td>
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
                                                <td colspan="6" class="text-center">&#x1F64F; Data Tidak Ditemukan...</td>
                                            </tr>
                                        </tbody>


                                    </template>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="custom-tabs-three-nonperiod" role="tabpanel"
                                aria-labelledby="custom-tabs-four-nonperiod-tab">
                                <div class="mt-2 mb-2 w-100">
                                    <label>Nama Santri</label>
                                    <input type="text" v-model="searchQuery_billnonperiod" class="form-control"
                                        placeholder="Siti..." />
                                </div>
                                <span class="mt-2 mb-2 text-bold">Total Tagihan : {{ formatMoney(bills_nonperiod.sum)
                                }}</span>
                                <table class="text-navy table table-hover w-100">
                                    <tbody v-if="isLoading">
                                        <tr>
                                            <td class="text-center m-2">
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <template v-else>
                                        <tbody v-if="bills_nonperiod.data.length > 0">
                                            <template v-for="(data) in bills_nonperiod.data" :key="data.nis">
                                                <tr data-widget="expandable-table" aria-expanded="false">
                                                    <td> <i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
                                                        {{ data.fullname + ' [ ' +
                                                            data.bill.length + ' Hutang ] :' + formatMoney(data.sum_amount) }}
                                                    </td>

                                                </tr>
                                                <tr class="expandable-body d-none">
                                                    <td>
                                                        <div class="p-0" style="display: none;">
                                                            <table class="w-100">
                                                                <tr>
                                                                    <th>Tanggal</th>
                                                                    <th>Judul</th>
                                                                    <th>Jumlah</th>
                                                                    <th>Sisa</th>
                                                                </tr>
                                                                <tr v-for="bill in data.bill">
                                                                    <td>{{ formatDate(bill.created_at) }}</td>
                                                                    <td>{{ bill.title }}</td>
                                                                    <td>{{ formatMoney(bill.amount) }}</td>
                                                                    <td>{{ formatMoney(bill.remainder) }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </td>

                                                </tr>
                                            </template>
                                        </tbody>
                                        <tbody v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">&#x1F64F; Data tidak ditemukan...</td>
                                            </tr>
                                        </tbody>


                                    </template>
                                </table>

                            </div>

                            <div class="tab-pane fade" id="custom-tabs-two-debt" role="tabpanel"
                                aria-labelledby="custom-tabs-four-debt-tab">
                                <div class="mt-2 mb-2 w-100">
                                    <label>Nama Santri</label>
                                    <input type="text" v-model="searchQuery_debt" class="form-control"
                                        placeholder="Nur..." />
                                </div>
                                <span class="mt-2 mb-2 text-bold">Total Piutang : {{ formatMoney(debts.sum) }}</span>
                                <table class="text-navy table table-hover w-100">
                                    <tbody v-if="isLoading">
                                        <tr>
                                            <td class="text-center m-2">
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                                <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <template v-else>



                                        <tbody v-if="debts.data.length > 0">

                                            <template v-for="(data) in debts.data" :key="data.nis">

                                                <tr data-widget="expandable-table" aria-expanded="false">
                                                    <td> <i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
                                                        {{ data.fullname + ' [ ' +
                                                            data.debt.length + ' Hutang ] :' + formatMoney(data.sum_amount) }}
                                                    </td>

                                                </tr>
                                                <tr class="expandable-body d-none">
                                                    <td>
                                                        <div class="p-0" style="display: none;">
                                                            <table class="w-100">
                                                                <tr>
                                                                    <th>Tanggal</th>
                                                                    <th>Jumlah</th>
                                                                    <th>Sisa</th>
                                                                </tr>
                                                                <tr v-for="debt in data.debt">
                                                                    <td>{{ formatDate(debt.created_at) }}</td>
                                                                    <td>{{ formatMoney(debt.amount) }}</td>
                                                                    <td>{{ formatMoney(debt.remainder) }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </td>

                                                </tr>
                                            </template>
                                        </tbody>
                                        <tbody v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">&#x1F64F; Data tidak ditemukan...</td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="custom-tabs-one-pdf" role="tabpanel"
                                aria-labelledby="custom-tabs-four-nonperiod-tab">
                                <div class="mt-2 mb-2 w-100 row">
                                    <div class="col">
                                        <label>Nama Santri</label>
                                        <div v-if="santris.length == 0" class="w-100 text-center">
                                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                                        </div>
                                        <VueMultiselect v-else v-model="formPdf.santri" :option-height="9"
                                            :options="santris" :class="{ 'is-invalid': errorsFormPdf.santri }"
                                            :multiple="false" @select="generateBilling" :close-on-select="true"
                                            placeholder="Pilih Satu  " label="fullname" track-by="nis" :show-labels="false">
                                            <template v-slot:option="{ option }">
                                                <div>{{ option.fullname }} - {{ option.nis }} </div>
                                            </template>
                                        </VueMultiselect>
                                    </div>
                                    <div class="col ">
                                        <div class="form-group">
                                            <label>Nomor Surat</label>
                                            <input type="text" v-model="formPdf.num"
                                                :class="{ 'is-invalid': errorsFormPdf.num }" class="form-control"
                                                placeholder="57/SPb/KGS-PI/XII/2023" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label>Periode Awal</label>
                                            <input @change="generateBilling" :class="{ 'is-invalid': errorsFormPdf.start }"
                                                v-model="formPdf.start" type="month" class="form-control"
                                                placeholder="Periode Awal">
                                            <span class="invalid-feedback">{{ errorsFormPdf.start }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label>Periode Akhir</label>
                                            <input @change="generateBilling" :class="{ 'is-invalid': errorsFormPdf.end }"
                                                v-model="formPdf.end" type="month" class="form-control"
                                                placeholder="Periode Akhir">
                                            <span class="invalid-feedback">{{ errorsFormPdf.end }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button class="w-100  btn btn-primary" type="button" @click="generatePdf">Buat PDF</button>

                                <div class="d-flex justify-content-center">


                                    <div id="pdf-content" style="height:1000px;width:780px ;"
                                        class="overflow-auto card m-2 shadow p-5 text-times">
                                        <!-- header -->
                                        <div class="row m-1 ml-3" style="opacity: 0.5;">
                                            <div class="col">
                                                <div class="">
                                                    <img src="/images/kop_surat.png" style="width: 400px; object-fit: fill;"
                                                        class="img-fluid ">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row ml-2 mr-2" style="opacity: 0.5;">
                                                <div class="col w-100">
                                                    <div style="margin-bottom: 2px;" class="border-bottom border-dark"></div>
                                                    <div style="border-width:5px !important;" class="border-bottom border-dark"></div>
                                                    <div style="margin-top: 2px;" class="border-bottom border-dark mb-1"></div>
                                                </div>
                                            </div>
                                        <div class="row m-2">
                                            <div class="col">
                                                <div class="">
                                                    <table class="table-sm table-borderless p-0">
                                                        <tbody>
                                                            <tr class="p-0">
                                                                <td class="p-0" scope="row">No</td>
                                                                <td class="p-0 pl-1">:</td>
                                                                <td class="p-0 pl-1">{{
                                                                    formPdf.num == null ? "57/SPb/KGS-PI/XII/2023" :
                                                                    formPdf.num
                                                                }}</td>
                                                            </tr>
                                                            <tr class="p-0">
                                                                <td class="p-0" scope="row">Hal</td>
                                                                <td class="p-0 pl-1">:</td>
                                                                <td class="p-0 pl-1 text-bold">Pemberitahuan
                                                                    Tunggakan</td>
                                                            </tr>
                                                            <tr class="p-0">
                                                                <td class="p-0" scope="row">Lamp</td>
                                                                <td class="p-0 pl-1">:</td>
                                                                <td class="p-0 pl-1">-</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col text-right">
                                                <span>Semarang, {{ date.word }}</span>
                                            </div>
                                        </div>
                                        <!-- Salam -->
                                        <div class="row m-2">
                                            <div class="col">
                                                Yth. Wali Santri {{ formPdf.santri == null ? '' :
                                                    formPdf.santri.fullname
                                                }}<br>di
                                                Tempat
                                                <br><br>
                                                <strong><i>Assalamu'alaikum Warahmatullahi Wabarakatuh</i></strong>
                                                <br>
                                            </div>
                                        </div>
                                        <!-- isi -->
                                        <div class="row m-2">
                                            <div class="col text-justify">
                                                <span>
                                                    Bersama surat ini kami memberitahukan bahwa menurut catatan
                                                    administrasi, santri yang bersangkutan
                                                    memiliki tunggakan administrasi syahriyah pondok sebagai
                                                    berikut:
                                                </span>
                                                <br><br>
                                                <span>
                                                    <table class="table-sm table-borderless p-0">
                                                        <tbody>
                                                            <tr class="p-0">
                                                                <td class="p-0" scope="row">Nama</td>
                                                                <td class="p-0 pl-1">:</td>
                                                                <td class="p-0 pl-1 text-bold">{{
                                                                    formPdf.santri == null ? '' :
                                                                    formPdf.santri.fullname }}
                                                                </td>
                                                            </tr>
                                                            <tr class="p-0">
                                                                <td class="p-0" scope="row">Jumlah Tunggakan</td>
                                                                <td class="p-0 pl-1">:</td>
                                                                <td class="p-0 pl-1"><strong> {{
                                                                    formPdf.billing.sum == null ? "-" : formatMoney
                                                                        (formPdf.billing.sum) }} </strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </span>
                                                <br>
                                                <span>
                                                    Rekapan tunggakan terhitung dari Bulan
                                                    <strong>{{ new Date(formPdf.start).toLocaleDateString('IN', {
                                                        year:
                                                            'numeric', month: 'long'
                                                    }) }}</strong> sampai Bulan
                                                    <strong>{{ new Date(formPdf.end).toLocaleDateString('IN', {
                                                        year:
                                                            'numeric', month: 'long'
                                                    }) }}</strong>. Kami mohon sekiranya
                                                    Bapak / Ibu dapat segera melunasi tunggakan tersebut. Informasi
                                                    lebih
                                                    lanjut dapat menghubungi 0823-3392-2366 a.n. Saniyatin Nibroniyah.
                                                </span>
                                                <br>
                                                <span>
                                                    Demikian pemberitahuan ini kami sampaikan, atas perhatian Bapak
                                                    / Ibu
                                                    kami ucapkan terima kasih.
                                                </span>
                                                <br><br>
                                                <strong><i>Wassalamu’alaikum Warahmatullahi Wabarokatuh
                                                    </i></strong>
                                                <br>
                                                <br>
                                                <div class="text-center w-100">Mengetahui,</div>
                                            </div>
                                        </div>

                                        <!-- TTD -->
                                        <div class="row m-2">
                                            <div class="col text-center">
                                                <div class="pb-5 mb-5">Lurah Pondok Putri</div>
                                                <div class=""><u><strong>Lulu Laeli Ramdany</strong></u></div>
                                            </div>
                                            <div class="col text-center">
                                                <div class="pb-5 mb-5">Bendahara Pondok Putri</div>
                                                <div class=""><u><strong>Saniyatin Nibroniyah, S.Kep, AH</strong></u>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                </div>

                            </div>
                        </div>
                    </div>






                </div>
            </div>
    </div>
</div></template>

