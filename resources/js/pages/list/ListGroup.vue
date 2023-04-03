<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';


const toastr = useToastr();
const listgroups = ref({ 'data': [] });
const users = ref([]);
const editing = ref(false);
const formValues = ref(null);
const form = ref(null);
const groupIdBeingDeleted = ref(null);
const searchQuery = ref(null);
const selectAll = ref(false);
const selectedGroup = ref([]);

const confirmGroupDeletion = (id) => {
    groupIdBeingDeleted.value = id;
    $('#deleteGroupModal').modal('show');
};

const deleteGroup = () => {
    console.log(groupIdBeingDeleted.value);
    axios.delete(`/api/group/${groupIdBeingDeleted.value}`,{
        data: {
            id: groupIdBeingDeleted.value
        }
    })
        .then((response) => {
            $('#deleteGroupModal').modal('hide');
            toastr.success('Group deleted successfully!');
            getGroup();
            //groupDeleted(groupIdBeingDeleted.value)
        });
};

const bulkDelete = () => {
    console.log(selectedGroup.value);
    axios.delete('/api/group', {
        data: {
            ids: selectedGroup.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            getGroup();
        });
};


const createGroupSchema = yup.object({
    user_id: yup.string().required(),
    status: yup.number().required(),
    group: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required()
});

const editGroupSchema = yup.object({
    user_id: yup.string().required(),
    group: yup.number().required(),
    remainder: yup.number().required(),
    title: yup.string().required(),
    status: yup.number().when((status, schema) => {
        return status ? schema.required() : schema;
    }),
});

const createGroup = (values, { resetForm, setErrors }) => {
    axios.post('/api/group', values)
        .then((response) => {
            listgroups.value.data.unshift(response.data);
            $('#groupFormModal').modal('hide');
            resetForm();
            toastr.success('Group created successfully!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
};

const AddGroup = () => {
    editing.value = false;
    $('#groupFormModal').modal('show');
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


const editGroup = (group) => {
    editing.value = true;
    form.value.resetForm();
    getUser();
    $('#groupFormModal').modal('show');
    formValues.value = {
        id: group.id,
        remainder: group.remainder,
        user_id: group.user_id,
        status: group.status,
        title: group.title,
        group: group.group
    };
};

const updateGroup = (values, { setErrors }) => {
    axios.put('/api/group/' + formValues.value.id, values)
        .then((response) => {
            // const index = groups.value.data.findIndex(group => group.id === response.id);
            // listgroups.value.data[index] = response.data;
            getGroup();
            $('#groupFormModal').modal('hide');
            toastr.success('Group updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
}

const handleSubmit = (values, actions) => {
    // console.log(actions);
    if (editing.value) {
        updateGroup(values, actions);
    } else {
        createGroup(values, actions);
    }
}

const groupDeleted = (groupId) => {
    listgroups.value.data = listgroups.value.data.filter(group => group.id !== groupId);
};


const search = (page = 1) => {
    const param = {};
    param.query = searchQuery.value

    axios.get(`/api/group/search?page=${page}`, {
        params: param
    })
        .then(response => {
            listgroups.value = response.data;
            selectedGroup.value = [];
            selectAll.value = false;
        })
        .catch(error => {
            console.log(error);
        })
};

const toggleSelection = (group) => {
    const index = selectedGroup.value.indexOf(group.id);
    if (index === -1) {
        selectedGroup.value.push(group.id);
    } else {
        selectedGroup.value.splice(index, 1);
    }
    console.log(selectedGroup.value);
};




const selectAllGroups = () => {
    if (selectAll.value) {
        selectedGroup.value = listgroups.value.data.map(group => group.id);
    } else {
        selectedGroup.value = [];
    }
    console.log(selectedGroup.value);
}

watch(searchQuery, debounce(() => {
    search();
}, 300));


const group_count = computed(() => {
    return group_status.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
})


const getGroup = (page = 1) => {

    axios.get(`/api/group?page=${page}`).then((response) => {
        listgroups.value = response.data;
        selectedGroup.value = [];
        selectAll.value = false;
    })

}

onMounted(() => {
    getGroup();
})


</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong> Group | Kelompok</strong></h1>
                    <p><small>List kelompok santri</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                        <li class="breadcrumb-item active">Groups</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-3">
                <div class="d-flex">

                    <button @click="AddGroup" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New Group
                    </button>

                    <div v-if="selectedGroup.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                            <i class="fa fa-trash mr-1"></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedGroup.length }} groups</span>
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
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" v-model="selectAll" @change="selectAllGroups" /></th>
                                        <th scope="col" style="width: 10px">#</th>
                                        <th scope="col">Group Name</th>
                                        <th scope="col">Desc</th>
                                        <th scope="col">Created At</th>
                                        <th scope="col">Updated At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(group, index) in listgroups.data" :key="group.id">
                                        <td><input type="checkbox" :checked="selectAll" @change="toggleSelection(group)" /></td>
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ group.name }} </td>
                                        <td>{{ group.desc }}</td>
                                        <td>{{ group.created_at }}</td>
                                        <td>{{ group.updated_at }}</td>

                                        <td>
                                            <a href="#" @click="editGroup(group)">
                                                <i class="fa fa-edit mr-2"></i>
                                            </a>

                                            <a href="#" @click="confirmGroupDeletion(group.id)">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                    <Bootstrap4Pagination :data="listgroups" @pagination-change-page="search" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteGroupModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete Group</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure you want to delete this group ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteGroup" type="button" class="btn btn-primary">Delete Group</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="groupFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit Group</span>
                        <span v-else>Add New Group</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editGroupSchema : createGroupSchema"
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
                            <Field name="group" type="number" class="form-control " :class="{ 'is-invalid': errors.group }"
                                id="pay_at" aria-describedby="nameHelp" placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.group }}</span>
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
            return this.listgroups.current_page;
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
