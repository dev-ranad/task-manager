import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from "./router/index.js";
import { createPinia } from "pinia";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

const app = createApp(App);

app.component("multiselect", Multiselect);
app.use(createPinia());
app.use(router);
app.mount("#app");
