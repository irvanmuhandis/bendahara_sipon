

import Dashboard from "./components/Dashboard.vue";

import CreatePayBill from "./pages/pay/PayBill.vue";
import CreatePayDebt from "./pages/pay/PayDebt.vue";
import CreateBill from "./pages/bill/CreateBill.vue";
import CreateGroup from "./pages/group/CreateGroup.vue";
import CreateDebt from "./pages/debts/CreateDebt.vue";
import CreateDispen from "./pages/dispen/CreateDispen.vue";

import ListAccount from "./pages/list/ListAccount.vue";
import ListDispen from "./pages/list/ListDispen.vue";
import ListGroup from "./pages/list/ListGroup.vue";
import ListIncome from "./pages/list/ListIncome.vue";
import ListWallet from "./pages/list/ListWallet.vue";
import ListExpense from "./pages/list/ListExpense.vue";

import ListDebt from "./pages/list/ListDebt.vue";
import ListBill from "./pages/list/ListBill.vue";

import ArrearReport from "./pages/report/ArrearsReport.vue";
import MoneyReport from "./pages/report/MoneyReport.vue";

import DetailLedger from "./pages/ledger/DetailLedger.vue";

export default [
  {
        path: "/",
        name: "admin.dashboard",
        component: Dashboard,
    },
    {
        path: "/admin/ledger/:id",
        name: "admin.ledger.detail",
        component: DetailLedger,
    },
    {
        path: "/admin/report/money",
        name: "admin.money",
        component: MoneyReport,
    },
    {
        path: "/admin/report/arrear",
        name: "admin.arrear",
        component: ArrearReport,
    },

    {
        path: "/admin/dispens",
        name: "admin.dispens",
        component: ListDispen,
    },
    {
        path: "/admin/dispens/create",
        name: "admin.dispens.create",
        component: CreateDispen,
    },

    {
        path: "/admin/master/wallet",
        name: "admin.wallet",
        component: ListWallet,
    },
    {
        path: "/admin/master/account",
        name: "admin.account",
        component: ListAccount,
    },
    {
        path: "/admin/master/group",
        name: "admin.group",
        component: ListGroup,
    },
    {
        path: "/admin/master/group/create",
        name: "admin.group.create",
        component: CreateGroup,
    },



    {
        path: "/admin/billing/bill",
        name: "admin.bill",
        component: ListBill,
    },
    {
        path: "/admin/billing/debt",
        name: "admin.debt",
        component: ListDebt,
    },
    {
        path: "/admin/billing/bill/create",
        name: "admin.bill.create",
        component: CreateBill,
    },
    {
        path: "/admin/billing/debt/create",
        name: "admin.debt.create",
        component: CreateDebt,
    },





    {
        path: "/admin/income",
        name: "admin.income",
        component: ListIncome,
    },
    {
        path: "/admin/income/create-debt",
        name: "admin.income.create-debt",
        component: CreatePayDebt,
    },
    {
        path: "/admin/income/create-bill",
        name: "admin.income.create-bill",
        component: CreatePayBill,
    },
    {
        path: "/admin/expense",
        name: "admin.expense",
        component: ListExpense,
    },
];


