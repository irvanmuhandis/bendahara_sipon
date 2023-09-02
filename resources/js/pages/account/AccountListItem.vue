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
            {{ account.account_type==1?"Hutang":account.account_type==2?"Tagihan":"Lain lain" }}
            <!-- <select class="form-control" @change="changeRole(account, $event.target.value)">
                <option v-for="role in type" :key="role.id" :value="role.value" :selected="(account.account_type === role.value)">{{ role.name }}</option>
            </select> -->
        </td>
        <td class="text-center">
            <a href="#" @click.prevent="$emit('editAccount', account)"><i class="fa fa-edit"></i></a>
        </td>
    </tr>


</template>
