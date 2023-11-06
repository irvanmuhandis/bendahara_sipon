<script setup >
import { formatMoney, formatDate, formatDay, formatBg } from '../helper';
import axios from 'axios';
import { ref, onMounted, reactive, computed, watch } from 'vue';
import moment from 'moment';
import { debounce, sum } from 'lodash';
import Chart from "chart.js/auto";
import { now, round } from 'lodash';

const status = ref();
const date = ref({
    'word': null,
    'month': null,
    'number': null,
    'year': null
});
const sums = ref({
    'income_bill': null,
    'income_debt': null,
    'income_other': null,
    'expense_debt': null,
    'expense_other': null,
    'bill_potential': null,
    'debt_potential': null
})
const inoutData = ref();
const bills = ref();
const debt = ref();
const other = ref();
const stat = ref({
    'debt': null,
    'bill': null,
    'santri': null,
    'dispen': null
})
const entryData = ref({
    'santri': 0,
    'dispen': 0,
    'debt': 0,
    'bill': 0
});
const mode = ref({
    'strt': 1,
    'end': 1
});
const form = ref({
    'start': '',
    'end': ''
});
const errors = ref({
    'start': null,
    'end': null
});
const incomeTable = ref({
    'other': false,
    'debt': false,
    'bill': false
});
const expenseTable = ref({
    'other': false,
    'debt': false
});



function getStatus() {
    const params = {
        'start': form.value.start,
        'end': form.value.end,
    };
    axios.get('/status-message')
        .then(response => {
            if (response.data) {
                // Update the Vue component's data with the status message
                status.value = response.data;
            }
        })
        .catch(error => {
            console.error(error);
        });

    axios.get('/api/accsum', {
        params
    }
    )
        .then(response => {
            if (response.data) {
                // Update the Vue component's data with the status message
                const datas = response.data;
                bills.value = datas.bill;
                debt.value = datas.debt;
                other.value = datas.other;
                sums.value.income_bill = datas.income_bill;
                sums.value.income_debt = datas.income_debt;
                sums.value.income_other = datas.income_other;
                sums.value.expense_debt = datas.expense_debt;
                sums.value.expense_other = datas.expense_other;
                sums.value.bill_potential = datas.bill_potential;
                sums.value.debt_potential = datas.debt_potential
            }
        })
        .catch(error => {
            console.error(error);
        });

    axios.get('/api/stat')
        .then(response => {
            if (response.data) {
                const datas = response.data;
                stat.value.bill = datas.bill;
                stat.value.debt = datas.debt;
                stat.value.santri = datas.santri;
                stat.value.dispen = datas.dispen;
            }
        })
        .catch(error => {
            console.error(error);
        });

}
const getDate = () => {
    const today = new Date();
    let options = { year: 'numeric', month: 'long', day: 'numeric' };
    date.value.word = today.toLocaleDateString('IN', options);

    const dates = moment().format('DD/MM/YY');

    options = { year: 'numeric', month: 'long' };
    let months = today.toLocaleDateString('IN', options);
    let now = today.toLocaleDateString('IN', options);

    date.value.number = dates;
    if (form.value.start == '') {
        date.value.month = months;
    } else {
        var start = form.value.start.split('-');
        var end = form.value.end.split('-');
        start = new Date(start[0], start[1] - 1);
        end = new Date(end[0], end[1] - 1);
        date.value.month = (start.toLocaleDateString('IN', options)) + " ~ " + end.toLocaleDateString('IN', options);
    }
    date.value.year = moment().format('YYYY');
    console.log(date.value);
}


const graph = () => {

    var def = {
        'pay': [
            {
                "sum": "0",
                "date": date.value.month
            }],
        'debt': [
            {
                "debt": "0",
                "date": date.value.month
            }
        ],
        'trans':
            [
                {
                    "sum_debit": "0",
                    "sum_credit": "0",
                    "date": date.value.month
                }
            ]
    };

    var paybill = inoutData.value.paybill.length == 0 ? def.pay : inoutData.value.paybill;
    var labelpb = paybill.map(function (e) {
        return e.date;
    });
    var dataspb = paybill.map(function (e) {
        return e.sum;
    });

    var paydebt = inoutData.value.paydebt.length == 0 ? def.pay : inoutData.value.paydebt;
    var labelpd = paydebt.map(function (e) {
        return e.date;
    });
    var dataspd = paydebt.map(function (e) {
        return e.sum;
    });

    var trans = inoutData.value.trans.length == 0 ? def.trans : inoutData.value.trans;
    var labelt = trans.map(function (e) {
        return e.date;
    });
    var datast_deb = trans.map(function (e) {
        return e.sum_debit;
    });
    var datast_cred = trans.map(function (e) {
        return e.sum_credit;
    });

    var debt = inoutData.value.debt.length == 0 ? def.debt : inoutData.value.debt;
    var labeld = debt.map(function (e) {
        return e.date;
    });
    var datasd = debt.map(function (e) {
        return e.debt;
    });

    // console.log(getInout(params));
    console.log(inoutData.value);

    const ctx = document.getElementById('myChart');
    const data = {
        datasets: [{
            data: dataspb,
            fill: true,
            backgroundColor: '#011f4b',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Pembayaran Tagihan'
        },
        {
            data: dataspd,
            fill: true,
            backgroundColor: '#03396c',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Pembayaran Hutang'
        },
        {
            data: datast_deb,
            fill: true,
            backgroundColor: '#3FB1C1',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Transaksi'
        }],
        labels: labelpb,
        options: {
            responsive: true,
            resizeDelay: 200,
            parsing: {
                xAxisKey: 'id',
                yAxisKey: 'id'
            }
        }
    };
    let chartStatus = Chart.getChart("myChart"); // <canvas> id
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }
    new Chart(ctx, {
        type: 'bar',
        data: data
    });

    const ctx2 = document.getElementById('myChart2');
    const data2 = {
        datasets: [{
            data: datasd,
            fill: true,
            backgroundColor: '#FF4433',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Hutang'
        },
        {
            data: datast_cred,
            fill: true,
            backgroundColor: '#A52A2A',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Transaksi'
        }],
        labels: labeld,
        options: {
            parsing: {
                xAxisKey: 'id',
                yAxisKey: 'id'
            }
        }
    };
    chartStatus = Chart.getChart("myChart2"); // <canvas> id
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }
    new Chart(ctx2, {
        type: 'bar',
        data: data2
    });
}

function getInout() {
    getDate();
    const params = {
        'start': form.value.start,
        'end': form.value.end,
    };

    axios.get('/api/inout', {
        params
    }).then(response => {
        inoutData.value = response.data;
        getStatus();
        graph();
    })
        .catch(error => {
            console.error(error);
        });
};
const inoutForm = (event) => {
    event.preventDefault();
    var err = 0;
    errors.value.start = null;
    errors.value.end = null;
    if (form.value.start == '') {
        errors.value.start = 'Masukkan Bulan Awal !';
        err += 1;
    }
    if (form.value.end == '') {
        errors.value.end = 'Masukkan Bulan Akhir !';
        err += 1;
    }
    console.log(form.value);
    if (err == 0) {
        getInout();
    }
}

onMounted(() => {
    getStatus();
    getInout();
})
</script>


<template>
    <div class="content">
        <div class="container-fluid">
            <div class="container-fluid">
                <!-- <h1>Keuangan SIPON Kyai Galang Sewu - {{ date.word }}</h1> -->
                <div class="mt-2 row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Santri</span>
                                <span class="info-box-number">
                                    {{ stat.santri }}
                                    <small>orang</small>
                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-scroll"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sedang Dispensasi</span>
                                <span class="info-box-number">{{ stat.dispen }} <small>orang</small></span>
                            </div>

                        </div>

                    </div>


                    <div class="clearfix hidden-md-up"></div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-piggy-bank"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Hutang Berjalan</span>
                                <span class="info-box-number">{{ stat.debt }} <small>buah</small></span>
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tagihan Berjalan</span>
                                <span class="info-box-number">{{ stat.bill }} <small>buah</small> </span>
                            </div>

                        </div>

                    </div>

                </div>
                <div class="row mb-2">
                    <div class="col">
                        <form @submit="inoutForm">
                            <div class="row">
                                <div class="col-md-5 ">
                                    <div class="form-group">
                                        <input :class="{ 'is-invalid': errors.start }" v-model="form.start" type="month"
                                            class="form-control" placeholder="Periode Awal">
                                        <span class="invalid-feedback">{{ errors.start }}</span>
                                    </div>
                                </div>
                                <div class="col-md-5 ">
                                    <div class="form-group">
                                        <input :class="{ 'is-invalid': errors.end }" v-model="form.end" type="month"
                                            class="form-control" placeholder="Periode Akhir">
                                        <span class="invalid-feedback">{{ errors.end }}</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn  btn-primary mr-md-1 w-100">
                                        <i class="fas fa-search mr-1"></i>
                                    </button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Pemasukan</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>

                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <p class="text-center">
                                            <strong>{{ date.month }}</strong>
                                        </p>
                                        <div class="chart">
                                            <div class="chartjs-size-monitor">
                                                <div class="chartjs-size-monitor-expand">
                                                    <div class="">
                                                    </div>
                                                </div>
                                                <div class="chartjs-size-monitor-shrink">
                                                    <div class=""></div>
                                                </div>
                                            </div>

                                            <canvas id="myChart" height="225"
                                                style="height: 180px; display: block; width: 700px;" width="700"
                                                class="chartjs-render-monitor"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col">
                                        <p class="text-center">

                                        <div class="card">
                                            <div class="card-body m-0 p-0">
                                                <div class="row">
                                                    <div class="col-4 border-right p-3 text-primary">
                                                        <h3 class="m-0"><i class="nav-icon fas fa-arrow-down"></i><strong> Pemasukan</strong></h3>
                                                    </div>
                                                    <div class="col-8 text-bold p-3 text-center">
                                                        <h2 class="m-0"><strong>
                                                {{ formatMoney(sums.income_bill + sums.income_debt + sums.income_other) }} /
                                                {{
                                                    formatMoney(sums.bill_potential + sums.debt_potential + sums.income_other)
                                                }}</strong><span class="mr-1 float-right badge badge-primary">{{
                                                    Math.round((sums.income_bill + sums.income_debt + sums.income_other) *
                                                        100 /
                                                        (sums.bill_potential + sums.debt_potential + sums.income_other)) }}
                                                    %</span></h2>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">

                                        <div class="small-box text-light " style="background-color:#011f4b ;">
                                            <div class="inner">
                                                <h3>{{ sums.bill_potential == 0 ? 0 : Math.round((sums.income_bill) * 100 /
                                                    (sums.bill_potential)) }}<sup style="font-size: 20px">%</sup></h3>
                                                <p>Tagihan</p>
                                            </div>
                                            <div class="icon text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"
                                                    viewBox="0 0 256 256">
                                                    <path fill="currentColor"
                                                        d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1 0-16h8v-64a8 8 0 0 1 8-8h24a8 8 0 0 1 8 8v64h16V88a8 8 0 0 1 8-8h32a8 8 0 0 1 8 8v112h16V40a8 8 0 0 1 8-8h40a8 8 0 0 1 8 8v160h8a8 8 0 0 1 8 8Z" />
                                                </svg>
                                            </div>
                                            <buton @click="incomeTable.bill = !incomeTable.bill" class="small-box-footer">

                                                <span v-if="incomeTable.bill == false"> Info tambahan <i
                                                        class="fas fa-arrow-circle-right"></i></span>
                                                <span v-else> Sembuyikan <i class="fas fa-arrow-circle-up"></i></span>
                                            </buton>
                                        </div>

                                        <table class="table table-striped" v-if="incomeTable.bill">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-left">Nama</th>
                                                    <th>Progress</th>
                                                    <th>Pemasukan</th>
                                                    <th>Tagihan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="data in bills">
                                                    <td>{{ data.account_name }}</td>
                                                    <td>
                                                        <div class="text-center m-0 p-0">
                                                            <span v-if="data.bill_sum_amount != null">
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar progress-bar-danger"
                                                                        :style="{ width: (data.bill_sum_amount - data.bill_sum_remainder) / data.bill_sum_amount * 100 + '%' }">
                                                                    </div>
                                                                </div>
                                                                <span class="badge bg-danger">{{
                                                                    Math.round((data.bill_sum_amount -
                                                                        data.bill_sum_remainder) /
                                                                        data.bill_sum_amount * 100) +
                                                                    '%'
                                                                }}
                                                                </span>
                                                            </span>
                                                            <span v-else>
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar progress-bar-danger"
                                                                        :style="{ width: 0 }">
                                                                    </div>
                                                                </div>
                                                                <span class="badge bg-danger">{{
                                                                    0 +
                                                                    '%'
                                                                }}
                                                                </span>
                                                            </span>

                                                        </div>
                                                    </td>
                                                    <td class="text-right">{{ formatMoney(data.bill_sum_amount -
                                                        data.bill_sum_remainder)
                                                    }}</td>

                                                    <td class="text-right"> {{ formatMoney(data.bill_sum_amount) }} </td>
                                                </tr>

                                                <tr style="border-top:3px solid black">
                                                    <td colspan="2" class="text-bold text-center">Total</td>
                                                    <td class="text-bold text-right">{{ formatMoney(sums.income_bill) }}
                                                    </td>
                                                    <td class="text-bold text-right">{{ formatMoney(sums.bill_potential)
                                                    }} </td>
                                                </tr>
                                            </tbody>
                                        </table>


                                    </div>
                                    <div class="col">
                                        <div class="small-box text-white" style="background-color:#03396c ;">
                                            <div class="inner">
                                                <h3>{{ sums.debt_potential == 0 ? 0 : Math.round((sums.income_debt) * 100 /
                                                    (sums.debt_potential)) }}<sup style="font-size: 20px">%</sup></h3>
                                                <p>Hutang</p>
                                            </div>
                                            <div class="icon text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"
                                                    viewBox="0 0 256 256">
                                                    <path fill="currentColor"
                                                        d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1 0-16h8v-64a8 8 0 0 1 8-8h24a8 8 0 0 1 8 8v64h16V88a8 8 0 0 1 8-8h32a8 8 0 0 1 8 8v112h16V40a8 8 0 0 1 8-8h40a8 8 0 0 1 8 8v160h8a8 8 0 0 1 8 8Z" />
                                                </svg>
                                            </div>
                                            <buton @click="incomeTable.debt = !incomeTable.debt" class="small-box-footer">

                                                <span v-if="incomeTable.debt == false"> Info tambahan <i
                                                        class="fas fa-arrow-circle-right"></i></span>
                                                <span v-else> Sembuyikan <i class="fas fa-arrow-circle-up"></i></span>
                                            </buton>
                                        </div>

                                        <table class="table table-striped" v-if="incomeTable.debt">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-left">Nama</th>
                                                    <th>Progress</th>
                                                    <th>Pemasukan</th>
                                                    <th>Tagihan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="data in debt">
                                                    <td>{{ data.account_name }}</td>
                                                    <td>
                                                        <div class="text-center m-0 p-0">
                                                            <span v-if="data.debt_sum_amount != null">
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar progress-bar-danger"
                                                                        :style="{ width: (data.debt_sum_amount - data.debt_sum_remainder) / data.debt_sum_amount * 100 + '%' }">
                                                                    </div>
                                                                </div>
                                                                <span class="badge bg-danger">{{
                                                                    Math.round((data.debt_sum_amount -
                                                                        data.debt_sum_remainder) /
                                                                        data.debt_sum_amount * 100) +
                                                                    '%'
                                                                }}
                                                                </span>
                                                            </span>
                                                            <span v-else>
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar progress-bar-danger"
                                                                        :style="{ width: 0 }">
                                                                    </div>
                                                                </div>
                                                                <span class="badge bg-danger">{{
                                                                    0 +
                                                                    '%'
                                                                }}
                                                                </span>
                                                            </span>

                                                        </div>
                                                    </td>
                                                    <td class="text-right">{{ formatMoney(data.debt_sum_amount -
                                                        data.debt_sum_remainder)
                                                    }}</td>

                                                    <td class="text-right"> {{ formatMoney(data.debt_sum_amount) }} </td>
                                                </tr>

                                                <tr style="border-top:3px solid black">
                                                    <td colspan="2" class="text-bold text-center">Total</td>
                                                    <td class="text-bold text-right">{{ formatMoney(sums.income_debt) }}
                                                    </td>
                                                    <td class="text-bold text-right">{{ formatMoney(sums.income_potential)
                                                    }} </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col">
                                        <div class="small-box text-light" style="background-color:#3fb1c1 ;">
                                            <div class="inner">
                                                <h3>{{ formatMoney(sums.income_other) }}<sup
                                                        style="font-size: 20px">,-</sup></h3>
                                                <p>Lain - lain</p>
                                            </div>
                                            <div class="icon text-light">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"
                                                    viewBox="0 0 256 256">
                                                    <path fill="currentColor"
                                                        d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1 0-16h8v-64a8 8 0 0 1 8-8h24a8 8 0 0 1 8 8v64h16V88a8 8 0 0 1 8-8h32a8 8 0 0 1 8 8v112h16V40a8 8 0 0 1 8-8h40a8 8 0 0 1 8 8v160h8a8 8 0 0 1 8 8Z" />
                                                </svg>
                                            </div>
                                            <buton @click="incomeTable.other = !incomeTable.other" class="small-box-footer">

                                                <span v-if="incomeTable.other == false"> Info tambahan <i
                                                        class="fas fa-arrow-circle-right"></i></span>
                                                <span v-else> Sembuyikan <i class="fas fa-arrow-circle-up"></i></span>
                                            </buton>
                                        </div>

                                        <table class="table table-striped" v-if="incomeTable.other">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-left">Nama</th>
                                                    <th>Pemasukan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="data in other">
                                                    <td>{{ data.account_name }}</td>
                                                    <td class="text-center">{{ formatMoney(data.trans_sum_debit)
                                                    }}</td>
                                                </tr>

                                                <tr style="border-top:3px solid black">
                                                    <td class="text-bold text-center">Total</td>
                                                    <td class="text-bold text-center">{{ formatMoney(sums.income_other) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Pengeluaran</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>

                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <p class="text-center">
                                            <strong>{{ date.month }}</strong>
                                        </p>
                                        <div class="chart">
                                            <div class="chartjs-size-monitor">
                                                <div class="chartjs-size-monitor-expand">
                                                    <div class="">
                                                    </div>
                                                </div>
                                                <div class="chartjs-size-monitor-shrink">
                                                    <div class=""></div>
                                                </div>
                                            </div>

                                            <canvas id="myChart2" height="225"
                                                style="height: 180px; display: block; width: 700px;" width="700"
                                                class="chartjs-render-monitor"></canvas>


                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col">
                                        <p class="text-center">

                                        <div class="card">
                                            <div class="card-body m-0 p-0">
                                                <div class="row">
                                                    <div class="col-4 border-right p-3 text-red">
                                                        <h3 class="m-0"><i class="nav-icon fas fa-arrow-up"></i><strong> Pengeluaran</strong></h3>
                                                    </div>
                                                    <div class="col-8 text-bold p-3 text-center">
                                                        <h2 class="m-0"><strong>{{ formatMoney(sums.expense_debt + sums.expense_other) }}</strong></h2>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="small-box text-white" style="background-color:#ff4433 ;">
                                            <div class="inner">
                                                <h3>{{ formatMoney(sums.expense_debt) }}<sup
                                                        style="font-size: 20px">,-</sup></h3>
                                                <p>Hutang</p>
                                            </div>
                                            <div class="icon text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"
                                                    viewBox="0 0 256 256">
                                                    <path fill="currentColor"
                                                        d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1 0-16h8v-64a8 8 0 0 1 8-8h24a8 8 0 0 1 8 8v64h16V88a8 8 0 0 1 8-8h32a8 8 0 0 1 8 8v112h16V40a8 8 0 0 1 8-8h40a8 8 0 0 1 8 8v160h8a8 8 0 0 1 8 8Z" />
                                                </svg>
                                            </div>
                                            <buton @click="expenseTable.debt = !expenseTable.debt" class="small-box-footer">

                                                <span v-if="expenseTable.debt == false"> Info tambahan <i
                                                        class="fas fa-arrow-circle-right"></i></span>
                                                <span v-else> Sembuyikan <i class="fas fa-arrow-circle-up"></i></span>
                                            </buton>
                                        </div>

                                        <table class="table table-striped" v-if="expenseTable.debt">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-left">Nama</th>
                                                    <th>Pengeluaran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="data in debt">
                                                    <td>{{ data.account_name }}</td>
                                                    <td class="text-center"> {{ formatMoney(data.debt_sum_amount) }} </td>
                                                </tr>

                                                <tr style="border-top:3px solid black">
                                                    <td class="text-bold text-center">Total</td>
                                                    <td class="text-bold text-center">{{ formatMoney(sums.expense_debt)
                                                    }} </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col">
                                        <div class="small-box text-light" style="background-color:#a52a2a ;">
                                            <div class="inner">
                                                <h3>{{ formatMoney(sums.expense_other) }}<sup
                                                        style="font-size: 20px">,-</sup></h3>
                                                <p>Lain - lain</p>
                                            </div>
                                            <div class="icon text-light">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"
                                                    viewBox="0 0 256 256">
                                                    <path fill="currentColor"
                                                        d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1 0-16h8v-64a8 8 0 0 1 8-8h24a8 8 0 0 1 8 8v64h16V88a8 8 0 0 1 8-8h32a8 8 0 0 1 8 8v112h16V40a8 8 0 0 1 8-8h40a8 8 0 0 1 8 8v160h8a8 8 0 0 1 8 8Z" />
                                                </svg>
                                            </div>
                                            <buton @click="expenseTable.other = !expenseTable.other"
                                                class="small-box-footer">

                                                <span v-if="expenseTable.other == false"> Info tambahan <i
                                                        class="fas fa-arrow-circle-right"></i></span>
                                                <span v-else> Sembuyikan <i class="fas fa-arrow-circle-up"></i></span>
                                            </buton>
                                        </div>

                                        <table class="table table-striped" v-if="expenseTable.other">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-left">Nama</th>
                                                    <th>Pengeluaran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="data in other">
                                                    <td>{{ data.account_name }}</td>
                                                    <td class="text-center">{{ formatMoney(data.trans_sum_credit)
                                                    }}</td>
                                                </tr>

                                                <tr style="border-top:3px solid black">
                                                    <td class="text-bold text-center">Total</td>
                                                    <td class="text-bold text-center">{{ formatMoney(sums.expense_other) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>



                                </div>

                            </div>

                        </div>
                    </div>

                </div>



            </div>

        </div>

</div></template>


