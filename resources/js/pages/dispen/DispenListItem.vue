<script setup>
import { formatDate, formatStatusDispen } from '../../helper.js';
import { ref } from 'vue';
import { useToastr } from '../../toastr.js';
import axios from 'axios';

const toastr = useToastr();

const props = defineProps({
    dispen: Object,
    index: Number,
    selectAll: Boolean,
});

const emit = defineEmits(['dispenDeleted', 'editDispen', 'confirmDispenDeletion']);

const toggleSelection = () => {
    emit('toggleSelection', props.dispen);
}
</script>
<template>
    <tr>
        <td><input type="checkbox" :checked="selectAll" @change="toggleSelection" /></td>
        <td>{{ dispen.santri.fullname }} - {{ dispen.santri.nis }}</td>
        <td>{{ dispen.dispen_periode }}</td>
        <td>{{ formatDate(dispen.pay_at) }}</td>
        <td>{{ dispen.dispen_desc }}</td>
        <td v-html=" formatStatusDispen( dispen.status)"></td>
        <td>{{ formatDate(dispen.updated_at) }}</td>
        <td>
            <a href="#" @click.prevent="$emit('editDispen', dispen)"><i class="fa fa-edit"></i></a>
           </td>
    </tr>
</template>
