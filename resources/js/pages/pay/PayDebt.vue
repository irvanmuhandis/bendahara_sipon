<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { debounce } from 'lodash';
import { formatMoney, formatDate, formatAccount } from '../../helper';

const toastr = useToastr();
const router = useRouter();

const santris = ref([]);
const wallets = ref([]);
const santridebt = ref({ 'data': [] });
const total = ref(0);
const isLoading = ref(true);
const init = ref(true);
const formatted = ref();
const pay_status = ref([]);
const remainder = ref([]);

const selected = ref([]);
const selectAll = ref(false);
const selectedWall = ref([]);

const listpays = ref({
    'data': []
});
const searchQuery = ref();
const errors = ref({
    'santri': null,
    'debt': null,
    'wallet': null,
    'payment': null
});
const formValue = ref({
    'santri': null,
    'debt': [],
    'wallet': null,
    'payment': null
});
const filter = ref({
    'created_at': null,
    'nis': null,
    'payment': null
});
const editValues = ref({
    'id': '',
    'date': '',
    'wallet': null,
    'paymentBef': '',
    'paymentAft': '',
    'remain': '',
    'debt': null,
    'santri': null
});
const errorsEdit = ref({
    'date': null,
    'wallet': null,
    'payment': null
});




const handleChange = (event) => {
    formatted.value = formatMoney(formValue.value.payment);
}

const checkLength = computed(() => {
    return formValue.value.debt.length;
});

const totalize = (event, id) => {
    const item = santridebt.value.data.find((item) => item.id === id);
    if (event.target.checked) {
        total.value = parseInt(total.value) + parseInt(item.remainder);
        remainder.value.push(item.remainder);
    }
    else {
        total.value = parseInt(total.value) - parseInt(item.remainder);
        let index = remainder.value.indexOf(item.remainder);
        if (index !== -1) {
            remainder.value.splice(index, 1);
        }

    }

    console.log(total.value);
}

const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in formValue.value) {

        if (Array.isArray(formValue.value[key])) {
            formValue.value[key] = [];
        }
        else {
            formValue.value[key] = null;
        }
    }
}



const createPay = (event) => {
    event.preventDefault();
    formValue.value.remainder = remainder.value;
    if (valid()) {
        axios.post('/api/pay/debt', formValue.value)
            .then((response) => {

                toastr.success('Berhasil melakukan pembayaran!');
                getData();
                clearform();
                santridebt.value = [];
                total.value = 0;
                init.value = true;
                isLoading.value = true;
                formatted.value = null;
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
    if (formValue.value.santri == null) {
        errors.value.santri = 'Pilih Santri ';
        err += 1;
    }
    if (formValue.value.debt.length == 0) {
        errors.value.debt = 'Pilih Bill '
        err += 1;
    }
    if (formValue.value.payment == null) {
        errors.value.payment = 'Masukkan jumlah pembayaran '
        err += 1;
    }
    if (formValue.value.wallet == null) {
        errors.value.wallet = 'Pilih dompet '
        err += 1;
    }
    if (formValue.value.payment > total.value) {
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
        const response = await axios.get(`/api/wallet/list`)
        wallets.value = response.data;
        console.log('wallet added');
    } catch (error) {
        console.error(error);
    }
}


const getSantridebt = async (link = `/api/santri/debt/${formValue.value.santri.nis}`) => {
    console.log(formValue.value.debt);
    try {
        const response = await axios.get(link);
        santridebt.value = response.data;
        isLoading.value = false;
    } catch (error) {
        console.error(error);
    }
};

const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');
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

const santrichange = (event) => {
    isLoading.value = true;
    init.value = false;
    formValue.value.debt = [];
    remainder.value = [];
    getSantridebt();
}

const getData = (link = `/api/paydebt`) => {
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
                query: searchQuery.value
            }
        }).then((response) => {
            listpays.value = response.data;
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
    getData();
    console.log(filter.value);
}


const bulkDelete = () => {
    axios.delete('/api/pay', {
        data: {
            pay: selected.value,
            wall_ids: selectedWall.value,
            type: 'App\\Models\\Debt'
        }
    })
        .then(response => {
            toastr.success("Berhasil menghapus data");
            getData();
            selected.value = [];
            selectedWall.value = [];
            $('#deleteDataModal').modal('hide');
        });
};

const confirmDataDeletion = (id) => {
    $('#deleteDataModal').modal('show');
};


const toggleSelection = (data) => {
    const index = selected.value.indexOf(data);
    if (index === -1) {
        selected.value.push(data);
        selectedWall.value.push(data.wallet_id);
    } else {
        selected.value.splice(index, 1);
        selectedWall.value.splice(index, 1);
    }
    console.log(selected.value);
    console.log(selectedWall.value);
    console.log(total.value);
};




const selectedAllData = () => {
    if (selectAll.value) {
        selected.value = listpays.value.data.map(data => data);
        selectedWall.value = listpays.value.data.map(data => data.wallet_id);
    } else {
        selected.value = []; selectedWall.value = [];
    }
    console.log(selected.value);
    console.log(selectedWall.value);
}

const editData = (data) => {

    console.log(data);
    editValues.value.id = data.id;
    editValues.value.wallet = data.wallet;
    editValues.value.date = convertDate(data.created_at);
    editValues.value.paymentBef = data.payment;
    editValues.value.paymentAft = data.payment;
    editValues.value.debt = data.payable;
    editValues.value.santri = data.payable.santri;
    editValues.value.remain = (editValues.value.debt.remainder + editValues.value.paymentBef);
    console.log(editValues.value);
    $('#editDataModal').modal('show');
}
const confirmUpdate = () => {


    if (validate()) {
        $('#editDataModal').modal('hide');
        $('#conEditDataModal').modal('show');
    }
}

const changeRemain = () => {
    if (editValues.value.paymentAft != '') {
        editValues.value.remain = (editValues.value.debt.remainder + editValues.value.paymentBef) - editValues.value.paymentAft;
    }
    else {
        editValues.value.remain = (editValues.value.debt.remainder + editValues.value.paymentBef) - 0;

    }
}

const validate = () => {
    var err = 0;

    for (const key in errorsEdit.value) {
        errorsEdit.value[key] = null;
    }
    if (editValues.value.wallet == null) {
        errorsEdit.value.wallet = 'Pilih Dompet '
        err += 1;
    }
    if (editValues.value.payment == '') {
        errorsEdit.value.payment = 'Isi Jumlah Pembayaran '
        err += 1;
    }
    if (editValues.value.date == '') {
        errorsEdit.value.date = 'Pilih Tanggal Pembayaran '
        err += 1;
    }
    if (editValues.value.paymentAft > (editValues.value.debt.remainder + editValues.value.paymentBef)) {
        errorsEdit.value.payment = 'Pembayaran tidak boleh lebih dari sisa tagihan'
        err += 1;
    }
    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const update = () => {
    editValues.value.type = 'App\\Models\\Debt';
    console.log(editValues.value);
    console.log(errors.value);

    axios.put(`/api/pay/${editValues.value.id}`, editValues.value)
        .then((response) => {
            toastr.success('Berhasil merubah data!');
            $('#conEditDataModal').modal('hide');
            search();
        })
        .catch((error) => {
            console.log(error);
        })

};


watch(searchQuery, debounce(() => {
    getData();
}, 300));


onMounted(() => {
    getWallet();
    getSantri();
    getData();
    getPayStatus();
});
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
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/income">Pemasukan</router-link>
                        </li>
                        <li class="breadcrumb-item active">Piutang</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Debt</label>
                                    <div v-if="formValue.debt.length > 0">
                                        <i> {{ checkLength }} debt checked</i>
                                    </div>
                                    <div v-if="isLoading" class="text-center">
                                        <div class="spinner-border row text-primary mx-auto" role="status"></div>
                                        <div v-if="init" class="h6">Pilih Santri Terlebih Dahulu</div>
                                    </div>
                                    <div v-else>
                                        <div class="" v-for="debt in santridebt.data" :key="debt.id">
                                            <input v-model="formValue.debt" type="checkbox"
                                                @change="totalize($event, debt.id)" :value="debt.id" />
                                            <label class="ml-2">
                                                {{ debt.title }} | {{ debt.due_date }}
                                                <span class="text-right text-monospace">
                                                    <div :class="'badge badge-' + debt.color">{{
                                                        '[' + formatDate(debt.created_at) + ']'
                                                    }}</div>
                                                    {{
                                                        formatMoney(
                                                            debt.remainder) }}
                                                </span>
                                            </label>
                                        </div>
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination">
                                                <li v-for="link in santridebt.links" :key="link.label"
                                                    :class="{ 'active': link.active, 'disabled': link.url == null }"
                                                    class="page-item">
                                                    <a class="page-link" v-html="link.label" href="#"
                                                        @click="getSantridebt(link.url)"></a>
                                                </li>
                                            </ul>
                                        </nav> <input type="text" class="d-none is-invalid">
                                        <span class="invalid-feedback">{{ errors.debt }}</span>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Santri</label>
                                    <VueMultiselect @click="getSantri" v-model="formValue.santri" :option-height="9"
                                        @select="santrichange" :options="santris" :multiple="false"
                                        :class="{ 'is-invalid': errors.santri }" :close-on-select="true"
                                        placeholder="Pilih Satu" label="fullname" track-by="nis" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.fullname }} - {{ option.nis }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.santri }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Wallet</label>
                                    <VueMultiselect @click="getWallet" v-model="formValue.wallet" :option-height="9"
                                        :options="wallets" :class="{ 'is-invalid': errors.wallet }" :close-on-select="true"
                                        :multiple="false" placeholder="Pilih Satu" label="wallet_name" track-by="id"
                                        :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.wallet_name }} - {{ formatMoney(option.sum.saldo) }} </div>
                                        </template>
                                    </VueMultiselect>

                                    <span class="invalid-feedback">{{ errors.wallet }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Payment</label><br>
                                    <span>Total Debt : {{ formatMoney(total) }}</span>
                                    <input type="number" v-model="formValue.payment" @keyup="handleChange"
                                        :class="{ 'is-invalid': errors.payment }" class="form-control" id="time" />
                                    <span class="invalid-feedback">{{ errors.payment }}</span>
                                    <span>{{ formatted }}</span><br>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button @click="createPay" type="button" class="btn btn-primary w-100">Submit</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
            <div class="row mt-2">
                <div class="col-md-3" v-if="selected.length > 0">
                    <button @click="confirmDataDeletion" type="button" class=" w-100 mb-2 btn btn-danger">
                        <i class="fa fa-trash mr-1"></i>
                        Hapus {{ selected.length }} Data
                    </button>

                </div>
                <div class="mb-2 col-md">
                    <div class="input-group w-100 ">
                        <input type="text" v-model="searchQuery" class=" form-control" placeholder="Search..." />
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered " style="overflow: auto;width:100%">
                        <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" v-model="selectAll"
                                        @change="selectedAllData" /></th>
                                <th>Tanggal
                                    <span class="float-right" @click="sort('created_at')">
                                        <i :class="{ 'text-primary': filter.created_at == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.created_at == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th>Nama
                                    <span class="float-right" @click="sort('nis')">
                                        <i :class="{ 'text-primary': filter.nis == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.nis == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th>Bayar
                                    <span class="float-right" @click="sort('payment')">
                                        <i :class="{ 'text-primary': filter.payment == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.payment == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th>Sisa</th>
                                <th>Judul</th>
                                <th>Dompet</th>
                                <th>Akun</th>
                                <th>Operator</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(pay) in listpays.data" :key="pay.id">
                                <td class="text-center"><input type="checkbox" :checked="selectAll"
                                        @change="toggleSelection(pay)" />
                                </td>
                                <td>{{ formatDate(pay.created_at) }}</td>
                                <td>{{ pay.payable.santri.fullname }} - {{ pay.payable.santri.nis }} </td>
                                <td>{{ formatMoney(pay.payment) }}</td>
                                <td>{{ formatMoney(pay.payable.remainder) }}</td>
                                <td>{{ pay.payable.title }}</td>
                                <td>{{ pay.wallet.wallet_name }}</td>
                                <td>{{ pay.payable.account.account_name }}</td>
                                <td>{{ pay.operator.fullname }}</td>
                                <td>
                                    <a href="#" @click="editData(pay)">
                                        <i class="fa fa-edit mr-2"></i>
                                    </a>


                                </td>
                            </tr>
                            <tr v-if="listpays.data.length == 0">
                                <td colspan="9" class="text-center">Tidak Ada Data</td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li v-for="link in listpays.links" :key="link.label"
                                :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                                <a class="page-link" v-html="link.label" href="#" @click="getData(link.url)"></a>
                            </li>
                        </ul>
                    </nav>

                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="deleteDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Konfirmasi Penghapusan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin ingin menghapus {{ selected.length }} data ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button @click.prevent="bulkDelete" type="button" class="btn btn-primary">Ya, saya yakin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="conEditDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Konfirmasi Perubahan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin ingin merubah data menjadi : </h5>
                    <div class="row">
                        <div class="col-5">Santri </div>
                        <div v-if="editValues.santri != null" class="col"> : {{ editValues.santri.fullname }} - {{
                            editValues.santri.nis }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5">Piutang </div>
                        <div v-if="editValues.debt != null" class="col"> : {{ editValues.debt.account.account_name + ' / ' +
                            editValues.debt.due_date }}</div>
                    </div>
                    <div class="row">
                        <div class="col-5">Dompet </div>
                        <div v-if="editValues.wallet != null" class="col"> : {{ editValues.wallet.wallet_name }}</div>
                    </div>
                    <div class="row">
                        <div class="col-5">Tanggal </div>
                        <div class="col"> : {{ formatDate(editValues.date) }}</div>
                    </div>
                    <div class="row">
                        <div v-if="editValues.paymentAft != editValues.paymentBef" class="col-5">Pembayaran Sebelum</div>
                        <div v-else class="col-5">Pembayaran</div>
                        <div class="col"> : {{ formatMoney(editValues.paymentBef) }}</div>
                    </div>
                    <div v-if="editValues.paymentAft != editValues.paymentBef" class="row">
                        <div class="col-5">Pembayaran Sesudah</div>
                        <div class="col"> : {{ formatMoney(editValues.paymentAft) }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button @click.prevent="update" type="button" class="btn btn-primary">Ya, saya yakin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Ubah Data Pembayaran Piutang</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input @change="console.log(editValues.date)" class="form-custom"
                                        :class="{ 'is-invalid': errorsEdit.date }" v-model="editValues.date"
                                        type="datetime-local" />
                                    <span class="invalid-feedback">{{ errorsEdit.date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Jumlah Pembayaran</label><br>
                                    <span v-if="editValues.debt != null">Sisa tagihan : {{
                                        formatMoney(editValues.remain) }}</span>
                                    <input class="form-custom" @keyup="changeRemain"
                                        :class="{ 'is-invalid': errorsEdit.payment }" v-model="editValues.paymentAft"
                                        type="number" />
                                    <span>{{ formatMoney(editValues.paymentAft) }}</span>
                                    <span class="invalid-feedback">{{ errorsEdit.payment }}</span>
                                </div>

                            </div>
                        </div>



                    </div>
                    <div class="modal-footer ">
                        <!-- <button type="button" class="col-md-3 btn btn-secondary" data-dismiss="modal">Tutup</button> -->
                        <button type="button" @click="confirmUpdate" class=" w-100 btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
