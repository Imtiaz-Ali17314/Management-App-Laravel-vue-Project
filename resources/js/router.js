// router.js
import { createRouter, createWebHistory } from "vue-router";

import Home from "./pages/Home.vue";
import About from "./pages/About.vue";
import Post from "./pages/Post.vue";
import ImageFileUpload from "./components/ImageFileUpload.vue";
import DriveFile from "./components/DriveFile.vue";
import OneDrive from "./components/OneDrive.vue";

const routes = [
    { path: "/", redirect: { name: "HomePage" } },
    { path: "/home", name: "HomePage", component: Home },
    { path: "/about", name: "AboutPage", component: About },
    { path: "/posts", name: "PostPage", component: Post },
    { path: "/uploads", name: "ImageFileUpload", component: ImageFileUpload },
    { path: "/gdrive", name: "DriveFiles", component: DriveFile },
    { path: "/odrive", name: "OneDrive", component: OneDrive },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
