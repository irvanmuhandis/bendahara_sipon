<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm } from 'vee-validate';
import * as yup from 'yup';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();

const users = ref([]);
const listgroups = ref([]);
const groups = ref([]);
const accounts = ref([]);
const groupusers = ref([]);
const selectedUser = ref([]);

const formatted = ref();
const form = ref({
    group: null,
    user: [],
});

const errors = ref({
    group: null,
    user: null,
});

const search = (page = 1) => {
    axios.get(`/api/group/user/search?page=${page}`, {
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

const getUser = async () => {

    try {
        const response = await axios.get(`/api/userlist`)
        users.value = response.data;
        console.log('user added');

    } catch (error) {
        console.error(error);
    }
}

const count = () => {
    countUser.value = selectedUser.length;
}


const createGroup = (event) => {
    event.preventDefault();
    form.value.user = form.value.user.map(user => {
        return user.id;
    });
    form.value.group = form.value.group.id;
    if (validateBill()) {
        axios.put('/api/group/link', form.value)
            .then((response) => {
                clearform();
                toastr.success('Pay created successfully!');
                getData();
            })
            .catch((error) => {
                console.log(error);
            })
    }

};

const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {
        form.value[key] = null;
    }
}

const validateBill = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.user.length == 0) {
        errors.value.user = 'Pilih User ';
        err += 1;
    }
    if (form.value.group == null) {
        errors.value.group = 'Pilih Grup '
        err += 1;
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}


const groupchange = () => {
    groupusers.value = [];
    console.log(form.value);
    axios.get('/api/user/group', {
        params: {
            group_id: form.value.group.id
        }
    })
        .then((response) => {
            groupusers.value = response.data;
        });
}

const getAccount = () => {
    axios.get('/api/account')
        .then((response) => {
            accounts.value = response.data;
        })
}

const getGroup = () => {
    axios.get('/api/group/list')
        .then((response) => {
            listgroups.value = response.data;
        })
}

const handleChange = (event) => {
    formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}

const getData = () => {
    axios.get('/api/group/user')
        .then((response) => {
            groups.value = response.data;
        })
}

onMounted(() => {
    getUser();
    getData();
    getGroup();
    getAccount();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/dashboard">Home</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/group">Group</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link" href="#create" data-toggle="tab">Create Group</a>
                        </li>
                        <li class="nav-item"><a class="nav-link active" href="#link" data-toggle="tab">Linking User To Group
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="create">
                            <Form @submit="createGroup_s" :validation-schema="createGroupSchema_s"
                                v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>User</label>
                                            <Field as="select" multiple @change="count" class="h-100 form-control"
                                                :class="{ 'is-invalid': errors.user_id }" v-model="selectedUser"
                                                name="user">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="user in users" :value="user.id">{{ user.id + "|" + user.name
                                                }}
                                                </option>

                                            </Field>
                                            <p>{{ countUser }} user selected</p>
                                            <span class="invalid-feedback">{{ errors.user }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Group</label><br>
                                            <Field as="select" class="form-control" multiple
                                                :class="{ 'is-invalid': errors.account }" name="account">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="group in listgroups" :value="group.id">
                                                    {{ group.id + ` | ` +
                                                        group.account_name }}
                                                </option>

                                            </Field>
                                            <span class="invalid-feedback">{{ errors.account }}</span>
                                        </div>

                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </Form>
                        </div>

                        <div class="tab-pane active" id="link">
                            <form @submit="createGroup">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Group</label>
                                            <VueMultiselect v-model="form.group" :option-height="9" @select="groupchange"
                                                :options="listgroups" :class="{ 'is-invalid': errors.group }"
                                                :multiple="false" :close-on-select="true" placeholder="Pilih Satu"
                                                label="group_name" track-by="id" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.group_name }} - {{ option.id }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.group }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label>User</label>
                                            <VueMultiselect v-model="form.user" :option-height="9" :options="users"
                                                :class="{ 'is-invalid': errors.user }" :multiple="true"
                                                :close-on-select="true" placeholder="Pilih Satu / Lebih" label="name"
                                                track-by="id" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.name }} - {{ option.id }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.user }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label> User Group
                                            </label>
                                            <table class="table table-sm text-center table-bordered">
                                                <thead>
                                                    <tr>

                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Join At</th>

                                                    </tr>
                                                </thead>
                                                <tr v-if="groupusers.length === 0">
                                                    <td colspan="3">No Record</td>
                                                </tr>
                                                <tr v-for="user in groupusers">
                                                    <td>{{ user.id }} </td>
                                                    <td>{{ user.name }}</td>
                                                    <td>{{ user.join_at }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </form>

                        </div>

                        <div class="tab-pane" id="range">
                            Coming Soon
                        </div>

                    </div>

                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="mx text-center">List Grup</h4>
                </div>
                <div class="card-body">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Grup</th>
                                    <th scope="col">Santri</th>
                                    <th scope="col">Dibuat</th>
                                    <th scope="col">Diperbarui</th>
                                    <th scope="col">Action</th>
                                </tr>

                            </thead>
                            <tbody>
                                <tr v-for="grup in groups.data" :key="grup.id">
                                    <td class="text-center"> {{ grup.group_name }}</td>
                                    <td class="p-0 text-start">
                                        <div v-if="grup.user.length > 0" class="m-2" v-for="user in grup.user"
                                            :key="user.id">
                                            {{ user.name }}
                                        </div>
                                        <div v-else class="text-center">-</div>
                                    </td>

                                    <td class="p-0 text-center">
                                        <div v-if="grup.user.length > 0" class="m-2" v-for="user in grup.user"
                                            :key="user.id">

                                            {{ formatDate(user.created_at) }}


                                        </div>
                                        <div v-else>-</div>
                                    </td>


                                    <td class="p-0 text-center">
                                        <div v-if="grup.user.length > 0" class="m-2" v-for="user in grup.user"
                                            :key="user.id">
                                            {{ formatDate(user.created_at) }}
                                        </div>
                                        <div v-else>-</div>
                                    </td>

                                    <td class="p-0 text-center">
                                        <div v-if="grup.user.length > 0" class="m-2" v-for="user in grup.user"
                                            :key="user.id">


                                            <a href="#" @click="editRelation(user)">
                                                <i class="fa fa-edit mr-2"></i>
                                            </a>

                                            <a href="#" @click="confirmRelationDeletion(user.id)">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>


                                        </div>
                                        <div v-else>-</div>
                                    </td>

                                </tr>





                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer">
                        <Bootstrap4Pagination :data="groups" @pagination-change-page="search" />
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script>



import accounting from 'accounting';
import { formatDate } from '../../helper';
export default {
    // computed property to retrieve current page number
    computed: {
        currentPage() {
            return this.listpays.current_page;
        }
    },

    // methods to handle pagination events
    methods: {
        changePage(page) {
            this.search(page);
        },


    },

}



</script>
