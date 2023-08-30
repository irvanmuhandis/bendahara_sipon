<script setup>
import axios from 'axios';
import { ref, onMounted, reactive, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import AccountListItem from '../account/AccountListItem.vue';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const accounts = ref({ 'data': [] });
const editing = ref(false);
const formValues = ref();
const form = ref(null);
const accountIdBeingDeleted = ref(null);
const selectAll = ref(false);
const searchQuery = ref(null);
const selectedAccounts = ref([]);

const fetchData = (link) => {

if (link != null) {
    axios.get(link).then((response) => {
        accounts.value = response.data;
    })
}

}

const confirmAccountDeletion = (id) => {
    accountIdBeingDeleted.value = id;
    $('#deleteAccountModal').modal('show');
};

const deleteAccount = () => {
    axios.delete(`/api/accounts/${accountIdBeingDeleted.value}`)
        .then(() => {
            $('#deleteAccountModal').modal('hide');
            toastr.success('Account deleted successfully!');
            accountDeleted(accountIdBeingDeleted.value)
        });
};

const getAccounts = (page = 1) => {
    axios.get(`/api/account`)
        .then((response) => {
            accounts.value = response.data;
            selectedAccounts.value = [];
            selectAll.value = false;
        })
}

const createAccountSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().required().min(8),
});

const editAccountSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().when((password, schema) => {
        return password ? schema.required().min(8) : schema;
    }),
});

const createAccount = (values, { resetForm, setErrors }) => {
    axios.post('/api/accounts', values)
        .then((response) => {
            accounts.value.data.unshift(response.data);
            $('#accountFormModal').modal('hide');
            resetForm();
            toastr.success('Account created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const addAccount = () => {
    editing.value = false;
    $('#accountFormModal').modal('show');
};

const editAccount = (account) => {
    editing.value = true;
    form.value.resetForm();
    $('#accountFormModal').modal('show');
    formValues.value = {
        id: account.id,
        name: account.name,
        email: account.email,
    };
};

const updateAccount = (values, { setErrors }) => {
    axios.put('/api/accounts/' + formValues.value.id, values)
        .then((response) => {
            const index = accounts.value.data.findIndex(account => account.id === response.data.id);
            accounts.value.data[index] = response.data;
            $('#accountFormModal').modal('hide');
            toastr.success('Account updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateAccount(values, actions);
    } else {
        createAccount(values, actions);
    }
}

const accountDeleted = (accountId) => {
    accounts.value.data = accounts.value.data.filter(account => account.id !== accountId);
};


const search = () => {
    axios.get('/api/accounts/search', {
        params: {
            query: searchQuery.value
        }
    })
        .then(response => {
            accounts.value = response.data;
        })
        .catch(error => {
            console.log(error);
        })
};

const toggleSelection = (account) => {
    const index = selectedAccounts.value.indexOf(account.id);
    if (index === -1) {
        selectedAccounts.value.push(account.id);
    } else {
        selectedAccounts.value.splice(index, 1);
    }
    console.log(selectedAccounts.value);
};

const bulkDelete = () => {
    axios.delete('/api/accounts', {
        data: {
            ids: selectedAccounts.value
        }
    })
        .then(response => {
            accounts.value.data = accounts.value.data.filter(account => !selectedAccounts.value.includes(account.id));
            selectedAccounts.value = [];
            selectAll.value = false;
            toastr.success(response.data.message);
        });
};


const selectAllAccounts = () => {
    if (selectAll.value) {
        selectedAccounts.value = accounts.value.data
            .filter(account => account.deletable === 1)
            .map(account => account.id);
    } else {
        selectedAccounts.value = [];
    }
    console.log(selectedAccounts.value);
}

watch(searchQuery, debounce(() => {
    search();
}, 300));

onMounted(() => {
    getAccounts();
});
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong> Accounts | Santri</strong></h1>
                    <p><small>List santri</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Accounts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <button @click="addAccount" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Account
                    </button>
                    <div v-if="selectedAccounts.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                            <i class="fa fa-trash mr-1"></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedAccounts.length }} accounts</span>
                    </div>
                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                </div>
            </div>
            <table class="table table-bordered ">
                <thead>
                    <tr>
                        <th><input type="checkbox" v-model="selectAll" @change="selectAllAccounts" /></th>
                        <th>Nama</th>
                        <th>Dibuat</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody v-if="accounts.data.length > 0" class="text-center">
                    <AccountListItem v-for="(account, index) in accounts.data" :key="account.id" :account=account
                        :index=index @edit-account="editAccount" @confirm-account-deletion="confirmAccountDeletion"
                        @toggle-selection="toggleSelection" :select-all="selectAll" />
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="6" class="text-center">No results found...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li v-for="link in accounts.links" :key="link.label"
                :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                <a class="page-link" v-html="link.label" href="#" @click="fetchData(link.url)"></a>
            </li>
        </ul>
    </nav>

    <div class="modal fade" id="deleteAccountModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Account</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this account ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteAccount" type="button" class="btn btn-primary">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="accountFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Account</span>
                        <span v-else>Add New Account</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit"
                    :validation-schema="editing ? editAccountSchema : createAccountSchema" v-slot="{ errors }"
                    :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <Field name="name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }"
                                id="name" aria-describedby="nameHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <Field name="email" type="email" class="form-control " :class="{ 'is-invalid': errors.email }"
                                id="email" aria-describedby="nameHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback">{{ errors.email }}</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Password</label>
                            <Field name="password" type="password" class="form-control "
                                :class="{ 'is-invalid': errors.password }" id="password" aria-describedby="nameHelp"
                                placeholder="Enter password" />
                            <span class="invalid-feedback">{{ errors.password }}</span>
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
