import "bootstrap/dist/css/bootstrap.css"
import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import App from "./App.vue";

//import Page1 from "./views/Page1";
//import Page2 from "./views/Page2";
//import Page3 from "./views/Page3";

const routes = [
    //{ path: "/", component: Page1 },
    //{ path: "/page2", component: Page2 },
    //{ path: "/page3", component: Page3 }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

const app = createApp(App);

app.use(router);
app.mount("#app");

import "bootstrap/dist/js/bootstrap.js"