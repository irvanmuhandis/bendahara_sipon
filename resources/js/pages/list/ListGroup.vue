<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate } from "../../helper";


const toastr = useToastr();
const listgroups = ref({ 'data': [] });
const formValues = ref(null);
const groupIdBeingDeleted = ref(null);
const searchQuery = ref(null);
const selectAll = ref(false);
const selectedGroup = ref([]);

function getCookieValue(cookieName) {
  const name = cookieName + '=';
  const decodedCookie = decodeURIComponent(document.cookie);
  const cookieArray = decodedCookie.split(';');

  for (let i = 0; i < cookieArray.length; i++) {
    let cookie = cookieArray[i];
    while (cookie.charAt(0) === ' ') {
      cookie = cookie.substring(1);
    }
    if (cookie.indexOf(name) === 0) {
      return cookie.substring(name.length, cookie.length);
    }
  }

  return 'tt';
}


const confirmGroupDeletion = (id) => {
    groupIdBeingDeleted.value = id;
    $('#deleteGroupModal').modal('show');
};

const deleteGroup = () => {
    console.log(groupIdBeingDeleted.value);
    axios.delete(`/api/group/${groupIdBeingDeleted.value}`, {
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

const editGroupSchema = yup.object({
    name: yup.string().required(),
    desc: yup.string().required()
});

const editGroup = (group) => {
    console.log(group);
    $('#groupFormModal').modal('show');
    formValues.value = {
        id: group.id,
        name: group.group_name,
        desc: group.desc
    };
};

const updateGroup = (values, { setErrors }) => {
    values.id = formValues.value.id;
    axios.put('/api/group/' + formValues.value.id, values)
        .then((response) => {
            getGroup();
            $('#groupFormModal').modal('hide');
            toastr.success('Group updated successfully!');
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        });
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

const getCookie = () => {
    console.log(document.cookie);
}

onMounted(() => {
    getGroup();
    getCookie();
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
                        <li class="breadcrumb-item ">  <RouterLink to="/">Beranda</RouterLink></li>
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

                    <router-link to="/admin/master/group/create" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Tambah Grup / Sambungkan Santri
                    </router-link>

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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll" @change="selectAllGroups" /></th>
                                <th scope="col">Nama Grup</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(group) in listgroups.data" :key="group.id">
                                <td><input type="checkbox" :checked="selectAll" @change="toggleSelection(group)" />
                                </td>

                                <td>{{ group.group_name }} </td>
                                <td>{{ group.desc }}</td>
                                <td>{{ formatDate( group.created_at) }}</td>

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
                        <span>Edit Group</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <Form @submit="updateGroup" :validation-schema="editGroupSchema" v-slot="{ errors }"
                    :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="desc">Nama grup</label>
                            <Field name="name" type="text" class="form-control " :class="{ 'is-invalid': errors.name }"
                                aria-describedby="nameHelp" placeholder="Enter name" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group">
                            <label for="desc">Deskripsi</label>
                            <Field name="desc" type="text" class="form-control " :class="{ 'is-invalid': errors.desc }"
                                aria-describedby="nameHelp" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.desc }}</span>
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
