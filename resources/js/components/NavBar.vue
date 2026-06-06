<script>
export default {
    name: "Navbar",
    data() {
        return {
            isNavbarOpen: false,
        };
    },
    methods: {
        toggleNavbar() {
            this.isNavbarOpen = !this.isNavbarOpen;
        },
    },
};
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
           <router-link class="navbar-brand-glow" to="/home">
                <img src="/images/icon.png" alt="Logo" class="brand-logo" />
                <span class="brand-text">LaraVue Portal</span>
            </router-link>

            <button class="navbar-toggler custom-toggler" type="button" @click="toggleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

          <div class="collapse navbar-collapse" :class="{ show: isNavbarOpen }" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                       <router-link class="nav-link-custom" to="/home" active-class="active-link">
                           <i class="fa-solid fa-house me-1"></i> Home
                       </router-link>
                    </li>

                    <li class="nav-item">
                       <router-link class="nav-link-custom" to="/posts" active-class="active-link">
                           <i class="fa-solid fa-file-invoice me-1"></i> Posts
                       </router-link>
                    </li>
                    <li class="nav-item">
                       <router-link class="nav-link-custom" to="/uploads" active-class="active-link">
                           <i class="fa-solid fa-users-gear me-1"></i> Profiles
                       </router-link>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.custom-navbar {
    background: rgba(11, 17, 30, 0.65);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 0.8rem 0;
    position: sticky;
    top: 0;
    z-index: 1030;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
}

.navbar-brand-glow {
    display: flex;
    align-items: center;
    text-decoration: none;
    gap: 10px;
    transition: all 0.3s ease;
}

.navbar-brand-glow:hover {
    opacity: 0.95;
    transform: scale(1.02);
}

.brand-logo {
    height: 32px;
    filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.5));
}

.brand-text {
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, #a855f7, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 4px rgba(99, 102, 241, 0.15));
}

.nav-link-custom {
    color: rgba(241, 245, 249, 0.75) !important;
    font-weight: 500;
    font-size: 0.95rem;
    padding: 0.5rem 1rem !important;
    margin: 0 0.25rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
}

.nav-link-custom:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.05);
}

.active-link {
    color: #ffffff !important;
    background: rgba(99, 102, 241, 0.15) !important;
    border: 1px solid rgba(99, 102, 241, 0.25);
    box-shadow: 0 0 12px rgba(99, 102, 241, 0.1);
}

.custom-toggler {
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 0.35rem 0.65rem;
    border-radius: 8px;
}

.custom-toggler:focus {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}

@media (max-width: 991px) {
    .navbar-collapse {
        background: rgba(11, 17, 30, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .nav-link-custom {
        width: 100%;
        margin: 0.25rem 0;
        padding: 0.65rem 1rem !important;
    }
}
</style>