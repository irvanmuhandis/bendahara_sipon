<script setup>
import { formatDate, formatMoney } from '../helper.js';


const {
    wallets,
    accounts,
    searchQuery,
    filter,
    selected
} = defineProps();

// Local reactive variable
let v_account = filter.account;
let v_wallet = filter.v_wallet;
let v_created_at = filter.v_created_at;

const emit = defineEmits(['getWallet', 'getAccount', 'confirmDataDeletion']);

</script>
<template>
    <div class="row mb-2">
        <div class="col-md-3" v-if="selected.length > 0">
            <button @click="confirmDataDeletion" type="button" class=" w-100 mb-2 btn btn-danger">
                <i class="fa fa-trash mr-1"></i>
                Hapus {{ selected.length }} Data
            </button>

        </div>
        <div class="col-md">
            <div class="form-group">
                <label for="">Akun</label>
                <VueMultiselect @click="getAccount" v-model="v_account" :option-height="9" :options="accounts"
                    :close-on-select="true" placeholder="Pilih Satu " label="account_name" track-by="id"
                    :show-labels="false">
                    <template v-slot:option="{ option }">
                        <div>{{ option.account_name }} - {{ option.id }} </div>
                    </template>
                </VueMultiselect>
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                <label>Dompet</label>
                <VueMultiselect @click="getWallet" v-model="v_wallet" :option-height="9" :options="wallets"
                    :class="{ 'is-invalid': errors.account }" :close-on-select="true" placeholder="Pilih Satu "
                    label="wallet_name" track-by="id" :show-labels="false">
                    <template v-slot:option="{ option }">
                        <div>{{ option.wallet_name }} - {{ formatMoney(option.sum.saldo) }} </div>
                    </template>
                </VueMultiselect>
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                <label>Bulan</label>
                <input class="form-control" v-model="v_created_at" type="month" />
            </div>
        </div>
        <div class="col-md">
            <div class="form-group w-100 ">
                <label for="">Cari Kata Kunci</label>
                <input type="text" :value="searchQuery" class=" form-control" placeholder="Search..." />
            </div>
        </div>

    </div>
</template>
