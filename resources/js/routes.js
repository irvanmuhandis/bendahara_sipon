import Dashboard from "./components/Dashboard.vue";

import CreatePayBill from "./pages/pay/PayBill.vue";
import CreatePayDebt from "./pages/pay/PayDebt.vue";
import CreateBill from "./pages/bill/CreateBill.vue";
import CreateGroup from "./pages/group/CreateGroup.vue";
import CreateDebt from "./pages/debts/CreateDebt.vue";

import ListAccount from "./pages/list/ListAccount.vue";
import ListDebt from "./pages/list/ListDebt.vue";
import ListDispen from "./pages/list/ListDispen.vue";
import ListGroup from "./pages/list/ListGroup.vue";
import ListIncome from "./pages/list/ListIncome.vue";
import ListBill from "./pages/list/ListBill.vue";
import ListLedger from "./pages/list/ListLedger.vue";
import ListWallet from "./pages/list/ListWallet.vue";
import ListExpense from "./pages/list/ListExpense.vue";

import DetailLedger from "./pages/ledger/DetailLedger.vue";

export default [
    {
        path: "/admin/dashboard",
        name: "admin.dashboard",
        component: Dashboard,
    },
    {
        path: "/admin/ledger/:id",
        name: "admin.ledger.detail",
        component: DetailLedger,
    },
    {
        path: "/admin/account",
        name: "admin.account",
        component: ListAccount,
    },

    {
        path: "/admin/dispens",
        name: "admin.dispens",
        component: ListDispen,
    },

    {
        path: "/admin/debt",
        name: "admin.debt",
        component: ListDebt,
    },

    {
        path: "/admin/group",
        name: "admin.group",
        component: ListGroup,
    },

    {
        path: "/admin/bill",
        name: "admin.bill",
        component: ListBill,
    },
    {
        path: "/admin/income",
        name: "admin.income",
        component: ListIncome,
    },
    {
        path: "/admin/pay/create-debt",
        name: "admin.pay.create-debt",
        component: CreatePayDebt,
    },
    {
        path: "/admin/pay/create-bill",
        name: "admin.pay.create-bill",
        component: CreatePayBill,
    },
    {
        path: "/admin/bill/create",
        name: "admin.bill.create",
        component: CreateBill,
    },
    {
        path: "/admin/debt/create",
        name: "admin.debt.create",
        component: CreateDebt,
    },
    {
        path: "/admin/group/create",
        name: "admin.group.create",
        component: CreateGroup,
    },
    {
        path: "/admin/wallet",
        name: "admin.wallet",
        component: ListWallet,
    },
    {
        path: "/admin/expense",
        name: "admin.expense",
        component: ListExpense,
    },
    {
        path: "/admin/ledger",
        name: "admin.ledger",
        component: ListLedger,
    },
];
