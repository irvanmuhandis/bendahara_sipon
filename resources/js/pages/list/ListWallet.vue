<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { formatDate, formatMoney, formatDiff } from "../../helper";
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { event } from "jquery";

const toastr = useToastr();
const listwallets = ref({ 'data': [{ 'id': 0 }] });
const editing = ref(false);
const formValues = ref({
    'name': '',
    'debit': '',
    'credit': ''
});
const errors = ref({
    'name': null,
    'debit': null,
    'credit': null
});
const inout = ref();
const types = ref([]);
const IdBeingDeleted = ref({
    'id': 0
});

const searchQuery = ref(null);
const filter = ref({
    'created_at': null,
    'saldo': null,
    'debit': null,
    'credit': null,
    'wallet_name': null
})

const resetForm = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
}
const valid = () => {
    var err = 0;
    resetForm();

    if (formValues.value.name == '') {
        errors.value.name = "Masukkan nama dompet !!";
        err += 1;
    }
    if (inout.value == 1) {
        if (formValues.value.debit == '') {
            errors.value.debit = "Masukkan jumlah pemasukan !!";
            err += 1;
        }
    }
    else {
        if (formValues.value.credit == '') {
            errors.value.credit = "Masukkan jumlah pengeluaran !!";
            err += 1;
        }
    }

    console.log(inout.value);
    console.log(errors.value);
    if (err == 0) {
        return true;
    }

}


const createWallet = () => {

    console.log(formValues.value);
    if (valid()) {
        axios.get('/csrf-token').then(() => {
            axios.post('/api/wallet', formValues.value)
                .then((response) => {
                    $('#FormModal').modal('hide');
                    fetchData();
                    getWalletType();
                    resetForm();
                    toastr.success('Dompet berhasil dibuat !');
                })
                .catch((error) => {
                    console.log(error);
                })
        })
    }
};

const AddWallet = () => {
    editing.value = false;
    inout.value = 1;
    resetForm();
    $('#FormModal').modal('show');
};



const editWallet = (wallet) => {
    editing.value = true;
    if (wallet.debit == 0) {
        inout.value = 0;
    } else {
        inout.value = 1;
    }
    resetForm();
    formValues.value = {
        id: wallet.id,
        name: wallet.wallet_name,
        credit: wallet.credit,
        debit: wallet.debit
    };
    $('#FormModal').modal('show');
};

const updateWallet = () => {

    console.log(formValues.value);
    if (valid()) {
        axios.put('/api/wallet/' + formValues.value.id, formValues.value)
            .then((response) => {
                fetchData();
                getWalletType();
                $('#FormModal').modal('hide');
                toastr.success('Dompet berhasil diupdate !');
            }).catch((error) => {
                console.log(error);
            });
    }

}

const handleSubmit = (event) => {
    // console.log(actions);
    event.preventDefault();
    if (editing.value) {
        updateWallet();
    } else {
        createWallet();
    }
}




const getWalletType = () => {

    axios.get(`/api/wallet/list`).then((response) => {
        types.value = response.data;

    })

}


const confirmWalletDeletion = () => {
    $('#deleteModal').modal('show');
};

const deleteWallet = () => {
    axios.post(`/api/wallet/delete`, IdBeingDeleted.value)
        .then(() => {
            $('#deleteModal').modal('hide');
            toastr.success('Wallet deleted successfully!');
            searchQuery.value = null;
            fetchData();
            IdBeingDeleted.value = null;
            getWalletType();
        });
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


watch(searchQuery, debounce(() => {
    fetchData();
}, 300));

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
                value: fil.value,
                query: searchQuery.value
            }
        }).then((response) => {
            listwallets.value = response.data;
        })
    }

}

onMounted(() => {
    fetchData();
    // getTest();
    getWalletType();
})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong> Wallet | Dompet</strong></h1>
                    <p><small>List sumber keuangan pondok pesantren</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/">Beranda</router-link></li>
                        <li class="breadcrumb-item active">Dompet</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-3">
                <div class="d-flex">

                    <button @click="AddWallet" type="button" class="btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Tambah Dompet
                    </button>

                    <div class="row ml-2">
                        <button class="col w-100 btn btn-outline-danger" type="button" data-toggle="collapse"
                            data-target="#collapseWidthExample" aria-expanded="false" aria-controls="collapseWidthExample">
                            Hapus Dompet <i class="ml-1 right fas fa-trash"></i>
                        </button>
                        <div class="collapse m-0 p-0 " id="collapseWidthExample">

                            <div class="row">
                                <div class="col-10 ">

                                    <VueMultiselect class="ml-2" v-model="IdBeingDeleted" :option-height="9"
                                        @input="test(IdBeingDeleted)" @select="test(IdBeingDeleted)"
                                        @remove="test(IdBeingDeleted)" :options="types" :multiple="false"
                                        :close-on-select="true" placeholder="Pilih Satu " label="wallet_name" track-by="id"
                                        :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.wallet_name }} </div>
                                        </template>
                                    </VueMultiselect>


                                </div>
                                <div class="col-2">
                                    <button type="button" @click="confirmWalletDeletion"
                                        class="btn btn-danger">Hapus</button>
                                </div>

                            </div>
                        </div>
                        <!-- Modal -->
                        <div class="modal" tabindex="-1" role="dialog" id="deleteModal">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">WARNING</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">

                                        <p>Apakah Kamu Yakin Ingin Menghapus seluruh Data {{
                                            IdBeingDeleted == null ? "---" : IdBeingDeleted.wallet_name }} ?</p>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                                        <button type="button" class="btn btn-primary" @click="deleteWallet">Ya, saya yakin
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">

                    <table class="display table table-bordered dataTables">
                        <thead>
                            <tr>
                                <th scope="col">Dibuat
                                    <span class="float-right" @click="sort('created_at')">
                                        <i :class="{ 'text-primary': filter.created_at == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.created_at == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th scope="col">Nama Dompet
                                    <span class="float-right" @click="sort('wallet_name')">
                                        <i :class="{ 'text-primary': filter.wallet_name == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.wallet_name == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th scope="col">Pemasukan
                                    <span class="float-right" @click="sort('debit')">
                                        <i :class="{ 'text-primary': filter.debit == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.debit == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th scope="col">Pengeluaran
                                    <span class="float-right" @click="sort('credit')">
                                        <i :class="{ 'text-primary': filter.credit == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.credit == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th>Saldo
                                    <span class="float-right" @click="sort('saldo')">
                                        <i :class="{ 'text-primary': filter.saldo == false }"
                                            class="fas fa-long-arrow-alt-up"></i>
                                        <i :class="{ 'text-primary': filter.saldo == true }"
                                            class="fas fa-long-arrow-alt-down"></i>
                                    </span>
                                </th>
                                <th scope="col">Aksi</th>
                            </tr>

                        </thead>
                        <tbody>
                            <tr v-for="(wal) in listwallets.data" :key="wal.id">
                                <td>{{ formatDate(wal.created_at) }}</td>
                                <td>{{ wal.wallet_name }}</td>
                                <td v-if="wal.debit != 0">{{ formatMoney(wal.debit) }}</td>
                                <td v-else>-</td>
                                <td v-if="wal.credit != 0">{{ formatMoney(wal.credit) }}</td>
                                <td v-else>-</td>
                                <td>{{ formatMoney(wal.saldo) }}</td>
                                <td class="text-center">
                                    <a href="#" @click="editWallet(wal)">
                                        <i class="fa fa-edit mr-2"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="listwallets.data.length == 0">
                                <td class="text-center" colspan="6">Tidak ada data</td>
                            </tr>
                        </tbody>
                    </table>

                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li v-for="link in listwallets.links" :key="link.label"
                                :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                                <a class="page-link" v-html="link.label" href="#" @click="fetchData(link.url)"></a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

        </div>
    </div>



    <div class="modal fade" id="FormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-if="editing" class="modal-title" id="staticBackdropLabel">
                        Edit Dompet
                    </h5>
                    <h5 v-else class=" modal-title" id="staticBackdropLabel">
                        Tambah Dompet
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit="handleSubmit">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="desc">Nama Dompet</label>
                            <input v-model="formValues.name" type="text" class="form-control "
                                :class="{ 'is-invalid': errors.name }" id="desc" aria-describedby="nameHelp"
                                placeholder="Masukkan nama dompet" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group" v-if="inout == 1">
                            <label>Pemasukan</label>
                            <input v-model="formValues.debit" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.debit }" placeholder="Masukkan Jumlah Pemasukan" />
                            <p>{{ formatMoney(formValues.debit) }}</p>
                            <span class="invalid-feedback">{{ errors.debit }}</span>
                        </div>
                        <div class="form-group" v-else>
                            <label>Pengeluaran</label>
                            <input v-model="formValues.credit" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.credit }" placeholder="Masukkan Jumlah Pengeluaran" />
                            <p>{{ formatMoney(formValues.credit) }}</p>
                            <span class="invalid-feedback">{{ errors.credit }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

