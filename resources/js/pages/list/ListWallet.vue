<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const listwallets = ref({ 'data': [{ 'id': 0 }] });
const users = ref([]);
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);
const walletIdBeingDeleted = ref(null);
const searchQuery = ref(null);
const selectAll = ref(false);
const selectedWallet = ref([]);

const confirmWalletDeletion = (id) => {
    walletIdBeingDeleted.value = id;
    $('#deleteWalletModal').modal('show');
};

const deleteWallet = () => {
    console.log(walletIdBeingDeleted.value);
    axios.delete(`/api/wallet/${walletIdBeingDeleted.value}`, {
        data: {
            id: walletIdBeingDeleted.value
        }
    })
        .then((response) => {
            $('#deleteWalletModal').modal('hide');
            toastr.success('Wallet deleted successfully!');
            getWallet();
            //walletDeleted(walletIdBeingDeleted.value)
        });
};

const bulkDelete = () => {
    console.log(selectedWallet.value);
    axios.delete('/api/wallet', {
        data: {
            ids: selectedWallet.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            getWallet();
        });
};


const createWalletSchema = yup.object({
    saldo: yup.number().required(),
    name: yup.string().required()
});

const editWalletSchema = yup.object({
    saldo: yup.number().required(),
    name: yup.string().required()
});

const createWallet = (values, { resetForm, setErrors }) => {
    axios.post('/api/wallet', values)
        .then((response) => {
            listwallets.value.data.unshift(response.data);
            $('#walletFormModal').modal('hide');
            resetForm();
            toastr.success('Wallet created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const AddWallet = () => {
    editing.value = false;
    formValues.value = {};
    $('#walletFormModal').modal('show');
};



const editWallet = (wallet) => {
    editing.value = true;
    form.value.resetForm();
    $('#walletFormModal').modal('show');
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
            $('#walletFormModal').modal('hide');
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

const walletDeleted = (walletId) => {
    listwallets.value.data = listwallets.value.data.filter(wallet => wallet.id !== walletId);
};


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

const toggleSelection = (wallet) => {
    const index = selectedWallet.value.indexOf(wallet.id);
    if (index === -1) {
        selectedWallet.value.push(wallet.id);
    } else {
        selectedWallet.value.splice(index, 1);
    }
    console.log(selectedWallet.value);
};




const selectAllWallets = () => {
    if (selectAll.value) {
        selectedWallet.value = listwallets.value.data.map(wallet => wallet.id);
    } else {
        selectedWallet.value = [];
    }
    console.log(selectedWallet.value);
}

watch(searchQuery, debounce(() => {
    search();
}, 300));


const wallet_count = computed(() => {
    return wallet_status.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
})


const getWallet = (page = 1) => {

    axios.get(`/api/wallet?page=${page}`).then((response) => {
        listwallets.value = response.data;
        console.log(listwallets.value.data);
        selectedWallet.value = [];
        selectAll.value = false;
    })

}

onMounted(() => {
    getWallet();

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

                    <button @click="AddWallet" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Wallet
                    </button>

                    <div v-if="selectedWallet.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                            <i class="fa fa-trash mr-1"></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedWallet.length }} wallets</span>
                    </div>
                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <table class="display table table-bordered dataTables">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" v-model="selectAll" @change="selectAllWallets" /></th>
                                        <th scope="col">Wallet Name</th>
                                        <th scope="col">Saldo</th>
                                        <th scope="col">Created At</th>
                                        <th scope="col">Updated At</th>
                                        <th scope="col">Action</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr  v-for="(wal) in listwallets.data" :key="wal.id">
                                        <td class="text-center"><input type="checkbox" :checked="selectAll" @change="toggleSelection(wallet)" />
                                        </td>
                                        <td>{{ wal.wallet_name }}</td>
                                        <td>{{ wal.saldo }}</td>
                                        <td>{{ wal.created_at }}</td>
                                        <td>{{ wal.updated_at }}</td>
                                        <td class="text-center">
                                            <a href="#" @click="editWallet(wal)">
                                                <i class="fa fa-edit mr-2"></i>
                                            </a>

                                            <a href="#" @click="confirmWalletDeletion(wal.id)">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                    <Bootstrap4Pagination :data="listwallets" @pagination-change-page="search" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteWalletModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Wallet</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this wallet ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteWallet" type="button" class="btn btn-primary">Delete Wallet</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="walletFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Wallet</span>
                        <span v-else>Add New Wallet</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editWalletSchema : createWalletSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">


                        <div class="form-wallet">
                            <label for="desc">Nama Dompet</label>
                            <Field name="name" type="text" class="form-control " :class="{ 'is-invalid': errors.name }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-wallet">
                            <label>Saldo</label>
                            <Field name="saldo" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.saldo }" id="pay_at" aria-describedby="nameHelp"
                                placeholder="Enter Pay Date" />
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
<script>
export default {
    // computed property to retrieve current page number
    computed: {
        currentPage() {
            return this.listwallets.current_page;
        }
    },

    // methods to handle pagination events
    methods: {
        changePage(page) {
            this.search(page);
        }
    }
}
</script>
