<script setup>
import { formatDate } from '../../helper.js';
import { formatDateTimestamp } from '../../helper.js';
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
        <td>{{ dispen.user.name }}</td>
        <td>{{ dispen.dispen_periode }}</td>
        <td>{{ formatDate(dispen.pay_at) }}</td>
        <td>{{ dispen.dispen_desc }}</td>
        <td>{{ formatDateTimestamp(dispen.updated_at) }}</td>
        <td>{{ formatDateTimestamp(dispen.created_at) }}</td>
        <td>
            <a href="#" @click.prevent="$emit('editDispen', dispen)"><i class="fa fa-edit"></i></a>
            <a href="#" @click.prevent="$emit('confirmDispenDeletion', dispen.id)"><i
                    class="fa fa-trash text-danger ml-2"></i></a>
        </td>
    </tr>
</template>
