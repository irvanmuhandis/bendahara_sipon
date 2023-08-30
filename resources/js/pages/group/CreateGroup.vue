<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm } from 'vee-validate';
import * as yup from 'yup';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();

const santris = ref([]);
const listgroups = ref([]);
const groups = ref([]);
const groupsantris = ref([]);
const selectedUser = ref([]);
const searchQuery = ref('');
const selectAll = ref(false);
const selectedRelation = ref([]);
const santri_id = ref([]);
const load = ref(false);

const form = ref({
    group: null,
    santri: [],
});

const errors = ref({
    group: null,
    santri: null,
});

const createGroupSchema = yup.object({
    name: yup.string().required(),
    desc: yup.string().required(),
});


const search = (page = 1) => {
    axios.get(`/api/group/santri/search?page=${page}`, {
        params: {
            query: searchQuery.value
        }
    })
        .then(response => {
            groups.value = response.data;
            console.log(groups);
        })
        .catch(error => {
            console.log(error);
        })
};

const getUser = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');

    } catch (error) {
        console.error(error);
    }
}

const count = () => {
    countUser.value = selectedUser.length;
}


const linkGroup = (event) => {
    event.preventDefault();

    if (validateBill()) {
        form.value.santri = form.value.santri.map(santri => {
            return santri.nis;
        });
        form.value.group = form.value.group.id;
        axios.put('/api/group/link', form.value)
            .then((response) => {
                clearform();
                toastr.success('Penyambungan berhasil !');
                getData();
                groupsantris.value = [];
            })
            .catch((error) => {
                console.log(error);
            })
    }
};

const createGroup = (values, { resetForm }) => {

    axios.post('/api/group', values)
        .then((response) => {
            resetForm();
            toastr.success('Pay created successfully!');
            getData();
            getGroup();
            groupsantris.value = [];
        })
        .catch((error) => {
            console.log(error);
        })

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
    if (form.value.santri.length == 0) {
        errors.value.santri = 'Pilih User ';
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


const santrichange = () => {
    load.value = true;
    // groupsantris.value = [];
    santri_id.value = form.value.santri.map(santri => {
        return santri.nis;
    });

    console.log(form.value);
    axios.get('/api/group/santri/form', {
        params: {
            'santri': JSON.stringify(santri_id.value)
        }
    })
        .then((response) => {
            load.value = false;
            groupsantris.value = response.data;
        });
}

const getGroup = () => {
    axios.get('/api/group/list')
        .then((response) => {
            listgroups.value = response.data;
            console.log(listgroups.value);
        })
}


const getData = () => {
    axios.get('/api/group/santri')
        .then((response) => {
            groups.value = response.data;
        })
}


const selectAllGroup = () => {
    if (selectAll.value) {
        selectedRelation.value = groups.value.data.map(grup => grup.santri.map(santri => santri.id))
            .flat(1)
            .filter(id => id !== null);
    } else {
        selectedRelation.value = [];
    }
    console.log(selectedRelation.value);
}
const toggleSelection = (data) => {
    const index = selectedRelation.value.indexOf(data.id);
    if (index === -1) {
        selectedRelation.value.push(data.id);
    } else {
        selectedRelation.value.splice(index, 1);
    }
    console.log(selectedRelation.value);
};

const confirmRelationDeletion = (id) => {
    selectedUser.value = id;
    // $('#deleteDispenModal').modal('show');
    deleteRelation();
};

const deleteRelation = () => {
    axios.delete(`/api/group/santri/${selectedUser.value}`)
        .then(() => {
            $('#deleteDispenModal').modal('hide');
            toastr.success('Dispen deleted successfully!');
            getData();
        });
};

const bulkDelete = () => {
    console.log(selectedRelation.value);
    axios.delete('/api/group/santri', {
        data: {
            ids: selectedRelation.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            getData();
        });
};

watch(searchQuery, debounce(() => {
    search();
}, 300));

onMounted(() => {
    getUser();
    getData();
    getGroup();
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
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/master/group">Grup</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Tambah Grup</li>
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
                        <li class="nav-item"><a class="nav-link" href="#create" data-toggle="tab">Tambah Group</a>
                        </li>
                        <li class="nav-item"><a class="nav-link active" href="#link" data-toggle="tab">Menyambungkan Santri
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="create">
                            <Form @submit="createGroup" :validation-schema="createGroupSchema" v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Grup</label>
                                            <Field name="name" type="text" class="form-control "
                                                :class="{ 'is-invalid': errors.name }" aria-describedby="nameHelp"
                                                placeholder="Masukkan Nama Grup" />
                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Deskripsi</label>
                                            <Field name="desc" as="textarea" class="form-control "
                                                :class="{ 'is-invalid': errors.desc }" aria-describedby="nameHelp"
                                                placeholder="Masukkan Deskripsi" />
                                            <span class="invalid-feedback">{{ errors.desc }}</span>
                                        </div>

                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </Form>
                        </div>

                        <div class="tab-pane active" id="link">
                            <form @submit="linkGroup">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Santri</label>
                                            <VueMultiselect v-model="form.santri" @select="santrichange"
                                                @remove="santrichange" @input="santrichange" :option-height="9"
                                                :options="santris" :class="{ 'is-invalid': errors.santri }" :multiple="true"
                                                :close-on-select="true" placeholder="Pilih Satu / Lebih" label="fullname"
                                                track-by="nis" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.fullname }} - {{ option.nis }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.santri }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label>Grup</label>
                                            <VueMultiselect v-model="form.group" :option-height="9" :options="listgroups"
                                                :class="{ 'is-invalid': errors.group }" :multiple="false"
                                                :close-on-select="true" placeholder="Pilih Satu" label="group_name"
                                                track-by="id" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.group_name }} - {{ option.id }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.group }}</span>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label> Afiliasi Santri
                                            </label>
                                            <table class="table table-sm text-center table-bordered">
                                                <thead>
                                                    <tr>

                                                        <th>NIS</th>
                                                        <th>Name</th>
                                                        <th>Grup</th>

                                                    </tr>
                                                </thead>
                                                <tr v-if="groupsantris.length == 0">
                                                    <td colspan="3">Kosong </td>
                                                </tr>
                                                <tr v-if="load">
                                                    <td colspan="3">
                                                        <div  class="spinner-border row text-primary mx-auto" role="status"></div>
                                                    </td>
                                                </tr>
                                                <tr v-else v-for="santri in groupsantris">
                                                    <td>{{ santri.nis }} </td>
                                                    <td>{{ santri.fullname }}</td>
                                                    <td v-if="santri.group != null">{{ santri.group.group_name }}</td>
                                                    <td v-else>-</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors }}
                            </form>

                        </div>

                    </div>

                </div>
            </div>

            <div class="m-2">
                <h4 class="mx text-center">List Grup</h4>
                <div class="d-flex justify-content-between mb-3">
                    <div class="d-flex">
                        <div v-if="selectedRelation.length > 0">
                            <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                                <i class="fa fa-trash mr-1"></i>
                                Hapus Relasi Dipilih
                            </button>
                            <span class="ml-2">Dipilih {{ selectedRelation.length }} relasi</span>
                        </div>
                    </div>
                    <div>
                        <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Grup</th>
                            <th scope="col">Santri</th>
                            <th scope="col">Dibuat</th>
                            <th scope="col">Diperbarui</th>
                            <th scope="col"><input type="checkbox" v-model="selectAll" @change="selectAllGroup" />
                            </th>
                        </tr>

                    </thead>
                    <tbody>
                        <tr v-for="grup in groups.data" :key="grup.id">
                            <td class="text-center"> {{ grup.group_name }}</td>
                            <td class="p-0 text-start">
                                <div v-if="grup.santri.length > 0" class="m-2" v-for="santri in grup.santri"
                                    :key="santri.id">
                                    {{ santri.name }}
                                </div>
                                <div v-else class="text-center">-</div>
                            </td>

                            <td class="p-0 text-center">
                                <div v-if="grup.santri.length > 0" class="m-2" v-for="santri in grup.santri"
                                    :key="santri.id">

                                    {{ formatDate(santri.created_at) }}


                                </div>
                                <div v-else>-</div>
                            </td>


                            <td class="p-0 text-center">
                                <div v-if="grup.santri.length > 0" class="m-2" v-for="santri in grup.santri"
                                    :key="santri.id">
                                    {{ formatDate(santri.created_at) }}
                                </div>
                                <div v-else>-</div>
                            </td>

                            <td class="p-0 text-center">
                                <div v-if="grup.santri.length > 0" class="m-2" v-for="santri in grup.santri"
                                    :key="santri.id">

                                    <input type="checkbox" :checked="selectAll" @change="toggleSelection(santri)" />
                                </div>
                                <div v-else>-</div>
                            </td>

                        </tr>





                    </tbody>
                </table>
                <Bootstrap4Pagination :data="groups" @pagination-change-page="search" />
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
