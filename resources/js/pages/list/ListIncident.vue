<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';


const toastr = useToastr();
const listdebts = ref({ 'data': [] });
const debt_status = ref([]);
const users = ref([]);
const selectedStatus = ref();
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);
const debtIdBeingDeleted = ref(null);
const searchQuery = ref(null);
const selectAll = ref(false);
const selectedDebt = ref([]);

const confirmDebtDeletion = (id) => {
    debtIdBeingDeleted.value = id;
    $('#deleteDebtModal').modal('show');
};

const deleteDebt = () => {
    console.log(debtIdBeingDeleted.value);
    axios.delete(`/api/debt/${debtIdBeingDeleted.value}`,{
        data: {
            id: debtIdBeingDeleted.value
        }
    })
        .then((response) => {
            $('#deleteDebtModal').modal('hide');
            toastr.success('Debt deleted successfully!');
            getDebt();
            //debtDeleted(debtIdBeingDeleted.value)
        });
};

const bulkDelete = () => {
    console.log(selectedDebt.value);
    axios.delete('/api/debt', {
        data: {
            ids: selectedDebt.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            getDebt();
        });
};


const createDebtSchema = yup.object({
    user_id: yup.string().required(),
    status: yup.number().required(),
    debt: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required()
});

const editDebtSchema = yup.object({
    user_id: yup.string().required(),
    debt: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required(),
    status: yup.number().when((status, schema) => {
        return status ? schema.required() : schema;
    }),
});

const createDebt = (values, { resetForm, setErrors }) => {
    axios.post('/api/debt', values)
        .then((response) => {
            listdebts.value.data.unshift(response.data);
            $('#debtFormModal').modal('hide');
            resetForm();
            toastr.success('Debt created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const AddDebt = () => {
    editing.value = false;
    $('#debtFormModal').modal('show');
};

const getUser = () => {
    axios.get(`/api/userlist`)
        .then((response) => {
            users.value = response.data;
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}


const editDebt = (debt) => {
    editing.value = true;
    form.value.resetForm();
    getUser();
    $('#debtFormModal').modal('show');
    formValues.value = {
        id: debt.id,
        remainder: debt.remainder,
        user_id: debt.user_id,
        status: debt.status,
        title: debt.title,
        debt: debt.debt
    };
};

const updateDebt = (values, { setErrors }) => {
    axios.put('/api/debt/' + formValues.value.id, values)
        .then((response) => {
            // const index = debts.value.data.findIndex(debt => debt.id === response.id);
            // listdebts.value.data[index] = response.data;
            getDebt();
            $('#debtFormModal').modal('hide');
            toastr.success('Debt updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateDebt(values, actions);
    } else {
        createDebt(values, actions);
    }
}

const debtDeleted = (debtId) => {
    listdebts.value.data = listdebts.value.data.filter(debt => debt.id !== debtId);
};


const search = (page = 1, status = selectedStatus.value) => {
    const param = {};
    param.query = searchQuery.value
    param.status = status;

    axios.get(`/api/debt/search?page=${page}`, {
        params: param
    })
        .then(response => {
            listdebts.value = response.data;
            selectedDebt.value = [];
            selectAll.value = false;
        })
        .catch(error => {
            console.log(error);
        })
};

const toggleSelection = (debt) => {
    const index = selectedDebt.value.indexOf(debt.id);
    if (index === -1) {
        selectedDebt.value.push(debt.id);
    } else {
        selectedDebt.value.splice(index, 1);
    }
    console.log(selectedDebt.value);
};




const selectAllDebts = () => {
    if (selectAll.value) {
        selectedDebt.value = listdebts.value.data.map(debt => debt.id);
    } else {
        selectedDebt.value = [];
    }
    console.log(selectedDebt.value);
}

watch(searchQuery, debounce(() => {
    changeStatus();
}, 300));


const debt_count = computed(() => {
    return debt_status.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
})

const changeStatus = (page, status) => {
    if (status) {
        selectedStatus.value = status;
    }
    search(page, selectedStatus.value);
}

const getDebtStatus = () => {
    axios.get('/api/debt_status')
        .then((response) => {
            debt_status.value = response.data;
        })
}

const getDebt = (page = 1, status) => {
    const params = {};
    selectedStatus.value = status;

    if (status) {
        params.status = status;
    }
    axios.get(`/api/debt?page=${page}`, {
        params: params
    }).then((response) => {
        listdebts.value = response.data;
        selectedDebt.value = [];
        selectAll.value = false;
    })

}

onMounted(() => {
    getDebt();
    getDebtStatus();
})


</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Incidential | Dana Insidential</strong></h1>
                    <p><small>List hutang di pondok pesantren</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Debts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">

        </div>
    </div>

    <div class="modal fade" id="deleteDebtModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Debt</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this debt ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteDebt" type="button" class="btn btn-primary">Delete Debt</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="debtFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Debt</span>
                        <span v-else>Add New Debt</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editDebtSchema : createDebtSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name User</label>
                            <Field @click="getUser" name="user_id" as="select" class="form-control"
                                :class="{ 'is-invalid': errors.user_id }" id="user_id" aria-describedby="nameHelp"
                                placeholder="Enter full name">
                                <option value="" disabled>Select a Name</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>

                            </Field>
                            <span class="invalid-feedback">{{ errors.user_id }}</span>
                        </div>

                        <div class="form-group">
                            <label for="desc">Title</label>
                            <Field name="title" type="text" class="form-control " :class="{ 'is-invalid': errors.title }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.title }}</span>
                        </div>

                        <div class="form-group">
                            <label>Pay At</label>
                            <Field name="debt" type="number" class="form-control " :class="{ 'is-invalid': errors.debt }"
                                id="pay_at" aria-describedby="nameHelp" placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.debt }}</span>
                        </div>

                        <div class="form-group">
                            <label>Remainder</label>
                            <Field name="remainder" type="number" class="form-control "
                                :class="{ 'is-invalid': errors.remainder }" id="pay_at" aria-describedby="nameHelp"
                                placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.remainder }}</span>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <Field name="status" as="select" class="form-control" :class="{ 'is-invalid': errors.status }"
                                id="status" aria-describedby="nameHelp" placeholder="Enter Status">
                                <option value="" disabled>Select a Status</option>
                                <option v-for="status in debt_status" :key="status.value" :value="status.value" >{{
                                    status.name }}</option>

                            </Field>
                            <span class="invalid-feedback">{{ errors.status }}</span>
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
            return this.listdebts.current_page;
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
