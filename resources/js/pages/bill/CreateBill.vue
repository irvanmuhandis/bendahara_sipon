<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm, validate } from 'vee-validate';
import * as yup from 'yup';
import { formatDate, formatMonth, formatStatus, formatMoney } from '../../helper.js';
import accounting from 'accounting';

const toastr = useToastr();
const santris = ref([]);
const groups = ref([]);
const accounts = ref([]);
const groupsantris = ref([]);

const formatted = ref();
const formatted_s = ref();
const switchRange = ref();
const switchAcc_s = ref(false);

const switchRange_g = ref();
const switchAcc = ref(false);

const errors = ref({
    'santri': null,
    'period': null,
    'period_start': null,
    'period_end': null,
    'price': null,
    'account': null
});

const errors2 = ref({
    'group': null,
    'period': null,
    'period_start': null,
    'period_end': null,
    'price': null,
    'account': null,
});


const form = ref({
    'santri': [],
    'period': '',
    'period_start': '',
    'period_end': '',
    'price': '',
    'account': null,
    'periodic': []
});

const form2 = ref({
    'group': null,
    'period': null,
    'period_start': null,
    'period_end': null,
    'price': null,
    'account': null,
    'periodic': []
});

const getSantri = async () => {

    try {
        const response = await axios.get(`/api/santrilist`)
        santris.value = response.data;
        console.log('santri added');
    } catch (error) {
        console.error(error);
    }
}


const createBillSchema = yup.object({
    group: yup.number().required(),
    price: yup.number().required(),
    period: yup.date().required(),
    account: yup.number().required(),
});

const createBillSchema_mult = yup.object({
    group: yup.number().required(),
    period: yup.date().required(),
    syah: yup.number().required(),
    madin: yup.number().required(),
    wifi: yup.number().required(),
});

const createBillSchema_r = yup.object({
    group: yup.number().required(),
    price: yup.number().required(),
    period_start: yup.date().required(),
    period_end: yup.date().required(),
    account: yup.number().required(),
});

const createBillSchema_rMult = yup.object({
    group: yup.number().required(),
    period_start: yup.date().required(),
    period_end: yup.date().required(),
    syah: yup.number().required(),
    madin: yup.number().required(),
    wifi: yup.number().required(),
    periodic: yup.array(
        yup.object({
            dayOfWeek: yup.string().required(),
            checked: yup.boolean().required()
        })
    )
});




const createBill = () => {

    console.log(form2.value);
    console.log(errors2.value);
    if (form2.value.account != null) {
        form2.value.account = form2.value.account.id;
    }
    if (form2.value.group != null) {
        form2.value.group = form2.value.group.id;
    }
    if (validateBill2()) {
        axios.post('/api/bill-group', form2.value)
            .then((response) => {
                formatted.value = null;
                toastr.success('Berhasil membuat tagihan!');

            })
            .catch((error) => {
                console.log(error);
            })
    }


    // if (!switchRange_g.value) {
    //     if (!switchAcc.value) {
    //         axios.post('/api/bill-group', values)
    //             .then((response) => {

    //                 resetForm();
    //                 formatted.value = null;
    //                 toastr.success('Berhasil membuat tagihan!');
    //
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             })
    //     }
    //     else {
    //         axios.post('/api/bill-group-mult', values)
    //             .then((response) => {
    //                 resetForm();
    //                 formatted.value = null;
    //                 formatMadin.value = null;
    //                 formatSyah.value = null;
    //                 formatWifi.value = null;
    //                 toastr.success('Berhasil membuat tagihan!');
    //
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             })
    //     }
    // }
    // else {
    //     if (!switchAcc.value) {
    //         axios.post('/api/bill-grouprange', values)
    //             .then((response) => {
    //                 resetForm();
    //                 formatted.value = null;
    //                 toastr.success('Berhasil membuat tagihan!');
    //
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             })
    //     }
    //     else {
    //         axios.post('/api/bill-grouprange-mult', values)
    //             .then((response) => {
    //                 resetForm();
    //                 formatted.value = null;
    //                 formatMadin.value = null;
    //                 formatSyah.value = null;
    //                 formatWifi.value = null;
    //                 toastr.success('Berhasil membuat tagihan!');
    //
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             })
    //     }
    // }

};
const clearform = () => {
    for (const key in errors.value) {
        errors.value[key] = null;
    }
    for (const key in form.value) {

        if (Array.isArray(form.value[key])) {
            form.value[key] = [];
        }
        else {
            form.value[key] = null;
        }
    }
}

const validateBill = () => {
    var err = 0;

    for (const key in errors.value) {
        errors.value[key] = null;
    }
    if (form.value.santri.length == 0) {
        errors.value.santri = 'Pilih Santri ';
        err += 1;
    }

    if (switchAcc_s.value) {
        var atleast_one = 0;
        form.value.periodic.forEach(element => {
            if (element.value != "") {
                atleast_one += 1;
            }
        });
        if (atleast_one == 0) {
            errors.value.account = 'Isi jumlah tagihan minimal satu';
            err += 1;
        }
    }
    else {
        if (form.value.price == ''||form.value.period == null) {
            errors.value.price = 'Pilih jumlah tagihan '
            err += 1;
        }
        if (form.value.account == null) {
            errors.value.account = 'Pilih akun '
            err += 1;
        }
    }


    if (!switchRange.value) {
        if (form.value.period == ''||form.value.period == null) {
            errors.value.period = 'Pilih Periode '
            err += 1;
        }
    }
    else {
        if (form.value.period_start == '') {
            errors.value.period_start = 'Pilih Periode Awal '
            err += 1;
        }
        if (form.value.period_end == '') {
            errors.value.period_end = 'Pilih Periode Akhir '
            err += 1;
        }
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}
const validateBill2 = () => {
    var err = 0;

    for (const key in errors2.value) {
        errors2.value[key] = null;
    }
    if (form2.value.group == null) {
        errors2.value.group = 'Pilih Grup ';
        err += 1;
    }
    if (!switchAcc.value) {
        if (form2.value.account == null) {
            errors2.value.account = 'Pilih akun '
            err += 1;
        }
        if (form2.value.price == null) {
            errors2.value.price = 'Pilih price '
            err += 1;
        }
    }

    if (!switchRange.value) {
        if (form2.value.period == null) {
            errors2.value.period = 'Pilih Period '
            err += 1;
        }
    }
    else {
        if (form2.value.period_start == null) {
            errors2.value.period_start = 'Pilih Periode Awal '
            err += 1;
        }
        if (form2.value.period_end == null) {
            errors2.value.period_end = 'Pilih Periode Akhir '
            err += 1;
        }
    }

    if (err == 0) {
        return true;
    }
    else {
        return false;
    }
}



const createBill_s = (event) => {

    console.log(switchAcc);
    console.log(form.value);
    console.log(errors.value);
    event.preventDefault();
    if (validateBill()) {
        if (!switchRange.value) {
            axios.post('/api/bill-single', form.value)
                .then(() => {
                    formatted_s.value = null;
                    clearform();
                    toastr.success('Berhasil membuat tagihan!');
                    getPeriodic();
                })
                .catch((error) => {
                    console.log(error);
                })
        }
        else {
            axios.post('/api/bill-singlerange', form.value)
                .then(() => {
                    formatted_s.value = null;
                    clearform();
                    toastr.success('Berhasil membuat tagihan!');
                    getPeriodic();
                })
                .catch((error) => {
                    console.log(error);
                })

        }
    }
};

const groupchange = (event) => {
    groupsantris.value = [];
    axios.get(`/api/santri/group/${form2.value.group.id}`)
        .then((response) => {
            groupsantris.value = response.data;
        });
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

const getGroup = () => {
    axios.get('/api/group/list')
        .then((response) => {
            groups.value = response.data;
        })
}

const handleChange = (event, index) => {

    console.log(event);
    console.log(index);
    // if (event.target.name == 'wifi') {
    //     formatWifi.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    // }
    // else if (event.target.name == 'madin') {
    //     formatMadin.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    // }
    // else if (event.target.name == 'syah') {
    //     formatSyah.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    // }
    // else {
    //     //formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    //     formatPeriod[index].value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    // }
}

const handleChange_s = (event) => {
    formatted_s.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}


const getPeriodic = () => {
    axios.get('/api/account/period')
        .then((response) => {
            response.data.forEach(element => {
                form2.value.periodic.push(
                    {
                        'id': element.id,
                        'name': element.account_name,
                        'value': ""
                    }
                );
                form.value.periodic.push(
                    {
                        'id': element.id,
                        'name': element.account_name,
                        'value': ""
                    }
                )
            });
        })
}

const AccChange_S = () => {
    form.value.account = null;
    form.value.periodic.forEach((element) => {
        element.value = '';
      });
    form.value.price = '';
}

const changeMonth_S = () => {
    form.value.period = '';
    form.value.period_start = '';
    form.value.period_end = '';
}

onMounted(() => {
    getSantri();
    getGroup();
    getAccount();
    getPeriodic();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <RouterLink to="/admin/billing/bill"><i class="fa fa-arrow-left"></i><strong> Kembali</strong>
                    </RouterLink>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/">Beranda</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/billing/bill">Tagihan</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Tambah Tagihan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card">
                <!-- <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link  active" href="#single" data-toggle="tab">Santri</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#group" data-toggle="tab">Grup
                            </a>
                        </li>
                    </ul>
                </div> -->
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="group">
                            <!-- :validation-schema="!switchRange_g ? (!switchAcc ? createBillSchema : createBillSchema_mult) : (!switchAcc ? createBillSchema_r : createBillSchema_rMult)"
                                v-slot:default="{ errors }" -->
                            <form @submit.prevent="createBill">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Grup</label>
                                            <VueMultiselect :option-height="9" :options="groups" @select="groupchange"
                                                :class="{ 'is-invalid': errors2.group }" v-model="form2.group"
                                                :multiple="false" :close-on-select="true" placeholder="Pilih Satu ..."
                                                label="group_name" track-by="id" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.group_name }} - {{ option.id }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors2.group }}</span>
                                        </div>
                                        <div v-if="!switchAcc">
                                            <div class="form-group">
                                                <label>Akun</label><br>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" v-model="switchAcc" @change="AccChange"
                                                        class="custom-control-input" id="customSwitch1">
                                                    <label class="font-weight-lighter font-italic custom-control-label"
                                                        for="customSwitch1">Tunggal / Jamak</label>
                                                </div>

                                                <VueMultiselect :option-height="9" :options="accounts"
                                                    :class="{ 'is-invalid': errors2.account }" v-model="form2.account"
                                                    :multiple="false" :close-on-select="true" placeholder="Pilih Satu ..."
                                                    label="account_name" track-by="id" :show-labels="false">
                                                    <template v-slot:option="{ option }">
                                                        <div>{{ option.account_name }} - {{ option.id }} </div>
                                                    </template>
                                                </VueMultiselect>
                                                <span class="invalid-feedback">{{ errors2.account }}</span>
                                            </div>
                                            <div class="form-group">
                                                <label>Jumlah Tagihan</label>
                                                <input :class="{ 'is-invalid': errors2.price }" class="form-control"
                                                    v-model="form2.price" type="number" />
                                                <p>{{ formatMoney(form2.price) }}</p>
                                                <span class="invalid-feedback">{{ errors2.price }}</span>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <div class="form-group">
                                                <label>Akun</label><br>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" v-model="switchAcc" class="custom-control-input"
                                                        id="customSwitch2">
                                                    <label class="font-weight-lighter font-italic custom-control-label"
                                                        for="customSwitch2">Tunggal / Jamak</label>
                                                </div>
                                            </div>
                                            <div v-for="(data, index) in form2.periodic" class="form-group">
                                                <label class="text-right text-primary font-weight-normal">{{
                                                    data.name }}</label>
                                                <input v-model="data.value" class="form-control" type="number" />
                                                <p>{{ formatMoney(data.value) }}</p>
                                            </div>
                                            <!-- <span>
                                                <div class="form-group">
                                                    <label
                                                        class="text-right text-primary font-weight-normal">Syahriah</label>
                                                    <Field :class="{ 'is-invalid': errors.syah }" @keyup="handleChange"
                                                        class="form-control" name="syah" type="number" />
                                                    <p>{{ formatSyah }}</p>
                                                    <span class="invalid-feedback">{{ errors.syah }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <label class="text-right text-primary font-weight-normal">Madin</label>
                                                    <Field :class="{ 'is-invalid': errors.madin }" @keyup="handleChange"
                                                        class="form-control" name="madin" type="number" />
                                                    <p>{{ formatMadin }}</p>
                                                    <span class="invalid-feedback">{{ errors.madin }}</span>
                                                </div>
                                                <div class="form-group">
                                                    <label class="text-right text-primary font-weight-normal">Wifi</label>
                                                    <Field :class="{ 'is-invalid': errors.wifi }" @keyup="handleChange"
                                                        class="form-control" name="wifi" type="number" />
                                                    <p>{{ formatWifi }}</p>
                                                    <span class="invalid-feedback">{{ errors.wifi }}</span>
                                                </div>
                                            </span> -->
                                        </div>
                                        <div class="form-group">
                                            <label>Bulan</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" v-model="switchRange_g" @change="RangeChange"
                                                    class="custom-control-input" id="customSwitch3">
                                                <label class="font-weight-lighter font-italic custom-control-label"
                                                    for="customSwitch3">Tunggal / Periode</label>
                                            </div>
                                            <div v-if="!switchRange_g">
                                                <input :class="{ 'is-invalid': errors2.period }" class="form-control"
                                                    v-model="form2.period" type="month" />
                                                <span class="invalid-feedback">{{ errors2.period }}</span>
                                            </div>
                                            <div v-else class="row">
                                                <div class="col-md-6">
                                                    <input :class="{ 'is-invalid': errors2.period_start }"
                                                        class="form-control" v-model="form2.period_start" type="month" />

                                                    <span class="invalid-feedback">{{ errors2.period_start }}</span>
                                                </div>
                                                <div class="col-md-6 mt-md-0 mt-2">
                                                    <input :class="{ 'is-invalid': errors2.period_end }"
                                                        class="form-control" v-model="form2.period_end" type="month" />

                                                    <span class="invalid-feedback">{{ errors2.period_end }}</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label> Grup Santri
                                            </label>
                                            <table class="table table-sm text-center table-bordered">
                                                <thead>
                                                    <tr>

                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Join At</th>

                                                    </tr>
                                                </thead>
                                                <tr v-if="groupsantris.length === 0">
                                                    <td colspan="3">No Record</td>
                                                </tr>
                                                <tr v-for="santri in groupsantris">
                                                    <td>{{ santri.id }} </td>
                                                    <td>{{ santri.name }}</td>
                                                    <td>{{ santri.join_at }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                                {{ errors2 }}
                            </form>

                        </div>


                        <div class="tab-pane active" id="single">

                            <form @submit="createBill_s">
                                <div class="row">
                                    <div class="col-md">
                                        <div class="form-group">
                                            <label>Santri</label>
                                            <VueMultiselect v-model="form.santri" :option-height="9" :options="santris"
                                                :class="{ 'is-invalid': errors.santri }" :multiple="true"
                                                :close-on-select="false" placeholder="Pilih Satu / Lebih ..."
                                                label="fullname" track-by="nis" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.fullname }} - {{ option.nis }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.santri }}</span>
                                        </div>
                                    </div>
                                    <div v-if="!switchAcc_s" class="col-md-6">
                                        <div class="form-group">
                                            <label>Jumlah Tagihan</label>
                                            <input @keyup="handleChange_s" class="form-custom"
                                                :class="{ 'is-invalid': errors.price }" v-model="form.price"
                                                type="number" />
                                            <span class="invalid-feedback">{{ errors.price }}</span>
                                            <p>{{ formatted_s }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Akun</label><br>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" @change="AccChange_S" v-model="switchAcc_s"
                                                    class="custom-control-input" id="customSwitch6">
                                                <label class="font-weight-lighter font-italic custom-control-label"
                                                    for="customSwitch6">Tunggal / Jamak</label>
                                            </div>

                                            <div v-if="!switchAcc_s">
                                                <VueMultiselect v-model="form.account" :option-height="9"
                                                    :options="accounts" :class="{ 'is-invalid': errors.account }"
                                                    :multiple="false" :close-on-select="true" placeholder="Pilih Satu "
                                                    label="account_name" track-by="id" :show-labels="false">
                                                    <template v-slot:option="{ option }">
                                                        <div>{{ option.account_name }} - {{ option.id }} </div>
                                                    </template>
                                                </VueMultiselect>
                                                <span class="invalid-feedback">{{ errors.account }}</span>
                                            </div>
                                            <div v-else>
                                                <div v-for="(data, index) in form.periodic">
                                                    <label class="text-right text-primary font-weight-normal">{{
                                                        data.name }}</label>
                                                    <input :class="{ 'is-invalid': errors.account }" v-model="data.value"
                                                        class="form-control" type="number" />
                                                    <span>{{ formatMoney(data.value) }}</span>
                                                    <span class="invalid-feedback">{{ errors.account }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bulan</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" v-model="switchRange" @change="changeMonth_S"
                                                    class="custom-control-input" id="customSwitch4">
                                                <label class="font-weight-lighter font-italic custom-control-label"
                                                    for="customSwitch4">Tunggal / Periode</label>
                                            </div>
                                            <div v-if="!switchRange">
                                                <input @v-if="!switchRange" :class="{ 'is-invalid': errors.period }"
                                                    class="form-custom" v-model="form.period" type="month" />
                                                <span class="invalid-feedback">{{ errors.period }}</span>
                                            </div>
                                            <div v-else class="row">
                                                <div class="col-md-6">
                                                    <input class="form-custom"
                                                        :class="{ 'is-invalid': errors.period_start }"
                                                        v-model="form.period_start" type="month" />

                                                    <span class="invalid-feedback">{{ errors.period_start }}</span>
                                                </div>
                                                <div class="col-md-6 mt-md-0 mt-2">
                                                    <input class="form-custom" :class="{ 'is-invalid': errors.period_end }"
                                                        v-model="form.period_end" type="month" />

                                                    <span class="invalid-feedback">{{ errors.period_end }}</span>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                            </form>
                        </div>


                    </div>

                </div>
            </div>
        </div>
    </div>
</template>


