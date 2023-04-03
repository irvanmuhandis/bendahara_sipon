<script setup>
import axios from 'axios';
import { ref, onMounted, reactive, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import DispenListItem from '../dispen/DispenListItem.vue';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const dispens = ref({ 'data': [] });
const users = ref([]);
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);
const dispenIdBeingDeleted = ref(null);
const searchQuery = ref(null);
const selectAll = ref(false);
const selectedDispen = ref([]);


const confirmDispenDeletion = (id) => {
    dispenIdBeingDeleted.value = id;
    $('#deleteDispenModal').modal('show');
};

const deleteDispen = () => {
    axios.delete(`/api/dispens/${dispenIdBeingDeleted.value}`)
        .then(() => {
            $('#deleteDispenModal').modal('hide');
            toastr.success('Dispen deleted successfully!');
            getDispen();
            //dispenDeleted(dispenIdBeingDeleted.value)
        });
};

const getDispen = (page = 1) => {
    axios.get(`/api/dispens?page=${page}`)
        .then((response) => {
            dispens.value = response.data;
            selectedDispen.value = [];
            selectAll.value = false;
        })
}

const createDispenSchema = yup.object({
    userId: yup.string().required(),
    pay_at: yup.date().required(),
    periode: yup.date().required(),
    desc: yup.string().required(),
});

const editDispenSchema = yup.object({
    userId: yup.string().required(),
    periode: yup.date().required(),
    desc: yup.string().required(),
    pay_at: yup.date().when((pay_at, schema) => {
        return pay_at ? schema.required() : schema;
    }),
});

const createDispen = (values, { resetForm, setErrors }) => {
    axios.post('/api/dispens', values)
        .then((response) => {
            dispens.value.data.unshift(response.data);
            $('#dispenFormModal').modal('hide');
            resetForm();
            toastr.success('Dispen created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const AddDispen = () => {
    editing.value = false;
    $('#dispenFormModal').modal('show');
};

const getUser = () => {
    axios.get(`/api/userlist`)
        .then((response) => {
            users.value = response.data;
        })
}


const editDispen = (dispen) => {
    editing.value = true;
    form.value.resetForm();
    getUser();
    $('#dispenFormModal').modal('show');
    formValues.value = {
        id: dispen.id,
        name: dispen.name,
        userId: dispen.userId,
        pay_at: dispen.pay_at,
        periode: dispen.periode,
        desc: dispen.desc
    };
};

const updateDispen = (values, { setErrors }) => {
    axios.put('/api/dispens/' + formValues.value.id, values)
        .then((response) => {
            const index = dispens.value.data.findIndex(dispen => dispen.id === response.id);
            dispens.value.data[index] = response.data;
            $('#dispenFormModal').modal('hide');
            toastr.success('Dispen updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });;
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateDispen(values, actions);
    } else {
        createDispen(values, actions);
    }
}

const dispenDeleted = (dispenId) => {
    dispens.value.data = dispens.value.data.filter(dispen => dispen.id !== dispenId);
};


const search = (page = 1) => {
    axios.get(`/api/dispens/search?page=${page}`, {
        params: {
            query: searchQuery.value
        }
    })
        .then(response => {
            dispens.value = response.data;
        })
        .catch(error => {
            console.log(error);
        })
};
const toggleSelection = (dispen) => {
    const index = selectedDispen.value.indexOf(dispen.id);
    if (index === -1) {
        selectedDispen.value.push(dispen.id);
    } else {
        selectedDispen.value.splice(index, 1);
    }
    console.log(selectedDispen.value);
};

const bulkDelete = () => {
    axios.delete('/api/dispens', {
        data: {
            ids: selectedDispen.value
        }
    })
        .then(response => {
            // dispens.value.data = dispens.value.data.filter(dispen => !selectedDispen.value.includes(dispen.id));
            // selectedDispen.value = [];
            // selectAll.value = false;
            toastr.success(response.data.message);
            getDispen();
        });
};


const selectAllDispens = () => {
    if (selectAll.value) {
        selectedDispen.value = dispens.value.data.map(dispen => dispen.id);
    } else {
        selectedDispen.value = [];
    }
    console.log(selectedDispen.value);
}

watch(searchQuery, debounce(() => {
    search();
}, 300));

onMounted(() => {
    getDispen();
});
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"> <strong> Dispensation | Dispensasi</strong> </h1>
                    <p><small>List santri yang dispensasi</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dispens</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <button @click="AddDispen" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Dispen
                    </button>
                    <div v-if="selectedDispen.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                            <i class="fa fa-trash mr-1"></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedDispen.length }} dispens</span>
                    </div>
                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll" @change="selectAllDispens" /></th>
                                <th style="width: 10px">#</th>
                                <th>Name</th>
                                <th>Periode</th>
                                <th>Pay At</th>
                                <th>Description</th>
                                <th>Updated At</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody v-if="dispens.data.length > 0">
                            <DispenListItem v-for="(dispen, index) in dispens.data" :key="dispen.id" :dispen=dispen
                                :index=index @edit-dispen="editDispen" @confirm-dispen-deletion="confirmDispenDeletion"
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
            <Bootstrap4Pagination :data="dispens" @pagination-change-page="search" />
        </div>
    </div>
    <div class="modal fade" id="deleteDispenModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Dispen</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this dispen ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteDispen" type="button" class="btn btn-primary">Delete Dispen</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="dispenFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Dispen</span>
                        <span v-else>Add New Dispen</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editDispenSchema : createDispenSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name User</label>
                            <Field @click="getUser" name="userId" as="select" class="form-control"
                                :class="{ 'is-invalid': errors.userId }" id="userId" aria-describedby="nameHelp"
                                placeholder="Enter full name">
                                <option value="" disabled>Select a Name</option>
                                <option v-for="user in users.data" :key="user.id" :value="user.id">{{ user.name }}</option>

                            </Field>
                            <span class="invalid-feedback">{{ errors.userId }}</span>
                        </div>

                        <div class="form-group">
                            <label for="desc">Description</label>
                            <Field name="desc" as="textarea" class="form-control " :class="{ 'is-invalid': errors.desc }"
                                id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.desc }}</span>
                        </div>

                        <div class="form-group">
                            <label>Pay At</label>
                            <Field name="pay_at" type="date" class="form-control " :class="{ 'is-invalid': errors.pay_at }"
                                id="pay_at" aria-describedby="nameHelp" placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.pay_date }}</span>
                        </div>

                        <div class="form-group">
                            <label>Periode</label>
                            <Field name="periode" type="date" class="form-control "
                                :class="{ 'is-invalid': errors.periode }" id="periode" aria-describedby="nameHelp"
                                placeholder="Enter Periode Date" />
                            <span class="invalid-feedback">{{ errors.periode }}</span>
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
