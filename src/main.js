import "../node_modules/bootstrap/dist/css/bootstrap.css";
import { createApp } from "vue";
//import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

const app = createApp(App);

app.use(router);

app.mount("#app");

import "../node_modules/bootstrap/dist/js/bootstrap.js"