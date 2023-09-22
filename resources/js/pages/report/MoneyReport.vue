<script setup>
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { formatMoney, formatDate, formatMoney_2, formatDiff, formatClass, formatlowerCase, formatBg, randColor, formatDateTimestamp } from '../../helper';
import Chart from "chart.js/auto";
import moment from 'moment';
import { toInteger } from 'lodash';
import { debounce } from 'lodash';
import { validate } from 'vee-validate';


const listData = ref({
    'data': []
});
const listCircul = ref({
    'data': []
});
const wallets = ref([]);
const listwallets = ref({
    'data': []
});
const graphWall = ref({
    'data': [],
    'label': [],
    'color': []
});

const date = ref({
    'word': null,
    'month': null,
    'number': null,
    'yaer': null
});
const inoutData = ref();
const bills = ref();
const debt = ref();
const other = ref();

const filter = ref({
    'created_at': null,
    'saldo': null,
    'wallet_type': null
})

const form = ref({
    'start': '',
    'end': '',
})

const errors = ref({
    'start': null,
    'end': null,
})


async function getInout(params) {

    axios.get('/api/accsum',{
        params
    })
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

    try {
        const response = await axios.get('/api/inout', {
            params
        })
        inoutData.value = response.data;
        graph();
    } catch (error) {
        console.error(error);
    }

};


const getWallet = (page = 1) => {
    // const param = {};
    // param.query = searchQuery.value

    axios.get(`/api/wallet/list`)
        .then(response => {
            wallets.value = response.data;
            wallets.value.sum = 0;
            graphWall.value.color = randColor(response.data.length);
            graphWall.value.data = response.data.map(e => e.sum.saldo);
            graphWall.value.label = response.data.map(e => e.wallet_name);
            response.data.forEach(element => {
                wallets.value.sum += toInteger(element.sum.saldo);
            });
            console.log(wallets.value);
            graph();
        })
        .catch(error => {
            console.log(error);
        })
};

const getData = (page = 1) => {

    axios.get(`/api/ledger?page=${page}`)
        .then((response) => {
            listData.value = response.data;
        })

}
const graph = () => {
    const ctx3 = document.getElementById('myChart9');
    const data3 = {
        datasets: [{
            data: graphWall.value.data,
            fill: true,
            backgroundColor: graphWall.value.color,
            hoverOffset: 4
        }],
        labels: graphWall.value.label,
    };
    new Chart(ctx3, {
        type: 'doughnut',
        data: data3
    });
}

const getDate = () => {
    const today = new Date();
    let options = { year: 'numeric', month: 'long', day: 'numeric' };
    date.value.word = today.toLocaleDateString('IN', options);

    const dates = moment().format('DD/MM/YY');

    options = { year: 'numeric', month: 'long' };
    const months = today.toLocaleDateString('IN', options);;

    date.value.number = dates;
    date.value.month = months;
    date.value.year = moment().format('YYYY');
    console.log(date.value);
}

const filtering = (event) => {
    event.preventDefault();
    errors.value.start = null;
    errors.value.end = null;
    var err = 0;
    if (form.value.start == '') {
        errors.value.start = "Isi tanggal awal";
        err += 1;
    }
    if (form.value.end == '') {
        errors.value.end = "Isi tanggal akhir";
        err += 1;
    }
    if (new Date(form.value.end) < new Date(form.value.start)) {
        errors.value.start = "Tanggal awal harus lebih kecil dari tanggal akhir";
        errors.value.end = "Tanggal awal harus lebih kecil dari tanggal akhir";
        err += 1;
    }
    if (err == 0) {
        fetchCircul();
    }
}

const fetchCircul = (link = `/api/ledger/circul`) => {
    var fil = {
        'key': null,
        'value': null
    };
    for (const key in filter.value) {
        if (filter.value[key] != null) {
            fil.value = filter.value[key] ? 1 : 0;
            fil.key = key;
        }
    }

    if (link != null) {
        axios.get(link, {
            params: {
                filter: fil.key,
                value: fil.value,
                start: form.value.start,
                end: form.value.end
            }
        }
        ).then((response) => {
            listCircul.value = response.data;
        })
    }
}

const sort = (a) => {
    for (const key in filter.value) {
        if (a != key) {
            filter.value[key] = null;
        }
    }
    if (filter.value[a] == null) {
        filter.value[a] = true;
    } else {
        filter.value[a] = !filter.value[a];
    }
    fetchCircul();
    console.log(filter.value);
}



const fetchData = (link = `/api/wallet`) => {
    var fil = {
        'key': null,
        'value': null
    };
    for (const key in filter.value) {
        if (filter.value[key] != null) {
            fil.value = filter.value[key] ? 1 : 0;
            fil.key = key;
        }
    }
    if (link != null) {
        axios.get(link, {
            params: {
                filter: fil.key,
                value: fil.value
            }
        }).then((response) => {
            listwallets.value = response.data;
        })
    }

}


onMounted(() => {
    getWallet();
    getData();
    fetchCircul();
    getDate();
    const params = { 'start': '', 'end': '' };
    getInout(params);
})

</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Keuangan</strong></h1>
                    <p><small>Kondisi keuangan terkini</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item ">
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Keuangan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3 ">
                        <div class="row">
                            <div class="col-md-4">
                                <canvas id="myChart9" height="225" style="display: block" width="700"
                                    class="w-100 chartjs-render-monitor"></canvas>
                            </div>
                            <div class="col">
                                <h5><strong>Total Dana : {{ formatMoney(wallets.sum) }}</strong></h5>
                                <div v-for="(wal, index) in wallets" :key="wal.id" class="info-box mb-3"
                                    :class="[formatBg(index)]">
                                    <span class="info-box-icon"><i class="fas fa-tag "></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ wal.wallet_name }} - {{ Math.round(wal.sum.saldo /
                                            wallets.sum * 100) + '%' }} Total Dana</span>
                                        <span class="info-box-number">{{ formatMoney(wal.sum.saldo) }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md">
                    <form @submit="filtering">
                        <div class="row mb-2">
                            <div class="col-md-5 ">

                                <label>Waktu Awal</label>
                                <input :class="{ 'is-invalid': errors.start }" v-model="form.start" type="date"
                                    class="form-control" placeholder="Periode Awal">
                                <span class="invalid-feedback">{{ errors.start }}</span>

                            </div>
                            <div class="col-md-5 ">

                                <label>Waktu Akhir</label>
                                <input :class="{ 'is-invalid': errors.end }" v-model="form.end" type="date"
                                    class="form-control" placeholder="Periode Akhir">
                                <span class="invalid-feedback">{{ errors.end }}</span>

                            </div>
                            <div class="col-md">
                                <button class="w-100 h-100 btn btn-primary" type="submit"><i class="fas fa-search"></i>
                                    Filter</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-bordered" style="overflow: auto;width:100%">
                        <thead>
                            <tr>
                                <th>No
                                </th>
                                <th>Waktu
                                    <span class="float-right" @click="sort('created_at')">
                                        <i :class="{ 'text-primary': filter.created_at == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.created_at == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th>Pemasukan
                                </th>
                                <th>Pengeluaran
                                </th>
                                <th scope="col">Dompet
                                </th>
                                <th>Saldo
                                </th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(data, index) in listCircul.data" class="text-center" :key="data.id">

                                <td>{{ index + 1 }}
                                </td>
                                <td>{{ formatDateTimestamp(data.created_at) }}</td>
                                <!-- pemasukan -->
                                <td v-if="data.ledgerable_type == 'App\\Models\\Pay'"
                                    v-html="formatMoney_2(data.ledgerable.payment, 1)"></td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Debt'">-</td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Trans'"
                                    v-html="data.ledgerable.debit == 0 ? `-` : formatMoney_2(data.ledgerable.debit, 1)">
                                </td>
                                <!-- pengeluaran -->
                                <td v-if="data.ledgerable_type == 'App\\Models\\Pay'">-</td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Debt'"
                                    v-html="formatMoney_2(data.ledgerable.amount, 2)"></td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Trans'"
                                    v-html="data.ledgerable.credit == 0 ? `-` : formatMoney_2(data.ledgerable.credit, 2)">
                                </td>

                                <td>{{ data.ledgerable.wallet.wallet_name }} </td>
                                <td>{{ formatMoney(data.ledgerable.wallet.saldo) }}</td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Pay'">Bayar
                                    {{ data.ledgerable.payable_type == 'App\\Models\\Debt' ?
                                        'hutang' + ' ' + data.ledgerable.santri.fullname :
                                        'tagihan' + ' ' + data.ledgerable.santri.fullname }}</td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Debt'">Hutang
                                    {{ formatlowerCase(data.ledgerable.title) + ' ' + data.ledgerable.santri.fullname }}
                                </td>
                                <td v-if="data.ledgerable_type == 'App\\Models\\Trans'">{{ data.ledgerable.desc }}</td>
                            </tr>
                            <tr class="text-center" v-if="listCircul.data.length == 0">
                                <td colspan="8">Tidak ada data</td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li v-for="link in listCircul.links" :key="link.label"
                                :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                                <a class="page-link" v-html="link.label" href="#" @click="fetchCircul(link.url)"></a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</template>
