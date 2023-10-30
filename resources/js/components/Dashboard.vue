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
    'income': null,
    'expense': null,
    'income_potential': null
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
                sums.value.income = datas.income;
                sums.value.expense = datas.expense;
                sums.value.income_potential = datas.income_potential
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
    var paybill = inoutData.value.paybill;
    var labelpb = paybill.map(function (e) {
        return e.date;
    });
    var dataspb = paybill.map(function (e) {
        return e.sum;
    });

    var paydebt = inoutData.value.paydebt;
    var labelpd = paydebt.map(function (e) {
        return e.date;
    });
    var dataspd = paydebt.map(function (e) {
        return e.sum;
    });

    var trans = inoutData.value.trans;
    var labelt = trans.map(function (e) {
        return e.date;
    });
    var datast_deb = trans.map(function (e) {
        return e.sum_debit;
    });
    var datast_cred = trans.map(function (e) {
        return e.sum_credit;
    });

    var debt = inoutData.value.debt;
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
                                            <div class="card-header">
                                                <strong>Pemasukan</strong>
                                            </div>
                                            <div class="card-body">
                                                <span class="mr-1 badge badge-primary">{{
                                                    Math.round(sums.income * 100 / sums.income_potential) }} %</span>
                                                {{ formatMoney(sums.income) }} / {{ formatMoney(sums.income_potential) }}
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">


                                        <div class="info-box mb-3" :class="[formatBg(2)]">
                                            <span class="info-box-icon"><i class="fas fa-user-tag"></i></span>
                                            <div class="info-box-content">
                                                <div v-for="data in bills" class="progress-group">
                                                    {{ data.account_name }}
                                                    <span class="info-box-text" v-if="data.bill_sum_amount != null">
                                                        <b>{{ formatMoney(data.bill_sum_amount - data.bill_sum_remainder)
                                                        }}</b>/
                                                        {{ formatMoney(data.bill_sum_amount) }}
                                                    </span>
                                                    <span class="info-box-text" v-else>
                                                        {{ formatMoney(data.bill_sum_amount) }}
                                                    </span>

                                                    <div class="progress progress-sm" style="height:10px!important">
                                                        <div v-if="data.bill_sum_amount != null"
                                                            class=" progress-bar bg-light"
                                                            :style="{ width: (data.bill_sum_amount - data.bill_sum_remainder) / data.bill_sum_amount * 100 + '%' }">
                                                            {{ Math.round((data.bill_sum_amount - data.bill_sum_remainder) /
                                                                data.bill_sum_amount * 100) +
                                                                '%'
                                                            }}</div>
                                                        <div v-else class="progress-bar bg-light"
                                                            :style="{ width: data.bill_sum_amount * 100 + '%' }">
                                                            {{ Math.round(data.bill_sum_amount * 100) +
                                                                '%'
                                                            }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                    </div>
                                    <div class="col">
                                        <div class="info-box mb-3" :class="[formatBg(3)]">
                                            <span class="info-box-icon">
                                                <i class="fas fa-receipt"></i></span>
                                            <div class="info-box-content">
                                                <div v-for="data in debt" class="progress-group">
                                                    {{ data.account_name }}
                                                    <span class="info-box-text"
                                                        v-if="data.debt_sum_amount == data.debt_sum_amount">
                                                        <b>{{ formatMoney(data.debt_sum_amount - data.debt_sum_remainder)
                                                        }}</b>/
                                                        {{ formatMoney(data.debt_sum_amount) }}
                                                    </span>
                                                    <span class="info-box-text" v-else>
                                                        {{ formatMoney(data.debt_sum_amount) }}
                                                    </span>

                                                    <div class="progress progress-sm" style="height:10px!important">
                                                        <div v-if="data.debt_sum_amount != null"
                                                            class=" progress-bar bg-light"
                                                            :style="{ width: (data.debt_sum_amount - data.debt_sum_remainder) / data.debt_sum_amount * 100 + '%' }">
                                                            {{ Math.round((data.debt_sum_amount - data.debt_sum_remainder) /
                                                                data.debt_sum_amount * 100) +
                                                                '%'
                                                            }}</div>
                                                        <div v-else class="progress-bar bg-light"
                                                            :style="{ width: data.debt_sum_amount * 100 + '%' }">
                                                            {{ Math.round(data.debt_sum_amount * 100) +
                                                                '%'
                                                            }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="info-box mb-3" :class="[formatBg(4)]">
                                            <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                                            <div class="info-box-content">
                                                <div v-for="data in other" class="progress-group">
                                                    {{ data.account_name }}
                                                    <span class="info-box-text"><b>{{ formatMoney(data.trans_sum_debit)
                                                    }}</b></span>

                                                </div>
                                            </div>
                                        </div>
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
                                            <div class="card-header">
                                                <strong>Pengeluaran</strong>
                                            </div>
                                            <div class="card-body text-bold">
                                                {{ formatMoney(sums.expense) }}
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm col">

                                        <div class="info-box mb-3" :class="[formatBg(0)]">
                                            <span class="info-box-icon"><i class="fas fa-user-clock"></i></span>
                                            <div class="info-box-content">
                                                <div v-for="data in debt" class="progress-group">
                                                    {{ data.account_name }}
                                                    <span class="info-box-text"><b>{{ formatMoney(data.debt_sum_amount)
                                                    }}</b></span>

                                                </div>
                                            </div>


                                        </div>
                                    </div>

                                    <div class="col-sm col">
                                        <div class="info-box mb-3" :class="[formatBg(1)]">
                                            <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                                            <div class="info-box-content">
                                                <div v-for="data in other" class="progress-group">
                                                    {{ data.account_name }}
                                                    <span class="info-box-text"><b>{{ formatMoney(data.trans_sum_credit)
                                                    }}</b></span>

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

    </div>
</template>


