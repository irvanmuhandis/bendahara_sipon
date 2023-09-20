<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { formatDate, formatMonth, formatStatus, formatMoney, formatAccount } from '../../helper.js';

const toastr = useToastr();
const wallets = ref([]);
const santris = ref([]);
const accounts = ref([]);
const formatted = ref();
const errors = ref({
    'santri': null,
    'price': null,
    'title': null,
    'account': null,
    'wallet': null,
    'account': null
});
const form = ref({
    'santri': [],
    'wallet': null,
    'account': null,
    'price': '',
    'title': '',
    'account': null
});


const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');

    } catch (error) {
        console.error(error);
    }
}

const getAccount = async () => {

    try {
        const datas = await axios.get(`/api/account/only`, {
            params: {
                type: 1
            }
        })
        accounts.value = datas.data

    } catch (error) {
        console.error(error);
    }
}

const getWallet = async () => {

    try {
        const response = await axios.get(`/api/wallet/list`)
        wallets.value = response.data;

    } catch (error) {
        console.error(error);
    }
}

const valid = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.santri.length == 0 || '') {
        errors.value.santri = 'Pilih Santri ';
        err += 1;
    }
    if (form.value.title == '') {
        errors.value.title = 'Masukkan Judul ';
        err += 1;
    }
    if (form.value.account == null) {
        errors.value.account = 'Pilih Akun ';
        err += 1;
    }
    if (form.value.wallet == null) {
        errors.value.wallet = 'Pilih Dompet';
        err += 1;
    }
    else {
        if (form.value.price > form.value.wallet.sum.saldo) {
            errors.value.price = 'Hutang tak boleh melebihi saldo dompet'
            err += 1;
        }
        const debt = form.value.price * form.value.santri.length;
        if (debt > form.value.wallet.sum.saldo) {
            errors.value.price = 'Hutang ' + formatMoney(debt) + ' lebih besar dari saldo dompet';
            err += 1;
        }
    }
    if (form.value.price == '') {
        errors.value.price = 'Masukkan jumlah uang '
        err += 1;
    }
    console.log(errors.value);
    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const clearAll = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    clearform();
}

const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {

        if (Array.isArray(form.value[key])) {
            form.value[key] = [];
        }
        else {
            form.value[key] = null;
        }
    }
}
const createDebt = (event) => {
    event.preventDefault();
    if (valid()) {
        axios.post('/api/debt', form.value)
            .then((response) => {
                clearAll();
                toastr.success('Berhasil menambah hutang santri!');
                clearform();
                getDebt();
            })
            .catch((error) => {
                console.log(error);
            })
    }
    formatted.value = null;
};


const handleChange = (event) => {
    formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}



onMounted(() => {
    getSantri();
    getWallet();
    getAccount();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/billing/debt">Piutang</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Tambah Piutang</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">



            <div class="row-md mb-3">
                <div class="col-md">
                    <form @submit="createDebt">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Santri</label>
                                    <VueMultiselect v-model="form.santri" :option-height="9" :options="santris"
                                        :class="{ 'is-invalid': errors.santri }" :multiple="true" :close-on-select="true"
                                        placeholder="Pilih Satu / Lebih" label="fullname" track-by="nis">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.fullname }} - {{ option.nis }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.santri }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dompet</label>
                                    <VueMultiselect @click="getWallet" v-model="form.wallet" :option-height="9"
                                        :options="wallets" :class="{ 'is-invalid': errors.wallet }" :multiple="false"
                                        :close-on-select="true" placeholder="Pilih Satu..." label="wallet_name"
                                        track-by="id" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.wallet_name }} - {{ formatMoney(option.sum.saldo) }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.account }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Akun</label>
                                    <VueMultiselect v-model="form.account" :option-height="9" :options="accounts"
                                        :class="{ 'is-invalid': errors.account }" :multiple="false" :close-on-select="true"
                                        placeholder="Pilih Satu..." label="account_name" track-by="id" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.account_name }} - <span
                                                    v-html="formatAccount(option.account_type)"></span> </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.account }}</span>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Keterangan</label><br>
                                    <input :class="{ 'is-invalid': errors.title }" class="form-control" v-model="form.title"
                                        type="text" />
                                    <span class="invalid-feedback">{{ errors.title }}</span>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Piutang</label>
                                    <input :class="{ 'is-invalid': errors.price }" @keyup="handleChange"
                                        class="form-control" v-model="form.price" type="number" />
                                    <span class="invalid-feedback">{{ errors.price }}</span>
                                    <p>{{ formatted }}</p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-100 btn btn-primary">Submit</button>
                    </Form>
                </div>
            </div>

        </div>

    </div>
</template>

<script>



import accounting from 'accounting';
import { validate } from 'vee-validate';
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
