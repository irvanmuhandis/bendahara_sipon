<script setup>
import axios from "axios"

import { ref, onMounted, reactive, computed, watch } from 'vue';
import { useToastr } from '../../toastr.js';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { formatDate, formatStatus, formatClass, formatMoney, formatMoney_2 } from '../../helper.js';

</script>

<template>
    <div class="m-3">
        <h1>Detail
            <span v-if="detailData.ledgerable_type == 'App\\Models\\Trans'">
                Transaksi
            </span>
            <span v-else>
                Pembayaran</span>
        </h1>
        <div class="row ">

            <div class="col-md-6">

                <div class="card">
                    <div class="card-header">
                        Dompet </div>
                    <div class="card-body">
                        <div class="card-body">

                            <table class="table table-borderless">

                                <tfoot>
                                    <tr>
                                        <td colspan="2">Nama</td>
                                        <td class="text-right">{{ detailData.ledgerable.wallet.wallet_name }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Saldo Sebelumnya</td>
                                        <td class="text-right">{{ formatMoney(detailData.ledgerable.wallet.prev_saldo) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Saldo Sekarang</td>
                                        <td class="text-right">{{ formatMoney(detailData.ledgerable.wallet.saldo) }}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="2">Selisih</td>
                                        <td v-if="selisih >= 0" class="text-right ">+ {{ formatMoney(selisih) }}</td>
                                        <td v-else class="text-right ">- {{ formatMoney(-1 * selisih) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Operator</div>
                    <div class="card-body">
                        <table class="table table-borderless">

                            <tfoot>
                                <tr>
                                    <td colspan="2">Nama</td>
                                    <td class="text-right">{{ detailData.ledgerable.operator.name }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Email</td>
                                    <td class="text-right">{{ detailData.ledgerable.operator.email }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Invoice</div>
                    <div class="card-body">
                        <table class="table table-borderless">

                            <tfoot>
                                <tr>
                                    <td colspan="2">Dibuat</td>
                                    <td class="text-right">{{ formatDate(detailData.created_at) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Tipe</td>
                                    <td class="text-right" v-html="formatClass(detailData.ledgerable_type)"></td>
                                </tr>
                                <tr>
                                    <td colspan="2">Akun</td>
                                    <td v-if="detailData.ledgerable_type == 'App\\Models\\Trans'" class="text-right">{{
                                        detailData.ledgerable.account.account_name }}</td>
                                    <td v-else class="text-right">{{
                                        detailData.ledgerable.payable.account.account_name }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Jumlah Uang</td>
                                    <span v-if="detailData.ledgerable_type == 'App\\Models\\Trans'">
                                        <td v-if="detailData.ledgerable.in != 0" class="d-flex justify-content-end"
                                            v-html="formatMoney_2(detailData.ledgerable.in, 1)"></td>
                                        <td v-if="detailData.ledgerable.out != 0" class="d-flex justify-content-end"
                                            v-html="formatMoney_2(detailData.ledgerable.out, 2)"></td>
                                    </span>
                                    <span v-else>
                                        <td class="d-flex justify-content-end"
                                            v-html="formatMoney_2(detailData.ledgerable.in, 1)"></td>
                                    </span>

                                </tr>
                                <tr v-if="detailData.ledgerable_type == 'App\\Models\\Trans'" class="fw-bold">
                                    <td colspan="2">Deskripsi</td>
                                    <td class=" text-right">{{ detailData.ledgerable.desc }}</td>
                                </tr>
                                <tr v-else class="fw-bold">
                                    <td colspan="2">Pembayar</td>
                                    <td class="text-right">{{ detailData.ledgerable.user.name }}</td>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
import { ref } from 'vue';

export default {
    data() {
        return {
            nis: '',
            detailData: '',
            selisih: ''
        }
    },
    created() {
        var path = this.$route.path.split('/');

        this.nis = path[path.length - 1];
        axios.get(`/api/ledger/${this.nis}`)
            .then((response) => {
                console.log(response.data);
                this.detailData = response.data;
                this.selisih = this.detailData.ledgerable.wallet.saldo - this.detailData.ledgerable.wallet.prev_saldo;
            })
            .catch((error) => {
                // Handle error
            });


    }
}
</script>
