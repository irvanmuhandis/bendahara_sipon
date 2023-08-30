<script setup>
import { formatDate } from '../../helper.js';
import { ref } from 'vue';
import { useToastr } from '../../toastr.js';
import axios from 'axios';

const toastr = useToastr();

const props = defineProps({
    account: Object,
    index: Number,
    selectAll: Boolean,
});

const emit = defineEmits(['accountDeleted', 'editAccount','confirmAccountDeletion']);



const type = ref([
    {
        name: 'Insidental',
        value: 1
    },
    {
        name: 'Periodik',
        value: 2,
    }
]);

const changeRole = (account, role) => {
    axios.patch(`/api/accounts/${account.id}/change-role`, {
        role: role,
    })
    .then(() => {
        toastr.success('Role changed successfully!');
    })
};

const toggleSelection = () => {
    emit('toggleSelection', props.account);
}
</script>
<template>
    <tr>
        <td v-if="account.deletable==1"><input type="checkbox" :checked="selectAll" @change="toggleSelection" /></td>
        <td v-else></td>
        <td>{{ account.account_name }}</td>
        <td>{{ formatDate(account.created_at) }}</td>
        <td>
            <select class="form-control" @change="changeRole(account, $event.target.value)">
                <option v-for="role in type" :key="role.id" :value="role.value" :selected="(account.account_type === role.value)">{{ role.name }}</option>
            </select>
        </td>
        <td>
            <a href="#" @click.prevent="$emit('editUser', account)"><i class="fa fa-edit"></i></a>
            <a  v-if="account.deletable==1" href="#" @click.prevent="$emit( 'confirmUserDeletion',account.id)"><i class="fa fa-trash text-danger ml-2"></i></a>
        </td>
    </tr>


</template>
