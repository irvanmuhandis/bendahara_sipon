<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate, formatMoney, formatClass, formatDiff } from '../../helper.js';

const toastr = useToastr();
const listData = ref({
    'data': [
    ]
});
const accounts = ref([]);
const wallets = ref([]);

const switchMode = ref(false);
const searchQuery = ref('');
const selected = ref([]);
const selectAll = ref(false);

const errorsEdit = ref({
    'price': null,
    'desc': null
});

const form = ref({
    'id': null,
    'wallet': null,
    'account': null,
    'price': '',
    'priceBefore': '',
    'desc': ''
})
const filter = ref({
    'created_at': null,
    'account_id': null,
    'wallet_id': null,
    'credit': null,
})
const formValue = ref({
    'wallet': null,
    'account': null,
    'out': null,
    'desc': null
});
const errors = ref({
    'wallet': null,
    'account': null,
    'out': null,
    'desc': null
});

const bulkDelete = () => {
    console.log(selected.value);
    axios.delete('/api/trans', {
        data: {
            datas: selected.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            fetchData();
        });

    $('#deleteDataModal').modal('hide');
};

const confirmDataDeletion = (id) => {
    $('#deleteDataModal').modal('show');
};
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
    fetchData();
    console.log(filter.value);
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

const toggleSelection = (data) => {
    const index = selected.value.indexOf(data);
    if (index === -1) {
        selected.value.push(data);
    } else {
        selected.value.splice(index, 1);
    }
    console.log(selected.value);
};




const selectedAllData = () => {
    if (selectAll.value) {
        selected.value = listData.value.data.map(data => data);
    } else {
        selected.value = []; selectedWall.value = [];
    }
    console.log(selected.value);

}

const editData = (data) => {

    form.value.id = data.id;
    form.value.price = data.credit;
    form.value.priceBefore = data.credit;
    form.value.wallet = data.wallet;
    form.value.account = data.account;
    form.value.desc = data.desc;
    console.log(form.value);
    $('#editDataModal').modal('show');
}
const confirmUpdate = () => {

    if (validate()) {

        $('#editDataModal').modal('hide');
        $('#conEditDataModal').modal('show');
    }
}

const validate = () => {
    var err = 0;

    for (const key in errorsEdit.value) {
        errorsEdit.value[key] = null;
    }

    if (form.value.price == '') {
        errorsEdit.value.price = 'Isi nominal transaksi '
        err += 1;
    }
    if (form.value.desc == '') {
        errorsEdit.value.desc = 'Isi deskripsi transaksi '
        err += 1;
    }
    wallets.value.forEach(element => {
        if (element.wallet_type == form.value.wallet.wallet_type) {
            if (form.value.price > element.sum.saldo + form.value.priceBefore) {
                errorsEdit.value.price = 'Nominal transaksi tidak boleh melebihi saldo dompet '
                err += 1;
            }
        }
    });

    console.log(errorsEdit.value);
    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const update = () => {
    form.value.type = 'credit';
    console.log(form.value);
    console.log(errors.value);

    axios.put(`/api/trans/${form.value.id}`, form.value)
        .then((response) => {
            toastr.success('Pay created successfully!');
            fetchData();
            $('#conEditDataModal').modal('hide');
        })
        .catch((error) => {
            console.log(error);
        })

};


const fetchData = (link = `/api/ledger`) => {
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

    console.log(switchMode.value);
    if (link != null) {
        axios.get(link, {
            params: {
                filter: fil.key,
                value: fil.value,
                mode: !switchMode.value ? 'App\\Models\\Trans' : 'App\\Models\\Bill',
                query: searchQuery.value,
                debit: 0
            }
        }).then((response) => {
            listData.value = response.data;
            selected.value = [];
            selectAll.value = false;
        })
    }

}

const createTrans = (event) => {
    event.preventDefault();

    if (valid()) {
        axios.post('/api/trans', formValue.value)
            .then((response) => {
                clearform();
                toastr.success('Berhasil menambah data Transaksi!');
                fetchData();
                getWallet();
            })
            .catch((error) => {
                {
                    console.log(error)
                }
            })
    }
};



const valid = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (formValue.value.wallet == null) {
        errors.value.wallet = 'Pilih Dompet ';
        err += 1;
    }
    if (formValue.value.wallet.sum.saldo < formValue.value.out) {
        errors.value.out = 'Pengeluaran lebih besar dari saldo';
        err += 1;
    }
    if (formValue.value.account == null) {
        errors.value.account = 'Pilih Akun '
        err += 1;
    }
    if (formValue.value.out == null || 0) {
        errors.value.out = 'Masukkan Jumlah Uang '
        err += 1;
    }
    if (formValue.value.desc == null) {
        errors.value.desc = 'Masukkan Deskripsi '
        err += 1;
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}



const getWallet = () => {

    axios.get(`/api/wallet/list`)
        .then((response) => {
            wallets.value = response.data;
        })

}

const getAccount = async () => {

    try {
        const datas = await axios.get(`/api/account/only`, {
            params: {
                type: 3
            }
        })
        accounts.value = datas.data

    } catch (error) {
        console.error(error);
    }
}
const switchmode = () => {
    for (const key in filter.value) {
        filter.value[key] = null;
    }
    fetchData();
}
watch(searchQuery, debounce(() => {
    fetchData();
}, 300));


onMounted(() => {
    getAccount();
    getWallet();
    fetchData();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Data Pengeluaran</strong></h1>
                    <!-- <p><small>Pembayaran </small></p> -->
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item ">
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Pengeluaran</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <hr>

    <div class="content">
        <div class="container-fluid">
            <div class="row pb-3">
                <router-link to="/admin/billing/debt/create" class="col-md-12">
                    <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Pengeluaran
                        Piutang</button>
                </router-link>
            </div>
            <!-- <button @click="AddPayBill" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Pay Bill
                    </button> -->
            <!-- <button id="delete-selected-rows" class="btn btn-danger" type="button" >DELETE ROW</button> -->

            <form @submit="createTrans" class="mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Akun</label>
                            <VueMultiselect v-model="formValue.account" :option-height="9" :options="accounts"
                                :class="{ 'is-invalid': errors.account }" :close-on-select="true" placeholder="Pilih Satu "
                                label="account_name" track-by="id" :show-labels="false">
                                <template v-slot:option="{ option }">
                                    <div>{{ option.account_name }} - {{ option.id }} </div>
                                </template>
                            </VueMultiselect>
                            <span class="invalid-feedback">{{ errors.account }}</span>
                        </div>
                        <div class="form-group">
                            <label>Dompet</label><br>
                            <VueMultiselect @click="getWallet" v-model="formValue.wallet" :option-height="9"
                                :options="wallets" :class="{ 'is-invalid': errors.account }" :close-on-select="true"
                                placeholder="Pilih Satu " label="wallet_name" track-by="id" :show-labels="false">
                                <template v-slot:option="{ option }">
                                    <div>{{ option.wallet_name }} - {{ formatMoney(option.sum.saldo) }} </div>
                                </template>
                            </VueMultiselect>
                            <span class="invalid-feedback">{{ errors.wallet }}</span>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label><br>
                            <input :class="{ 'is-invalid': errors.out }" @change="handleChange" class="form-control"
                                v-model="formValue.out" type="number" />
                            <span class="invalid-feedback">{{ errors.out }}</span>
                            {{ formatMoney(formValue.out) }}
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label><br>
                            <textarea :class="{ 'is-invalid': errors.desc }" class="form-control"
                                v-model="formValue.desc"></textarea>
                            <span class="invalid-feedback">{{ errors.desc }}</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-100 btn btn-primary">Submit</button>
            </form>
            <hr>
            <div class="row mb-2">
                <div class="col">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" @change="switchmode()" v-model="switchMode" class="custom-control-input"
                            id="customSwitch3">
                        <label class="font-weight-lighter font-italic custom-control-label" for="customSwitch3">Eksternal /
                            Internal</label>
                    </div>
                </div>
            </div>

            <div class="" v-if="switchMode">
                <div class="mb-2 col-md">
                    <div class="input-group w-100 ">
                        <input type="text" v-model="searchQuery" class=" form-control" placeholder="Search..." />
                    </div>
                </div>
                <table class="table table-bordered" style="overflow: auto;width:100%">
                    <thead>
                        <tr>
                            <th>Tanggal
                                <span class="float-right" @click="sort('created_at')">
                                    <i :class="{ 'text-primary': filter.created_at == false }"
                                        class="fas fa-long-arrow-alt-up"></i>
                                    <i :class="{ 'text-primary': filter.created_at == true }"
                                        class="fas fa-long-arrow-alt-down"></i>
                                </span>
                            </th>
                            <th>Pengeluaran</th>
                            <th>Dompet</th>
                            <th>Sumber</th>
                            <th>Deskripsi</th>
                            <th>Operator</th>
                            <!-- <th>Aksi</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data) in listData.data" class="text-center" :key="data.id">

                            <td>{{ formatDate(data.created_at) }}</td>
                            <td v-html="formatDiff(data.wallet.debit, data.wallet.credit)"></td>
                            <td>{{ data.wallet.wallet_name }} </td>
                            <td >{{ data.account.account_name }}</td>
                            <td>
                                <span >Hutang {{ data.santri.fullname }}</span>
                            </td>
                            <td>{{ data.operator.fullname }}</td>
                            <!-- <td>
                                <a href="#" @click="showDetail(data)">
                                </a>
                                <RouterLink :to="`/admin/ledger/${data.id}`">
                                    <i class="fas fa-chevron-right"></i>
                                </RouterLink>
                            </td> -->
                        </tr>
                        <tr v-if="listData.data.length == 0">
                            <td class="text-center" colspan="7">
                                Tidak Ada Data
                            </td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <li v-if="switchMode" v-for="link in listData.links" :key="link.label"
                            :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                            <a class="page-link" v-html="link.label" href="#" @click="fetchData(link.url)"></a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="" v-else>
                <div class="row">
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
                <table class="table table-bordered" style="overflow: auto;width:100%">
                    <thead>
                        <tr>
                            <th class="text-center"><input type="checkbox" v-model="selectAll" @change="selectedAllData" />
                            </th>
                            <th>Tanggal
                                <span class="float-right" @click="sort('created_at')">
                                    <i :class="{ 'text-primary': filter.created_at == false }"
                                        class="fas fa-long-arrow-alt-up"></i>
                                    <i :class="{ 'text-primary': filter.created_at == true }"
                                        class="fas fa-long-arrow-alt-down"></i>
                                </span>
                            </th>
                            <th>Pengeluaran
                                <span class="float-right" @click="sort('credit')">
                                    <i :class="{ 'text-primary': filter.credit == false }"
                                        class="fas fa-long-arrow-alt-up"></i>
                                    <i :class="{ 'text-primary': filter.credit == true }"
                                        class="fas fa-long-arrow-alt-down"></i>
                                </span>
                            </th>
                            <th>Dompet
                                <span class="float-right" @click="sort('wallet_id')">
                                    <i :class="{ 'text-primary': filter.wallet_id == false }"
                                        class="fas fa-long-arrow-alt-up"></i>
                                    <i :class="{ 'text-primary': filter.wallet_id == true }"
                                        class="fas fa-long-arrow-alt-down"></i>
                                </span>
                            </th>
                            <th>Akun
                                <span class="float-right" @click="sort('account_id')">
                                    <i :class="{ 'text-primary': filter.account_id == false }"
                                        class="fas fa-long-arrow-alt-up"></i>
                                    <i :class="{ 'text-primary': filter.account_id == true }"
                                        class="fas fa-long-arrow-alt-down"></i>
                                </span>
                            </th>
                            <th>Deskripsi</th>
                            <th>Operator</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data) in listData.data" class="text-center" :key="data.id">
                            <td class="text-center"><input type="checkbox" :checked="selectAll"
                                    @change="toggleSelection(data)" />
                            </td>
                            <td>{{ formatDate(data.created_at) }}</td>
                            <td v-html="formatDiff(data.wallet.debit, data.wallet.credit)"></td>
                            <td>{{ data.wallet.wallet_name }} </td>
                            <td>{{ data.account.account_name }}</td>
                            <td>{{ data.desc }}</td>
                            <td>{{ data.operator.fullname }}</td>
                            <td>
                                <a href="#" @click="editData(data)">
                                    <i class="fa fa-edit mr-2"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="text-center" v-if="listData.data.length == 0">
                            <td colspan="8">Tidak ada data</td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <li v-for="link in listData.links" :key="link.label"
                            :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                            <a class="page-link" v-html="link.label" href="#" @click="fetchData(link.url)"></a>
                        </li>
                    </ul>
                </nav>

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
                        <div class="col-5">Deskripsi </div>
                        <div class="col"> : {{ form.desc }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5">Akun </div>
                        <div v-if="form.account != null" class="col"> : {{ form.account.account_name }}</div>
                    </div>
                    <div class="row">
                        <div class="col-5">Dompet </div>
                        <div v-if="form.wallet != null" class="col"> : {{ form.wallet.wallet_name }}
                        </div>
                    </div>
                    <span v-if="form.price != form.priceBefore">
                        <div class="row">
                            <div class="col-5">Jumlah Nominal Sebelum</div>
                            <div class="col"> : {{ formatMoney(form.priceBefore) }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5">Jumlah Nominal Sesudah</div>
                            <div class="col"> : {{ formatMoney(form.price) }}</div>
                        </div>
                    </span>
                    <span v-else>
                        <div class="row">
                            <div class="col-5">Jumlah Nominal</div>
                            <div class="col"> : {{ formatMoney(form.price) }}</div>
                        </div>
                    </span>

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
                        <span>Ubah Data Transaksi Eksternal</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errorsEdit.desc }"
                                        v-model="form.desc" type="text" />
                                    <span class="invalid-feedback">{{ errorsEdit.desc }}</span>
                                </div>

                            </div>
                        </div>
                        <div class="row">


                            <div class="col">
                                <div class="form-group">
                                    <label>Nominal Transaksi</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errorsEdit.price }"
                                        v-model="form.price" type="number" />
                                    <span class="invalid-feedback">{{ errorsEdit.price }}</span>
                                    <p>{{ formatMoney(form.price) }}</p>
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

