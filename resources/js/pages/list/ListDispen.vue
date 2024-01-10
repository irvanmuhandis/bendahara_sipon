<script setup>
import axios from 'axios';
import { ref, onMounted, reactive, watch } from 'vue';
import { Form, Field, useResetForm, useForm, validate } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import DispenListItem from '../dispen/DispenListItem.vue';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();

const dispens = ref({ 'data': [] });
const santris = ref([]);

const editformValues = ref({
    'id': '',
    'santri': '',
    'periode': '',
    'pay_at': '',
    'desc': '',
    'status': ''
});
const errors = ref({
    'santri': null,
    'periode': null,
    'pay_at': null,
    'desc': null,
    'status': null
})

const searchQuery = ref(null);
const selectAll = ref(false);
const selectedDispen = ref([]);
const filter = ref({
    'status': null,
    'nis': null,
    'dispen_periode': null,
    'pay_at': null,
    'updated_at': null
})

const confirmDispenDeletion = (id) => {
    $('#deleteDispenModal').modal('show');
};

// const deleteDispen = () => {
//     axios.delete(`/api/dispens/${dispenIdBeingDeleted.value}`)
//         .then(() => {
//             $('#deleteDispenModal').modal('hide');
//             toastr.success('Dispen deleted successfully!');
//             index();
//             //dispenDeleted(dispenIdBeingDeleted.value)
//         });
// };

const editDispen = (dispen) => {

    $('#dispenFormModal').modal('show');
    editformValues.value = {
        id: dispen.id,
        santri: dispen.santri,
        pay_at: dispen.pay_at,
        status : dispen.status,
        periode: dispen.dispen_periode,
        desc: dispen.dispen_desc
    };
    console.log(editformValues.value);
};

const updateDispen = () => {
    if (validation()) {
        axios.put('/api/dispens/' + editformValues.value.id, editformValues.value)
            .then((response) => {
                index();
                $('#dispenFormModal').modal('hide');
                toastr.success('Dispensasi berhasil diupdate!');
            }).catch((error) => {
                console.log(error);
            });
    }
}

const validation = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (editformValues.value.santri == null) {
        errors.value.santri = 'Pilih Santri ';
        err += 1;
    }
    if (editformValues.value.desc == '') {
        errors.value.desc = 'Isi deskripsi '
        err += 1;
    }
    if (editformValues.value.pay_at == '') {
        errors.value.pay_at = 'Pilih tanggal jatuh tempo'
        err += 1;
    }
    if (editformValues.value.periode == '') {
        errors.value.periode = 'Pilih periode'
        err += 1;
    }

    console.log(errors.value);
    console.log(editformValues.value);
    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}



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
            $('#deleteDispenModal').modal('hide');
            index();
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
    index();
    console.log(filter.value);
}

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


const index = (link = `/api/dispens`) => {
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
            dispens.value = response.data;
            selectedDispen.value = [];
            selectAll.value = false;
        })
    }

}

const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');



    } catch (error) {
        console.error(error);
    }
}

watch(searchQuery, debounce(() => {
    index();
}, 300));

onMounted(() => {
    index();
});
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"> <strong>Dispensasi</strong> </h1>
                    <p><small>List santri yang dispensasi</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item "><router-link to="/">Beranda</router-link></li>
                        <li class="breadcrumb-item active">Dispensasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <router-link to="/admin/dispens/create">
                <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i>Tambah Dispensasi</button>
            </router-link>
            <div class="row  mb-2 mt-2">
                <div class="col-md-3" v-if="selectedDispen.length > 0">
                    <button @click="confirmDispenDeletion" type="button" class="w-100 btn btn-danger">
                        <i class="fa fa-trash mr-1"></i>
                        Hapus {{ selectedDispen.length }} data
                    </button>
                </div>
                <div class="col-md">
                    <input type="text" v-model="searchQuery" class="form-control " placeholder="Search..." />
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><input type="checkbox" v-model="selectAll" @change="selectAllDispens" /></th>
                        <th>Nama
                            <span class="float-right" @click="sort('nis')">
                                <i :class="{ 'text-primary': filter.nis == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.nis == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Periode
                            <span class="float-right" @click="sort('dispen_periode')">
                                <i :class="{ 'text-primary': filter.dispen_periode == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.dispen_periode == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Jatuh Tempo
                            <span class="float-right" @click="sort('pay_at')">
                                <i :class="{ 'text-primary': filter.pay_at == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.pay_at == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Deskripsi</th>
                        <th>Status
                            <span class="float-right" @click="sort('status')">
                                <i :class="{ 'text-primary': filter.status == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.status == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Update
                            <span class="float-right" @click="sort('updated_at')">
                                <i :class="{ 'text-primary': filter.updated_at == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.updated_at == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody v-if="dispens.data.length > 0">
                    <DispenListItem v-for="(dispen, index) in dispens.data" :key="dispen.id" :dispen=dispen :index=index
                        @edit-dispen="editDispen" @confirm-dispen-deletion="confirmDispenDeletion"
                        @toggle-selection="toggleSelection" :select-all="selectAll" />
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li v-for="link in dispens.links" :key="link.label"
                        :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                        <a class="page-link" v-html="link.label" href="#" @click="index(link.url)"></a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>

    <div class="modal fade" id="deleteDispenModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Kofirmasi Penghapusan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin ingin menghapus {{ selectedDispen.length }} data ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                    <button @click.prevent="bulkDelete" type="button" class="btn btn-primary">Yakin</button>
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
                        <span>Edit Dispen</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Santri</label>
                            <VueMultiselect @click="getSantri" v-model="editformValues.santri" :option-height="9"
                                :options="santris" :class="{ 'is-invalid': errors.santri }" :multiple="false"
                                :close-on-select="true" placeholder="Pilih Satu " label="fullname" track-by="nis"
                                :show-labels="false">
                                <template v-slot:option="{ option }">
                                    <div>{{ option.fullname }} - {{ option.nis }} </div>
                                </template>
                            </VueMultiselect>
                            <span class="invalid-feedback">{{ errors.santri }}</span>
                        </div>

                        <div class="form-group">
                            <label for="desc">Deskripsi</label>
                            <input v-model="editformValues.desc" type="text" class="form-control "
                                :class="{ 'is-invalid': errors.desc }" placeholder="Enter description" />
                            <span class="invalid-feedback">{{ errors.desc }}</span>
                        </div>

                        <div class="form-group">
                            <label>Jatuh Tempo</label>
                            <input v-model="editformValues.pay_at" type="date" class="form-control "
                                :class="{ 'is-invalid': errors.pay_at }" placeholder="Enter Pay Date" />
                            <span class="invalid-feedback">{{ errors.pay_at }}</span>
                        </div>

                        <div class="form-group">
                            <label>Periode</label>
                            <input v-model="editformValues.periode" type="month" class="form-control "
                                :class="{ 'is-invalid': errors.periode }" placeholder="Enter Periode Date" />
                            <span class="invalid-feedback">{{ errors.periode }}</span>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <div :class="{ 'is-invalid': errors.status }" class="form-control">
                                <input v-model="editformValues.status" value="1" type="radio" />
                                Aktif
                                <input v-model="editformValues.status" value="0" type="radio" />
                                Tidak Aktif
                            </div>
                            <span class="invalid-feedback">{{ errors.status }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" @click="updateDispen" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
