<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate, formatMoney, formatStatus } from '../../helper.js';

const toastr = useToastr();
const listBill = ref({
    'data': []
});
const accounts = ref([]);
const santris = ref([]);
const switchMode = ref(false);
const isLoading = ref(false);


const errors = ref({
    'santri': null,
    'period': null,
    'price': null,
    'remain': null,
    'account': null
});

const form = ref({
    'id': null,
    'santri': null,
    'period': '',
    'price': '',
    'remain': '',
    'account': null
});

const searchQuery = ref(null);
const selectAll = ref();
const selected = ref([]);
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
const destroyType = ref(null);
const filter = ref({
    'account_id': null,
    'amount': null,
    'nis': null,
    'remainder': null,
    'month': null,
    'created_at': null
})

const delPrompt = (values, action) => {
    const modal = document.getElementById("myModal");

    // Show the modal
    $(modal).modal('show');
    destroyType.value = values.delType;
    console.log(destroyType);
}
const toggleSelection = (bill) => {


    const index = selected.value.indexOf(bill.id);
    if (index === -1) {
        selected.value.push(bill.id);
    } else {
        selected.value.splice(index, 1);
    }
    console.log(selected.value);
};

const selectAllGroups = () => {
    if (selectAll.value) {
        selected.value = listBill.value.data.map(bill => bill.id);
    } else {
        selected.value = [];
    }
    console.log(selected.value);
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
    fetchData();
    console.log(filter.value);
}

const delMass = () => {
    switch (destroyType.value) {
        case 1:
            axios.delete('/api/bill/delHour?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 2:
            axios.delete('/api/bill/delHour?type=' + 12)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 3:
            axios.delete('/api/bill/delDay?type=' + 1)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 4:
            axios.delete('/api/bill/delDay?type=' + 30)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 5:
            axios.delete('/api/bill/del?type=' + 10)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

                })
                .catch((error) => {
                    console.log(error);
                })
            break;
        case 6:
            axios.delete('/api/bill/del?type=' + 100)
                .then((response) => {


                    toastr.success(response.data.message + " data deleted succesfully");
                    fetchData();

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

const bulkDelete = () => {
    console.log(selected.value);
    axios.delete('/api/bill', {
        data: {
            ids: selected.value
        }
    })
        .then(response => {
            toastr.success(response.data.message);
            fetchData();
        });

    $('#deleteDataModal').modal('hide');
};

const confirmDataDeletion = (id) => {
    $('#deleteDataModal').modal('show');
};


// const selectAllDatas = () => {
//     if (selectAll.value) {
//         selectedData.value = listpays.value.data.map(pay => pay.id);
//     } else {
//         selectedData.value = [];
//     }
//     console.log(selectedData.value);
// }

// watch(searchQuery, debounce(() => {
//     search();
// }, 300));


// const pay_count = computed(() => {
//     return pay_status.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
// })

watch(searchQuery, debounce(() => {
    fetchData();
}, 300));

const fetchData = (link = `/api/bill`) => {
    isLoading.value = true;
    var fil = {
        'key': null,
        'value': null
    };
    if (switchMode.value) {
        fil['mode'] = 'nonperiod';
    } else {
        fil['mode'] = 'period';
    }


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
                query: searchQuery.value,
                mode: fil.mode
            }
        }).then((response) => {
            listBill.value = response.data;
        }).finally(() => {
            isLoading.value = false;
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
const getAccount = () => {
    axios.get('/api/account/only', {
        params: {
            type: 2
        }
    }
    )
        .then((response) => {
            accounts.value = response.data;
        })
}

const editData = (bill) => {

    form.value.id = bill.id;
    form.value.price = bill.amount;
    form.value.santri = bill.santri;
    form.value.account = bill.account;
    form.value.period = bill.month;
    form.value.remain = bill.remainder;

    $('#editDataModal').modal('show');
}
const confirmUpdate = () => {

    if (validate()) {

        $('#editDataModal').modal('hide');
        $('#conEditDataModal').modal('show');
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
        errors.value.price = 'Isi Jumlah Tagihan '
        err += 1;
    }
    if (form.value.remain == '') {
        errors.value.remain = 'Isi Sisa Tagihan '
        err += 1;
    }
    if (form.value.period == '') {
        errors.value.period = 'Pilih Periode Tagihan '
        err += 1;
    }
    if (form.value.remain > form.value.price) {
        errors.value.remain = 'Sisa tagihan tidak boleh lebih dari total tagihan'
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

    axios.put(`/api/bill/${form.value.id}`, form.value)
        .then((response) => {
            toastr.success('Pay created successfully!');
            fetchData();
            $('#conEditDataModal').modal('hide');
        })
        .catch((error) => {
            console.log(error);
        })

};

watch(switchMode, debounce(() => {
    fetchData();
}, 300));

onMounted(() => {
    fetchData();
    getSantri();
    getAccount();

})



</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><strong>Tagihan </strong></h1>
                    <p><small>List tagihan di pondok pesantren</small></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item ">
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Tagihan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="row pb-3">
                <!--
                    demo ditutup sementara

                    <div class="col-md-3">
                    <button class="w-100 btn btn-primary" type="button" data-toggle="collapse"
                        data-target="#collapseWidthExample" aria-expanded="false" aria-controls="collapseWidthExample">
                        Hapus Per Waktu <i class="ml-1 right fas fa-trash"></i>
                    </button>

                    <div class="collapse mt-2" id="collapseWidthExample">

                        <Form @submit="delPrompt" class="row">
                            <div class="col-md">

                                <Field as="select" class="form-control" name="delType">
                                    <option disabled>Pilih Salah Satu</option>
                                    <option v-for="t in types" :value="t.id">
                                        {{ t.name }}
                                    </option>
                                </Field>


                            </div>
                            <div class="col-md">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>

                        </Form>
                    </div>
                    <div class="modal" tabindex="-1" role="dialog" id="myModal">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modal title</h5>
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
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" @click="delMass">Save
                                        changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
                <router-link to="/admin/billing/bill/create" class="col-md">
                    <button class="btn btn-primary w-100"><i class="fa fa-plus-circle mr-1"></i>Tambah Tagihan
                    </button>
                </router-link>
            </div>
            <div class="row">
                <div class="col-md-3" v-if="selected.length > 0">
                    <button @click="confirmDataDeletion" type="button" class=" w-100 mb-2 btn btn-danger">
                        <i class="fa fa-trash mr-1"></i>
                        Hapus {{ selected.length }} Data
                    </button>

                </div>

                <div class="mb-2 col-md">
                    <div class="input-group w-100 ">
                        <input type="text" v-model="searchQuery" class=" form-control" placeholder="Search..." />
                    </div>
                </div>

            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" v-model="switchMode" class="custom-control-input" id="customSwitch3">
                        <label class="font-weight-lighter font-italic custom-control-label" for="customSwitch3">Periodik /
                            Non Periodik</label>
                    </div>
                </div>
            </div>
            <table class="table table-bordered hover ">
                <thead>
                    <tr>
                        <th class="text-center"><input type="checkbox" v-model="selectAll" @change="selectAllGroups" /></th>
                        <th>Dibuat
                            <span class="float-right" @click="sort('created_at')">
                                <i :class="{ 'text-primary': filter.created_at == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.created_at == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>

                        <th> <span>Nama Akun </span>
                            <span class=" float-right" @click="sort('account_id')">
                                <i :class="{ 'text-primary': filter.account_id == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.account_id == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th> <span>Santri </span>
                            <span class="float-right" @click="sort('nis')">
                                <i :class="{ 'text-primary': filter.nis == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.nis == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th>Tagihan
                            <span class="float-right" @click="sort('amount')">
                                <i :class="{ 'text-primary': filter.amount == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.amount == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th> <span>Sisa </span>
                            <span class="float-right" @click="sort('remainder')">
                                <i :class="{ 'text-primary': filter.remainder == false }"
                                    class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.remainder == true }"
                                    class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th v-if="!switchMode"> <span>Periode </span>
                            <span class="float-right" @click="sort('month')">
                                <i :class="{ 'text-primary': filter.month == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.month == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th v-else> <span>Judul </span>
                            <span class="float-right" @click="sort('title')">
                                <i :class="{ 'text-primary': filter.title == false }" class="fas fa-long-arrow-alt-up"></i>
                                <i :class="{ 'text-primary': filter.title == true }" class="fas fa-long-arrow-alt-down"></i>
                            </span>
                        </th>
                        <th class="text-center">Operator</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody v-if="isLoading">
                    <tr>
                        <td class="text-center m-2" colspan="9">

                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                            <div class="spinner-grow spinner-grow-sm mr-1 text-primary"></div>
                        </td>


                    </tr>
                </tbody>
                <tbody v-else>

                    <tr v-for="(bill) in listBill.data" :key="bill.id">
                        <td class="text-center"><input type="checkbox" :checked="selectAll"
                                @change="toggleSelection(bill)" />
                        </td>
                        <td>{{ formatDate(bill.created_at) }}</td>

                        <td>{{ bill.account.account_name }}</td>
                        <td>{{ bill.santri.fullname }}</td>
                        <td>{{ formatMoney(bill.amount) }}</td>
                        <td>{{ formatMoney(bill.remainder) }}</td>
                        <td v-if="!switchMode">{{ bill.month }}</td>
                        <td v-else>{{ bill.title }}</td>
                        <td>{{ bill.operator.fullname }}</td>
                        <td class="text-center">
                            <a href="#" @click="editData(bill)">
                                <i class="mx-auto fa fa-edit mr-2"></i>
                            </a>
                        </td>
                    </tr>
                    <tr v-if="listBill.data.length == 0">
                        <td colspan="9" class="text-center">Tidak Ada Data</td>
                    </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li v-for="link in listBill.links" :key="link.label"
                        :class="{ 'active': link.active, 'disabled': link.url == null }" class="page-item">
                        <a class="page-link" v-html="link.label" href="#" @click="fetchData(link.url)"></a>
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
                        <div class="col-4">Periode </div>
                        <div class="col-8"> : {{ form.period }}</div>
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
                                        placeholder="Pilih Satu ..." label="fullname" track-by="id" :show-labels="false">
                                        <template v-slot:option="{ option }">
                                            <div>{{ option.fullname }} - {{ option.nis }} </div>
                                        </template>
                                    </VueMultiselect>
                                    <span class="invalid-feedback">{{ errors.santri }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulan</label>


                                    <input @v-if="!switchRange" :class="{ 'is-invalid': errors.period }" class="form-custom"
                                        v-model="form.period" type="month" />
                                    <span class="invalid-feedback">{{ errors.period }}</span>



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
                                    <label>Jumlah Tagihan</label>
                                    <input class="form-custom" :class="{ 'is-invalid': errors.price }" v-model="form.price"
                                        type="number" />
                                    <span class="invalid-feedback">{{ errors.price }}</span>
                                    <p>{{ formatMoney(form.price) }}</p>
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
                                    <p>{{ formatMoney(form.remain) }}</p>
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
