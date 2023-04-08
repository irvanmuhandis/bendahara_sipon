import Dashboard from "./components/Dashboard.vue";
import AppointmentForm from "./pages/appointments/AppointmentForm.vue";
import UpdateSetting from "./pages/settings/UpdateSetting.vue";
import UpdateProfile from "./pages/profile/UpdateProfile.vue";

import CreatePayBill from "./pages/pay/PayBill.vue";
import CreatePayDebt from "./pages/pay/PayDebt.vue";
import CreateBill from "./pages/bill/CreateBill.vue";

import UserList from "./pages/list/UserList.vue";
import ListIncident from "./pages/list/ListIncident.vue";
import ListAppointments from "./pages/list/ListAppointments.vue";
import ListDispen from "./pages/list/ListDispen.vue";
import ListGroup from "./pages/list/ListGroup.vue";
import ListPay from "./pages/list/ListPay.vue";
import ListBill from "./pages/list/ListBill.vue";
import ListBigBook from "./pages/list/ListBigBook.vue";
import ListWallet from "./pages/list/ListWallet.vue";
import ListTrans from "./pages/list/ListTrans.vue";

export default [
    {
        path: "/admin/dashboard",
        name: "admin.dashboard",
        component: Dashboard,
    },

    {
        path: "/admin/appointments",
        name: "admin.appointments",
        component: ListAppointments,
    },
    {
        path: "/admin/appointments/create",
        name: "admin.appointments.create",
        component: AppointmentForm,
    },
    {
        path: "/admin/users",
        name: "admin.users",
        component: UserList,
    },

    {
        path: "/admin/dispens",
        name: "admin.dispens",
        component: ListDispen,
    },

    {
        path: "/admin/incident",
        name: "admin.incident",
        component: ListIncident,
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
        path: "/admin/pay",
        name: "admin.pay",
        component: ListPay,
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
        path: "/admin/wallet",
        name: "admin.wallet",
        component: ListWallet,
    },
    {
        path: "/admin/trans",
        name: "admin.trans",
        component: ListTrans,
    },
    {
        path: "/admin/bigbook",
        name: "admin.bigbook",
        component: ListBigBook,
    },
    {
        path: "/admin/setting",
        name: "admin.setting",
        component: UpdateSetting,
    },
    {
        path: "/admin/profile",
        name: "admin.profile",
        component: UpdateProfile,
    },
];
