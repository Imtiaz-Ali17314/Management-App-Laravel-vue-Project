// router.js
import { createRouter, createWebHistory } from "vue-router";

import Home from "./pages/Home.vue";
import Post from "./pages/Post.vue";
import ImageFileUpload from "./components/ImageFileUpload.vue";

const routes = [
    { path: "/", redirect: { name: "HomePage" } },
    { path: "/home", name: "HomePage", component: Home },
    { path: "/posts", name: "PostPage", component: Post },
    { path: "/uploads", name: "ImageFileUpload", component: ImageFileUpload },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
