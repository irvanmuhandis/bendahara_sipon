<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { formatDate, formatMoney } from "../../helper";
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const listwallets = ref({ 'data': [{ 'id': 0 }] });
const editing = ref(false);
const formValues = ref({
    'saldo': 0
});
const form = ref(null);
const searchQuery = ref(null);
const types = ref([]);
const IdBeingDeleted = ref({
    'id':0
});
const saldo = ref(null);

const createWalletSchema = yup.object({
    saldo: yup.number().required('Saldo harus diisi !'),
    name: yup.string().required('Nama dompet harus diisi !')
});

const editWalletSchema = yup.object({
    saldo: yup.number().required('Saldo harus diisi !'),
    name: yup.string().required('Nama dompet harus diisi !')
});

const saldoChange = (event) => {
    saldo.value = formatMoney(event.target.value);
}

const createWallet = (values, { resetForm, setErrors }) => {
    axios.get('/csrf-token').then(() => {
        axios.post('/api/wallet', values)
            .then((response) => {
                $('#FormModal').modal('hide');
                getWallet();
                getWalletType();
                resetForm();
                toastr.success('Dompet berhasil dibuat !');
            })
            .catch((error) => {
                if (error.response.data.errors) {
                    setErrors(error.response.data.errors);
                }
            })
    })

};

const AddWallet = () => {
    editing.value = false;
    formValues.value = {};
    $('#FormModal').modal('show');
};



const editWallet = (wallet) => {
    editing.value = true;
    form.value.resetForm();
    saldo.value = formatMoney(wallet.saldo);
    $('#FormModal').modal('show');
    formValues.value = {
        id: wallet.id,
        name: wallet.wallet_name,
        saldo: wallet.saldo,
    };
};

const updateWallet = (values, { setErrors }) => {
    values.id = formValues.value.id;
    axios.put('/api/wallet/' + formValues.value.id, values)
        .then((response) => {
            getWallet();
            getWalletType();
            $('#FormModal').modal('hide');
            toastr.success('Wallet updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateWallet(values, actions);
    } else {
        createWallet(values, actions);
    }
}

const search = (page = 1) => {
    const param = {};
    param.query = searchQuery.value

    axios.get(`/api/wallet/search?page=${page}`, {
        params: param
    })
        .then(response => {
            listwallets.value = response.data;
            selectedWallet.value = [];
            selectAll.value = false;
        })
        .catch(error => {
            console.log(error);
        })
};

watch(searchQuery, debounce(() => {
    search();
}, 300));


const getWallet = (page = 1) => {

    axios.get(`/api/wallet?page=${page}`).then((response) => {
        listwallets.value = response.data;
    })

}

const getWalletType = () => {

    axios.get(`/api/wallet/list`).then((response) => {
        types.value = response.data;

    })

}

const getTest = () => {

    axios.get('/sanctum/csrf-cookie').then(response => {
        console.log(response);
        axios.post('/api/wallet', values)
            .then((response) => {
                listwallets.value.data.unshift(response.data);
                $('#FormModal').modal('hide');
                resetForm();
                toastr.success('Dompet berhasil dibuat !');
            })
            .catch((error) => {
                if (error.response.data.errors) {
                    setErrors(error.response.data.errors);
                }
            })
    });

    axios.get('/test').then(response => {
        console.log(response);
    });
}

const confirmWalletDeletion = () => {
    $('#deleteModal').modal('show');
};

const deleteWallet = () => {
    axios.post(`/api/wallet/delete`,IdBeingDeleted.value)
        .then(() => {
            $('#deleteModal').modal('hide');
            toastr.success('Wallet deleted successfully!');
            getWallet();
            IdBeingDeleted.value = null;
            getWalletType();
        });
};

const test = (x) => {
    console.log(x)
}



onMounted(() => {
    getWallet();
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
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Wallets</li>
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
                                    <button type="button" @click="confirmWalletDeletion" class="btn btn-danger">Hapus</button>
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
                                <th scope="col">Nama Dompet</th>
                                <th scope="col">Saldo Sebelumnya</th>
                                <th scope="col">Saldo</th>
                                <th scope="col">Dibuat</th>
                                <th scope="col">Aksi</th>
                            </tr>

                        </thead>
                        <tbody>
                            <tr v-for="(wal) in listwallets.data" :key="wal.id">

                                <td>{{ wal.wallet_name }}</td>
                                <td>{{ formatMoney(wal.prev_saldo) }}</td>
                                <td>{{ formatMoney(wal.saldo) }}</td>
                                <td>{{ formatDate(wal.created_at) }}</td>
                                <td class="text-center">
                                    <a href="#" @click="editWallet(wal)">
                                        <i class="fa fa-edit mr-2"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Bootstrap4Pagination :data="listwallets" @pagination-change-page="getWallet" />

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
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editWalletSchema : createWalletSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">


                        <div class="form-group">
                            <label for="desc">Nama Dompet</label>
                            <Field name="name" type="text" class="form-control " :class="{ 'is-invalid': errors.name }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group">
                            <label>Saldo</label>
                            <Field name="saldo" @change="saldoChange" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.saldo }" id="pay_at" aria-describedby="nameHelp"
                                placeholder="Enter Pay Date" />
                            <p>{{ saldo }}</p>
                            <span class="invalid-feedback">{{ errors.saldo }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>

