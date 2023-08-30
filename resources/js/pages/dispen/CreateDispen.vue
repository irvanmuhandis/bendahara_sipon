<script setup>
import axios from 'axios';
import { ref, onMounted, reactive, watch } from 'vue';
import { Form, Field, useResetForm, useForm } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toastr.js';
import DispenListItem from '../dispen/DispenListItem.vue';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const { resetForm } = useForm();
const dispens = ref({ 'data': [] });
const santris = ref([]);
const formValues = ref(null);
const form = ref(null);

const createDispenSchema = yup.object({
    santri: yup.string().required('Pilih santri'),
    pay_at: yup.date().required('Pilih Tanggal Jatuh Tempo'),
    periode: yup.string().required('Pilih Periode'),
    desc: yup.string().required('Isi Deskripsi dispensasi'),
});

const createDispen = (values, { resetForm, setErrors }) => {
    axios.post('/api/dispens', values)
        .then((response) => {
            $('#dispenFormModal').modal('hide');
            resetForm();
            toastr.success('Dispensasi berhasil dibuat!');
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
    formValues.value = null;
};


const getSantri = () => {
    axios.get(`/api/santrilist`)
        .then((response) => {
            santris.value = response.data;
        })
}

const handleSubmit = (values, actions) => {

    createDispen(values, actions);

}

onMounted(() => {
    getSantri();
});
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
                            <RouterLink to="/admin/dispens">Dispensasi</RouterLink>
                        </li>
                        <li class="breadcrumb-item active">Tambah Dispensasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <Form ref="form" @submit="handleSubmit" :validation-schema="createDispenSchema" v-slot="{ errors }"
                :initial-values="formValues">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name Santri</label>
                        <Field @click="getSantri" name="santri" as="select" class="form-control"
                            :class="{ 'is-invalid': errors.santri }" id="santri" aria-describedby="nameHelp"
                            placeholder="Enter full name">
                            <option value="" disabled>Select a Name</option>
                            <option v-for="santri in santris" :key="santri.nis" :value="santri.nis">{{ santri.fullname }}
                            </option>

                        </Field>
                        <span class="invalid-feedback">{{ errors.santri }}</span>
                    </div>

                    <div class="form-group">
                        <label for="desc">Deskripsi</label>
                        <Field name="desc" as="textarea" class="form-control " :class="{ 'is-invalid': errors.desc }"
                            id="desc" aria-describedby="nameHelp" placeholder="Enter description" />
                        <span class="invalid-feedback">{{ errors.desc }}</span>
                    </div>

                    <div class="form-group">
                        <label>Jatuh Tempo</label>
                        <Field name="pay_at" type="date" class="form-control " :class="{ 'is-invalid': errors.pay_at }"
                            id="pay_at" aria-describedby="nameHelp" placeholder="Enter Pay Date" />
                        <span class="invalid-feedback">{{ errors.pay_at }}</span>
                    </div>

                    <div class="form-group">
                        <label>Periode</label>
                        <Field name="periode" type="month" class="form-control " :class="{ 'is-invalid': errors.periode }"
                            id="periode" aria-describedby="nameHelp" placeholder="Enter Periode Date" />
                        <span class="invalid-feedback">{{ errors.periode }}</span>
                    </div>
                    <button type="submit" class="w-100 btn btn-primary">Submit</button>

                </div>

            </Form>
        </div>
    </div>
</template>

