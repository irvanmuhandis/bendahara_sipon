<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../toastr';
import { Form, Field, useResetForm, useField, useForm, validate } from 'vee-validate';
import * as yup from 'yup';
import { formatDate, formatMonth } from '../../helper.js';
import accounting from 'accounting';

const toastr = useToastr();
const router = useRouter();
const user = ref([]);
const users = ref([]);
const groups = ref([]);
const accounts = ref([]);
const groupusers = ref([]);

const formatted = ref();
const formatMadin = ref();
const formatSyah = ref();
const formatWifi = ref();
const formatted_s = ref();
const switchRange = ref();


const switchRange_g = ref();
const switchAcc = ref(false);

const errors = ref({
    'user': null,
    'period': null,
    'period_start': null,
    'period_end': null,
    'price': null,
    'account': null
});
const form = ref({
    'user': [],
    'period': null,
    'period_start': null,
    'period_end': null,
    'price': null,
    'account': null
});

const getUser = async () => {

    try {
        const response = await axios.get(`/api/userlist`)
        users.value = response.data;
        console.log('user added');



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
});




const createBill = (values, { resetForm, actions }) => {
    console.log(switchAcc.value);
    if (!switchRange_g.value) {
        if (!switchAcc.value) {
            axios.post('/api/bill-group', values)
                .then((response) => {

                    resetForm();
                    formatted.value = null;
                    toastr.success('Pay created successfully!');
                    getBill();
                })
                .catch((error) => {
                    console.log(error);
                })
        }
        else {
            axios.post('/api/bill-group-mult', values)
                .then((response) => {
                    resetForm();
                    formatted.value = null;
                    formatMadin.value = null;
                    formatSyah.value = null;
                    formatWifi.value = null;
                    toastr.success('Pay created successfully!');
                    getBill();
                })
                .catch((error) => {
                    console.log(error);
                })
        }
    }
    else {
        if (!switchAcc.value) {
            axios.post('/api/bill-grouprange', values)
                .then((response) => {
                    resetForm();
                    formatted.value = null;
                    toastr.success('Pay created successfully!');
                    getBill();
                })
                .catch((error) => {
                    console.log(error);
                })
        }
        else {
            axios.post('/api/bill-grouprange-mult', values)
                .then((response) => {
                    resetForm();
                    formatted.value = null;
                    formatMadin.value = null;
                    formatSyah.value = null;
                    formatWifi.value = null;
                    toastr.success('Pay created successfully!');
                    getBill();
                })
                .catch((error) => {
                    console.log(error);
                })
        }
    }

};
const clearform = () =>{
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
    if (form.value.account == null) {
        errors.value.account = 'Pilih akun '
        err += 1;
    }
    if (form.value.price == null) {
        errors.value.price = 'Pilih price '
        err += 1;
    }

    if (!switchRange.value) {
        if (form.value.period == null) {
            errors.value.period = 'Pilih Period '
            err += 1;
        }
    }
    else {
        if (form.value.period_start == null) {
            errors.value.period_start = 'Pilih Periode Awal '
            err += 1;
        }
        if (form.value.period_end == null) {
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


const createBill_s = (event) => {

    console.log(switchAcc);
    console.log(user.value);
    console.log(form.value);
    console.log(errors.value);
    event.preventDefault();
    form.value.user = user.value.map(user => {
        return user.id;
    });
    if (validateBill()) {
        form.value.operator = 1;
        if (!switchRange.value) {
            axios.post('/api/bill-single', form.value)
                .then(() => {
                    formatted_s.value = null;
                    clearform();
                    toastr.success('Pay created successfully!');
                    getBill();
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
                    toastr.success('Pay created successfully!');
                    getBill();
                })
                .catch((error) => {
                    console.log(error);
                })

        }
    }
};

const groupchange = (event) => {
    groupusers.value = [];
    axios.get('/api/user/group', {
        params: {
            group_id: event.target.value
        }
    })
        .then((response) => {
            groupusers.value = response.data;
        });
}

const getAccount = () => {
    axios.get('/api/except', {
        params: {
            except: 1
        }
    })
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

const handleChange = (event) => {
    if (event.target.name == 'wifi') {
        formatWifi.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    }
    else if (event.target.name == 'madin') {
        formatMadin.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    }
    else if (event.target.name == 'syah') {
        formatSyah.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    }
    else {
        formatted.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
    }
}

const handleChange_s = (event) => {
    formatted_s.value = accounting.formatMoney(event.target.value, 'Rp. ', 0);
}


const getBill = () => {
    if ($.fn.DataTable.isDataTable('#myTable')) {
        $('#myTable').DataTable().destroy();
    }
    axios.get(`/api/bill`)
        .then((response) => {

            $("#myTable").DataTable({
                destroy: true,
                columnDefs: [
                    {
                        targets: [-1, -2],
                        className: 'dt-body-center'
                    }
                ],
                dom: "Bfrtip",
                buttons: [

                    'colvis',
                    "copy",
                    "csv",
                    'pageLength'
                ],

                data: response.data,
                responsive: true,
                columns: [
                    {
                        data: 'created_at',
                        render(data) {
                            return formatDate(data);
                        }
                    },
                    { data: "name" },
                    { data: "bill_amount" },
                    {
                        data: 'due_date',
                        render(data) {
                            return formatMonth(data);
                        },
                    },

                    {
                        data: "account_name"
                    },
                    {
                        data: "payment_status",
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        data: 'operator'
                    },
                    {
                        data: ['id'],
                        render: function (data) {
                            return `
                                <a href="#" class="mr-2" data-id="${data}" id="show_bill" >
                                    <i class="fas fa-eye "></i>
                                            </a>

                                <a href="#" data-id="${data}" id="edit" >
                                    <i class="fa fa-edit mr-2"></i>
                                </a>
                                <a href="#" data-id="${data}" id="del" >
                                    <i class="fa fa-trash text-danger"></i>
                                </a>

                                    `

                            //     <div class="delete-checkbox">
                            //     <i class="fa fa-trash text-danger"></i>
                            //     <input type="checkbox" name="delete-checkbox"  value="${data}">
                            // </div>

                        }
                    }
                ],
            });
            $('#myTable tbody').on('click', "#del", function () {
                let id = $(this).data('id');
                confirmPayDeletion(id);
            });

        })
    $('#myTable tbody').on('click', '#edit', function () {
        var row = $(this).closest('tr');
        var table = $('#myTable').DataTable();
        var rowData = table.row(row).data();
        // Do something with the row data, such as displaying it in a form for editing
        console.log(rowData);
        editPay(rowData);
    });
}


onMounted(() => {
    getUser();
    getBill();
    getGroup();
    getAccount();
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <RouterLink to="/admin/bill"><i class="fa fa-arrow-left"></i><strong> Kembali</strong></RouterLink>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/dashboard">Home</RouterLink>
                        </li>
                        <li class="breadcrumb-item">
                            <RouterLink to="/admin/bill">Bill</RouterLink>
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
                        <li class="nav-item"><a class="nav-link" href="#single" data-toggle="tab">User Create Bill</a>
                        </li>
                        <li class="nav-item"><a class="nav-link active" href="#group" data-toggle="tab">Group Create Bill
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="single">

                            <form @submit="createBill_s">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>User</label>
                                            <VueMultiselect v-model="user" :option-height="9" :options="users"
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
                                        <div class="form-group">
                                            <label>Periodic</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" v-model="switchRange" class="custom-control-input"
                                                    id="customSwitch1">
                                                <label class="font-weight-lighter font-italic custom-control-label"
                                                    for="customSwitch1">Single/Range</label>
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Account</label><br>
                                            <VueMultiselect v-model="form.account" :option-height="9" :options="accounts"
                                                :class="{ 'is-invalid': errors.account }" :multiple="true"
                                                :close-on-select="true" placeholder="Pilih Satu / Lebih" label="account_name"
                                                track-by="id" :show-labels="false">
                                                <template v-slot:option="{ option }">
                                                    <div>{{ option.account_name }} - {{ option.id }} </div>
                                                </template>
                                            </VueMultiselect>
                                            <span class="invalid-feedback">{{ errors.account }}</span>
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Price</label>
                                            <input @keyup="handleChange_s" class="form-custom"
                                                :class="{ 'is-invalid': errors.price }" v-model="form.price"
                                                type="number" />
                                            <span class="invalid-feedback">{{ errors.price }}</span>
                                            <p>{{ formatted_s }}</p>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="w-100 btn btn-primary">Submit</button>
                            </form>
                        </div>


                        <div class="tab-pane active" id="group">
                            <Form @submit="createBill"
                                :validation-schema="!switchRange_g ? (!switchAcc ? createBillSchema : createBillSchema_mult) : (!switchAcc ? createBillSchema_r : createBillSchema_rMult)"
                                v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Group</label>
                                            <Field as="select" @change="groupchange" class="form-control"
                                                :class="{ 'is-invalid': errors.group }" name="group">
                                                <option disabled>Pilih Salah Satu</option>

                                                <option v-for="group in groups" :value="group.id">{{ group.id + " | " +
                                                    group.group_name }}
                                                </option>

                                            </Field>
                                            <span class="invalid-feedback">{{ errors.group }}</span>
                                        </div>
                                        <div v-if="!switchAcc" class="">
                                            <div class="form-group">
                                                <label>Account</label><br>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" v-model="switchAcc" class="custom-control-input"
                                                        id="customSwitch3">
                                                    <label class="font-weight-lighter font-italic custom-control-label"
                                                        for="customSwitch3">Single/Multiple</label>
                                                </div>
                                                <Field as="select" class="form-control"
                                                    :class="{ 'is-invalid': errors.account }" name="account">
                                                    <option disabled>Pilih Salah Satu</option>

                                                    <option v-for="account in accounts" :value="account.id">
                                                        {{ account.id + ` | ` +
                                                            account.account_name }}
                                                    </option>

                                                </Field>
                                                <span class="invalid-feedback">{{ errors.account }}</span>
                                            </div>
                                            <div class="form-group">
                                                <label>Price</label>
                                                <Field :class="{ 'is-invalid': errors.price }" @keyup="handleChange"
                                                    class="form-control" name="price" type="number" />
                                                <p>{{ formatted }}</p>
                                                <span class="invalid-feedback">{{ errors.price }}</span>
                                            </div>
                                        </div>
                                        <div v-else class="">
                                            <div class="form-group">
                                                <label>Account</label><br>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" v-model="switchAcc" class="custom-control-input"
                                                        id="customSwitch3">
                                                    <label class="font-weight-lighter font-italic custom-control-label"
                                                        for="customSwitch3">Single/Multiple</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="text-right text-primary font-weight-normal">Syahriah</label>
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
                                        </div>
                                        <div class="form-group">
                                            <label>Periodic</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" v-model="switchRange_g" class="custom-control-input"
                                                    id="customSwitch2">
                                                <label class="font-weight-lighter font-italic custom-control-label"
                                                    for="customSwitch2">Single/Range</label>
                                            </div>
                                            <div v-if="!switchRange_g">
                                                <Field :class="{ 'is-invalid': errors.period }" class="form-control"
                                                    name="period" type="month" />
                                                <span class="invalid-feedback">{{ errors.period }}</span>
                                            </div>
                                            <div v-else class="row">
                                                <div class="col-md-6">
                                                    <Field :class="{ 'is-invalid': errors.period_start }"
                                                        class="form-control" name="period_start" type="month" />

                                                    <span class="invalid-feedback">{{ errors.period_start }}</span>
                                                </div>
                                                <div class="col-md-6 mt-md-0 mt-2">
                                                    <Field :class="{ 'is-invalid': errors.period_end }" class="form-control"
                                                        name="period_end" type="month" />

                                                    <span class="invalid-feedback">{{ errors.period_end }}</span>
                                                </div>
                                            </div>
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
                            </Form>

                        </div>



                    </div>

                </div>
            </div>
                    <div class="row-md">
                        <div class="col-md">
                            <h1></h1>
                        </div>
                    </div>
                    <table id="myTable" class="display table " style="overflow: auto;width:100%">
                        <thead>
                            <tr>
                                <th>Created</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Periode</th>
                                <th>Account</th>
                                <th>Status</th>
                                <th>Update</th>
                                <th>Operator</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

           

    </div>
</template>


