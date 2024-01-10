<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { formatStatus, formatMoney, formatDate } from "../../helper";
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const santris = ref([]);
const accounts = ref([]);

const toastr = useToastr();
const listdebts = ref({ 'data': [] });

const searchQuery = ref(null);
const selectAll = ref(false);
const selected = ref([]);
const selectedWall = ref([]);

const types = [{
    id: 1,
    name: 'Satu Jam Terakhir',
},
{
    id: 2,
    name: '12 Jam Terakhir'
},
{
    id: 3,
    name: 'Satu Hari Terakhir'
},
{
    id: 4,
    name: '30 Hari Terakhir'
},
{
    id: 5,
    name: '10 Data Terakhir'
},
{
    id: 6,
    name: '100 Data Terakhir'
}
];
const filter = ref({
    'account_id': null,
    'nis': null,
    'titlr': null,
    'amount': null,
    'remainder': null,
    'created_at': null
})
const errors = ref({
    'santri': null,
    'title': null,
    'price': null,
    'remain': null,
    'account': null
});

const form = ref({
    'id': null,
    'wallet_id': null,
    'santri': null,
    'title': '',
    'price': '',
    'remain': '',
    'account': null
});

const selectedStatus = ref();
const debt_status = ref([]);
const geteDebtStatus = () => {
    axios.get('/api/pay/status')
        .then((response) => {
            debt_status.value = response.data;
        })
};
const destroyType = ref(null);

const changeStatus = (val) => {
    selectedStatus.value = val;
    search();
}

const delPrompt = (values, action) => {
    const modal = document.getElementById("myModal");

    // Show the modal
    $(modal).modal('show');
    destroyType.value = values.delType;
    console.log(destroyType);
}

const delMass = () => {
    switch (destroyType.value) {
        case 1:
            axios.delete('/api/debt/delHour?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 2:
            axios.delete('/api/debt/delHour?type=' + 12)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 3:
            axios.delete('/api/debt/delDay?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 4:
            axios.delete('/api/debt/delDay?type=' + 30)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 5:
            axios.delete('/api/debt/del?type=' + 10)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 6:
            axios.delete('/api/debt/del?type=' + 100)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    getDebt();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        default:
            console.log('errror');
            break;
    }
    const modal = document.getElementById("myModal");

    // Show the modal
    $(modal).modal('hide');


};

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
    search();
    console.log(filter.value);
}



const search = (link = '/api/debt/search') => {
    const param = {};
    if (selectedStatus.value != null) {
        param.status = selectedStatus.value;
    }
    param.query = searchQuery.value
    console.log(param);

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
    param.filter = fil.key;
    param.value = fil.value;
    axios.get(link, {
        params: param
    })
        .then(response => {
            listdebts.value = response.data;
            selected.value = [];
            selectedWall.value = [];
            selectAll.value = false;
            links.value = response.data.links;
        })
        .catch(error => {

        })
};

// const deleteDebt = () => {
//     console.log(debtIdBeingDeleted.value);
//     axios.delete(`/api/debt/${debtIdBeingDeleted.value}`, {
//         data: {
//             id: debtIdBeingDeleted.value
//         }
//     })
//         .then((response) => {
//             $('#deleteDataModal').modal('hide');
//             toastr.success('Debt deleted successfully!');
//             getDebt();
//             //debtDeleted(debtIdBeingDeleted.value)
//         });
// };

const bulkDelete = () => {
    console.log(selected.value);

    console.log(selectedWall.value);
    axios.delete('/api/debt', {
        data: {
            ids: selected.value,
            wall_ids: selectedWall.value,
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            search();
            $('#deleteDataModal').modal('hide');
        });
};

const confirmDataDeletion = (id) => {
    $('#deleteDataModal').modal('show');
};


const toggleSelection = (debt) => {
    const index = selected.value.indexOf(debt.id);
    if (index === -1) {
        selected.value.push(debt.id);
        selectedWall.value.push(debt.wallet_id);
    } else {
        selected.value.splice(index, 1);
        selectedWall.value.splice(index, 1);
    }
    console.log(selected.value);
};




const selectAllDebts = () => {
    if (selectAll.value) {
        selected.value = listdebts.value.data.map(debt => debt.id);
        selectedWall.value = listdebts.value.data.map(debt => debt.wallet_idid);
    } else {
        selected.value = []; selectedWall.value = [];
    }
    console.log(selected.value);
}

const editData = (debt) => {

    console.log(debt);
    form.value.id = debt.id;
    form.value.wallet_id = debt.wallet_id;
    form.value.price = debt.amount;
    form.value.santri = debt.santri;
    form.value.account = debt.account;
    form.value.title = debt.title;
    form.value.remain = debt.remainder;

    $('#editDataModal').modal('show');
}
const confirmUpdate = () => {


    if (validate()) {
        $('#editDataModal').modal('hide');
        $('#conEditDataModal').modal('show');
    }
}
const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {
        form.value[key] = null;
    }
}

const validate = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.santri == null) {
        errors.value.santri = 'Pilih Santri ';
        err += 1;
    }
    if (form.value.account == null) {
        errors.value.account = 'Pilih Akun '
        err += 1;
    }
    if (form.value.price == '') {
        errors.value.price = 'Isi Jumlah Piutang '
        err += 1;
    }
    if (form.value.remain == '') {
        errors.value.remain = 'Isi Sisa Piutang '
        err += 1;
    }
    if (form.value.title == '') {
        errors.value.title = 'Isi Judul Piutang '
        err += 1;
    }
    if (form.value.remain > form.value.price) {
        errors.value.remain = 'Sisa Piutang tidak boleh lebih dari total Piutang'
        err += 1;
    }
    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}

const update = () => {

    console.log(form.value);
    console.log(errors.value);

    axios.put(`/api/debt/${form.value.id}`, form.value)
        .then((response) => {
            toastr.success('Berhasil merubah data!');
            $('#conEditDataModal').modal('hide');
            search();
        })
        .catch((error) => {
            console.log(error);
        })

};
const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');



    } catch (error) {
        console.error(error);
    }
}
const getAccount = () => {
    axios.get('/api/account/only', {
        params: {
            type: 1
        }
    }
    )
        .then((response) => {
            accounts.value = response.data;
        })
}

watch(searchQuery, debounce(() => {
    search();
}, 300));

onMounted(() => {
    search();
    geteDebtStatus();
    getSantri();
    getAccount();
})


</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Piutang</strong></h1>
                    <p><small>List Piutang di pondok pesantren</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item ">
                        <RouterLink to="/">Beranda</RouterLink></li>
                        <li class="breadcrumb-item active">Piutang</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="mb-2">

                <router-link to="/admin/billing/debt/create" class="w-100 ">
                    <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i> Tambah Piutang
                    </button>
                </router-link>

            </div>
            <div class="btn-group w-100 mb-2">
                <button @click="changeStatus(null)" type="button" class="btn btn-dark">
                    <span class="mr-1">All</span>
                </button>

                <button v-for="status in debt_status" @click="changeStatus(status.value)" type="button" class="btn"
                    :class="'btn-' + status.color">
                    <span class="mr-1">{{ status.name }}</span>
                </button>
            </div>
            <div class="row pb-3">
                <!--
                    tutup sementara

                    <div class="col-md-3">
                    <button class="w-100 btn btn-primary" type="button" data-toggle="collapse"
                        data-target="#collapseWidthExample" aria-expanded="false" aria-controls="collapseWidthExample">
                        Hapus Berderet <i class="ml-1 right fas fa-trash"></i>
                    </button>

                    <div class="collapse m-0 pt-2" id="collapseWidthExample">

                        <Form @submit="delPrompt" class="row">
                            <div class="col-md-10">

                                <Field as="select" class="form-control" name="delType">
                                    <option disabled>Pilih Salah Satu</option>
                                    <option v-for="t in types" :value="t.id">
                                        {{ t.name }}
                                    </option>
                                </Field>


                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>

                        </Form>
                    </div>
                    <div class="modal" tabindex="-1" role="dialog" id="myModal">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">WARNING</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Kamu Yakin Ingin Menghapus Data {{
                                        destroyType == null ? "---" : types.filter(item => item.id ==
                                            destroyType).map(item => item.name)[0] }} ?</p>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                                    <button type="button" class="btn btn-primary" @click="delMass">Ya, saya yakin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="col-md-3" v-if="selected.length > 0">
                    <button @click="confirmDataDeletion" type="button" class=" w-100 btn btn-danger">
                        <i class="fa fa-trash mr-1"></i>
                        Hapus {{ selected.length }} Data
                    </button>

                </div>

                <div class="col-md">
                    <div class="input-group w-100 ">
                        <input type="text" v-model="searchQuery" class=" form-control" placeholder="Search..." />
                    </div>
                </div>


            </div>


            <table class="table table-bordered hover ">
                <thead>
                    <tr>
                        <th class="text-center"><input type="checkbox" v-model="selectAll" @change="selectAllDebts" /></th>
                        <th>Dibuat
                            <span class="float-right" @click="sort('created_at')">
                                <i :class="{ 'text-primary': filter.created_at == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.created_at == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Akun
                            <span class="float-right" @click="sort('account_id')">
                                <i :class="{ 'text-primary': filter.account_id == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.account_id == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Judul
                            <span class="float-right" @click="sort('title')">
                                <i :class="{ 'text-primary': filter.title == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.title == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Santri
                            <span class="float-right" @click="sort('nis')">
                                <i :class="{ 'text-primary': filter.nis == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.nis == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Piutang
                            <span class="float-right" @click="sort('amount')">
                                <i :class="{ 'text-primary': filter.amount == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.amount == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Sisa
                            <span class="float-right" @click="sort('remainder')">
                                <i :class="{ 'text-primary': filter.remainder == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.remainder == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>


                        <th class="text-center">Operator</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(debt) in listdebts.data" :key="debt.id">
                        <td class="text-center"><input type="checkbox" :checked="selectAll"
                                @change="toggleSelection(debt)" />
                        </td>

                        <td>{{ formatDate(debt.created_at) }}</td>
                        <td>{{ debt.account.account_name }}</td>
                        <td>{{ debt.title }} </td>
                        <td>{{ debt.nis }} - {{ debt.santri.fullname }} </td>
                        <td v-html="formatMoney(debt.amount)"></td>
                        <td>{{ formatMoney(debt.remainder) }}</td>

                        <td>{{ debt.operator.fullname }}</td>
                        <td class="text-center">
                            <a href="#" @click="editData(debt)">
                                <i class="mx-auto fa fa-edit mr-2"></i>
                            </a>
                        </td>
                    </tr>
                    <tr v-if="listdebts.data.length == 0">
                        <td colspan="9" class="text-center">Tidak Ada Data</td>
                    </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li v-for="link in listdebts.links" :key="link.label"
                        :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                        <a class="page-link" v-html="link.label" href="#" @click="search(link.url)"></a>
                    </li>
                </ul>
            </nav>


        </div>
    </div>


    <div class="modal fade" id="deleteDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Konfirmasi Penghapusan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin ingin menghapus {{ selected.length }} data ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button @click.prevent="bulkDelete" type="button" class="btn btn-primary">Ya, saya yakin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="conEditDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Konfirmasi Perubahan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin ingin merubah data menjadi : </h5>
                    <div class="row">
                        <div class="col-4">Santri </div>
                        <div v-if="form.santri != null" class="col-8"> : {{ form.santri.fullname }} - {{ form.santri.nis }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">Akun </div>
                        <div v-if="form.account != null" class="col-8"> : {{ form.account.account_name }}</div>
                    </div>
                    <div class="row">
                        <div class="col-4">Jumlah Tagihan </div>
                        <div class="col-8"> : {{ formatMoney(form.price) }}</div>
                    </div>
                    <div class="row">
                        <div class="col-4">Judul Piutang </div>
                        <div class="col-8"> : {{ form.title }}</div>
                    </div>
                    <div class="row">
                        <div class="col-4">Sisa Tagihan </div>
                        <div class="col-8"> : {{ formatMoney(form.remain) }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button @click.prevent="update" type="button" class="btn btn-primary">Ya, saya yakin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDataModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Ubah Data Tagihan</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Santri</label>
                                    <VueMultiselect v-model="form.santri" :option-height="9" :options="santris"
                                        :class="{ 'is-invalid': errors.santri }" :multiple="false" :close-on-select="true"
                                        placeholder="Pilih Satu ..." label="fullname" track-by="nis" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.fullname }} - {{ option.nis }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.santri }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errors.title }" v-model="form.title"
                                        type="text" />
                                    <span class="invalid-feedback">{{ errors.title }}</span>



                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Akun</label><br>
                                    <VueMultiselect v-model="form.account" :option-height="9" :options="accounts"
                                        :class="{ 'is-invalid': errors.account }" :multiple="false" :close-on-select="true"
                                        placeholder="Pilih Satu..." label="account_name" track-by="id" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.account_name }} - {{ option.id }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.account }}</span>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Piutang</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errors.price }" v-model="form.price"
                                        type="number" />
                                    <span class="invalid-feedback">{{ errors.price }}</span>
                                    <span>{{ formatMoney(form.price) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Sisa Tagihan</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errors.remain }"
                                        v-model="form.remain" type="number" />
                                    <span class="invalid-feedback">{{ errors.remain }}</span>
                                    <span>{{ formatMoney(form.remain) }}</span>
                                </div>
                            </div>
                        </div>



                    </div>
                    <div class="modal-footer ">
                        <!-- <button type="button" class="col-md-3 btn btn-secondary" data-dismiss="modal">Tutup</button> -->
                        <button type="button" @click="confirmUpdate" class=" w-100 btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
