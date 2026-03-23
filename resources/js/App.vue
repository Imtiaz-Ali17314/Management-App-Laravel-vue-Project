<script>
import NavBar from "./components/NavBar.vue";
export default {
    name: "App",
    components: {
        NavBar,
    },
};
</script>

<style>
#router-view {
    overflow-x: hidden;
    padding: 0;
}

.effect-enter-active,
.effect-leave-active {
    transition: 0.5s;
}

.effect-leave-to {
    opacity: 0;
}

.effect-enter-from {
    transform: translateX(-100%);
}
</style>

<template>
    <NavBar />

    <div id="router-view" class="app-bg">
        <router-view v-slot="{ Component, route }">
            <transition name="effect" mode="out-in">
                <component :is="Component" :key="route.fullPath"></component>
            </transition>
        </router-view>
    </div>
</template>
