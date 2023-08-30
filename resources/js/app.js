import "./bootstrap";

import "admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js";
import "admin-lte/dist/js/adminlte.min.js";

// import "datatables.net-bs4";
// import "jszip";
// import "pdfmake";
// import "datatables.net-buttons-bs4/js/buttons.bootstrap4.min.mjs";
// import "datatables.net-buttons/js/buttons.colVis.min.mjs";
// import "datatables.net-buttons/js/buttons.flash.min.js";
// import "datatables.net-buttons/js/buttons.html5.min.mjs";
// import "datatables.net-buttons/js/buttons.print.min.mjs";
// import "datatables.net-buttons/js/dataTables.buttons.min.mjs";

// import "datatables.net-bs4/css/dataTables.bootstrap4.min.css";
// import "datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css";
import VueMultiselect from "vue-multiselect";

import { createApp } from "vue/dist/vue.esm-bundler.js";
import { createRouter, createWebHistory } from "vue-router";
import Routes from "./routes.js";
import axios from "axios";




const app = createApp({});

const router = createRouter({
    routes: Routes,
    history: createWebHistory(),
});

// app.config.globalProperties.fetchData = fetchData;
app.use(router);
app.component("VueMultiselect", VueMultiselect);
app.mount("#app");
