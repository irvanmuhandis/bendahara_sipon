<script setup >
import { formatMoney, formatDate, formatDay } from '../helper';
import axios from 'axios';
import { ref, onMounted, reactive, computed, watch } from 'vue';
import moment from 'moment';
import { debounce } from 'lodash';
import Chart from "chart.js/auto";
import { now, round } from 'lodash';

const status = ref();
const date = ref({
    'word': null,
    'month': null,
    'number': null,
    'year': null
});
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
        start = new Date(start[0],start[1]);
        end = new Date(end[0],end[1]);
        date.value.month = (start.toLocaleDateString('IN', options)) + " ~ " + end.toLocaleDateString('IN', options);
    }
    date.value.year = moment().format('YYYY');
    console.log(date.value);
}


const graph = () => {
    var pay = inoutData.value.pay;
    var labelp = pay.map(function (e) {
        return e.date;
    });
    var datasp = pay.map(function (e) {
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
            data: datasp,
            fill: true,
            backgroundColor: '#FF21C1',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Pembayaran'
        }],
        labels: labelp,
        options: {
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
            backgroundColor: '#3FB1C1',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Hutang'
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

    const ctx3 = document.getElementById('myChart3');
    const data3 = {
        datasets: [{
            data: datast_deb,
            fill: true,
            backgroundColor: '#ffff00',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Transaksi'
        }],
        labels: labelt,
        options: {
            parsing: {
                xAxisKey: 'id',
                yAxisKey: 'id'
            }
        }
    };


    chartStatus = Chart.getChart("myChart3"); // <canvas> id
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }
    new Chart(ctx3, {
        type: 'bar',
        data: data3
    });

    const ctx4 = document.getElementById('myChart4');
    const data4 = {
        datasets: [{
            data: datast_cred,
            fill: true,
            backgroundColor: '#00ff00',
            borderColor: 'rgb(75, 192, 192)',
            label: 'Transaksi'
        }],
        labels: labelt,
        options: {
            parsing: {
                xAxisKey: 'id',
                yAxisKey: 'id'
            }
        }
    };

    chartStatus = Chart.getChart("myChart4"); // <canvas> id
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }
    new Chart(ctx4, {
        type: 'bar',
        data: data4
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
                                    <div class="col-md-8">
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
                                                style="height: 180px; display: block; width: 560px;" width="700"
                                                class="chartjs-render-monitor"></canvas>

                                            <canvas id="myChart3" height="225"
                                                style="height: 180px; display: block; width: 560px;" width="700"
                                                class="chartjs-render-monitor"></canvas>
                                        </div>

                                    </div>

                                    <div class="col-md-4">
                                        <p class="text-center">
                                            <strong>Pemasukan</strong>
                                        </p>
                                        <div v-for="data in bills" class="progress-group">
                                            {{ data.account_name }}
                                            <span class="float-right" v-if="data.bill_sum_amount != null">
                                                <b>{{ formatMoney(data.paybill_sum_payment) }}</b>/
                                                {{ formatMoney(data.bill_sum_amount) }}
                                            </span>
                                            <span class="float-right" v-else>
                                                {{ formatMoney(data.bill_sum_amount) }}
                                            </span>
                                            <div class="progress progress-sm">
                                                <div v-if="data.bill_sum_amount != null" class="progress-bar bg-primary"
                                                    :style="{ width: data.paybill_sum_payment / data.bill_sum_amount * 100 + '%' }">
                                                    {{ Math.round( data.paybill_sum_payment / data.bill_sum_amount * 100) +
                                                        '%'
                                                    }}</div>
                                                <div v-else class="progress-bar bg-primary"
                                                    :style="{ width: data.bill_sum_amount * 100 + '%' }">
                                                    {{ Math.round(data.bill_sum_amount * 100) +
                                                        '%'
                                                    }}</div>
                                            </div>
                                        </div>
                                        <div v-for="data in debt" class="progress-group">
                                            {{ data.account_name }}
                                            <span class="float-right" v-if="data.debt_sum_amount != null">
                                                <b>{{ formatMoney(data.paydebt_sum_payment) }}</b>/
                                                {{ formatMoney(data.debt_sum_amount) }}
                                            </span>
                                            <span class="float-right" v-else>
                                                {{ formatMoney(data.debt_sum_amount) }}
                                            </span>
                                            <div class="progress progress-sm">
                                                <div v-if="data.debt_sum_amount != null" class="progress-bar bg-primary"
                                                    :style="{ width:  data.paydebt_sum_payment / data.debt_sum_amount * 100 + '%' }">
                                                    {{ Math.round( data.paydebt_sum_payment /
                                                        data.debt_sum_amount * 100) +
                                                        '%'
                                                    }}</div>
                                                <div v-else class="progress-bar bg-primary"
                                                    :style="{ width: data.debt_sum_amount * 100 + '%' }">
                                                    {{ Math.round(data.debt_sum_amount * 100) +
                                                        '%'
                                                    }}</div>
                                            </div>
                                        </div>
                                        <div v-for="data in other" class="progress-group">
                                            {{ data.account_name }}
                                            <span class="float-right"><b>{{ formatMoney(data.trans_sum_debit) }}</b>
                                            </span>
                                            <div class="progress progress-sm " style="height: 3px;">
                                                <div class="progress-bar bg-primary" :style="{ width: 1 * 100 + '%' }">
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                            </div>

                            <!-- <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm col">
                                        <div class="description-block border-right">
                                            <span class="description-percentage text-success"><i
                                                    class="fas fa-caret-up"></i> 17%</span>
                                            <h5 class="description-header">$35,210.43</h5>
                                            <span class="description-text">TOTAL DEBIT</span>
                                        </div>

                                    </div>

                                    <div class="col-sm col">
                                        <div class="description-block border-right">
                                            <span class="description-percentage text-warning"><i
                                                    class="fas fa-caret-left"></i> 0%</span>
                                            <h5 class="description-header">$10,390.90</h5>
                                            <span class="description-text">TOTAL KREDIT</span>
                                        </div>

                                    </div>

                                    <div class="col-sm col-auto">
                                        <div class="description-block ">
                                            <span class="description-percentage text-success"><i
                                                    class="fas fa-caret-up"></i> 20%</span>
                                            <h5 class="description-header">$24,813.53</h5>
                                            <span class="description-text">TOTAL SALDO</span>
                                        </div>

                                    </div>

                                </div>

                            </div> -->

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
                                    <div class="col-md-8">
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
                                                style="height: 180px; display: block; width: 560px;" width="700"
                                                class="chartjs-render-monitor"></canvas>

                                            <canvas id="myChart4" height="225"
                                                style="height: 180px; display: block; width: 560px;" width="700"
                                                class="chartjs-render-monitor"></canvas>
                                        </div>

                                    </div>

                                    <div class="col-md-4">
                                        <p class="text-center">
                                            <strong>Pengeluaran</strong>
                                        </p>
                                        <div v-for="data in debt" class="progress-group">
                                            {{ data.account_name }}
                                            <span class="float-right"><b>{{ formatMoney(data.debt_sum_amount) }}</b></span>
                                            <div class="progress progress-sm " style="height: 3px;">
                                                <div class="progress-bar bg-danger" :style="{ width: 1 * 100 + '%' }"></div>
                                            </div>
                                        </div>
                                        <div v-for="data in other" class="progress-group">
                                            {{ data.account_name }}
                                            <span class="float-right"><b>{{ formatMoney(data.trans_sum_credit) }}</b>
                                            </span>
                                            <div class="progress progress-sm " style="height: 3px;">
                                                <div class="progress-bar bg-danger" :style="{ width: 1 * 100 + '%' }"></div>
                                            </div>
                                        </div>



                                    </div>

                                </div>

                            </div>

                            <!-- <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm col">
                                        <div class="description-block border-right">
                                            <span class="description-percentage text-success"><i
                                                    class="fas fa-caret-up"></i> 17%</span>
                                            <h5 class="description-header">$35,210.43</h5>
                                            <span class="description-text">TOTAL DEBIT</span>
                                        </div>

                                    </div>

                                    <div class="col-sm col">
                                        <div class="description-block border-right">
                                            <span class="description-percentage text-warning"><i
                                                    class="fas fa-caret-left"></i> 0%</span>
                                            <h5 class="description-header">$10,390.90</h5>
                                            <span class="description-text">TOTAL KREDIT</span>
                                        </div>

                                    </div>

                                    <div class="col-sm col-auto">
                                        <div class="description-block ">
                                            <span class="description-percentage text-success"><i
                                                    class="fas fa-caret-up"></i> 20%</span>
                                            <h5 class="description-header">$24,813.53</h5>
                                            <span class="description-text">TOTAL SALDO</span>
                                        </div>

                                    </div>

                                </div>

                            </div> -->

                        </div>
                    </div>

                </div>



            </div>

        </div>

    </div>
</template>


