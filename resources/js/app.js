import { createApp } from "vue";
import App from "./App.vue";

import "bootstrap/dist/css/bootstrap.min.css"; // ✅ CSS
import "bootstrap/dist/js/bootstrap.bundle.min.js"; //
import "./bootstrap.js";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/css/all.min.css";

import router from "./router.js";

import "./assets/main.css";

// Pehle app create karo
const app = createApp(App);

// Pehle router use karo
app.use(router);

// Phir mount karo
app.mount("#app");
