<script>
export default {
    name: "HomePage",
    methods: {
        goTo(route) {
            this.$router.push(route);
        }
    }
};
</script>

<template>
    <div class="home-wrapper">

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-badge">
                <i class="fa-solid fa-layer-group me-2"></i>Laravel + Vue.js
            </div>
            <h1 class="hero-title">
                Welcome to <span class="gradient-text">LaraVue Portal</span>
            </h1>
            <p class="hero-subtitle">
                A full-stack content &amp; profile management workspace powered by Laravel 12 and Vue.js 3.
                Manage posts, user profiles, and media — all in one elegant hub.
            </p>
        </div>

        <!-- Feature Cards Grid -->
        <div class="modules-grid">

            <div class="module-card" @click="goTo('/posts')">
                <div class="module-icon posts-icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div class="module-info">
                    <h3>Post Management</h3>
                    <p>Create, edit, update and delete posts through a full REST API CRUD workflow.</p>
                </div>
                <div class="module-arrow">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <div class="module-card" @click="goTo('/uploads')">
                <div class="module-icon profiles-icon">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div class="module-info">
                    <h3>Profile Management</h3>
                    <p>Add and manage user profiles with names, emails, cities, and profile image uploads.</p>
                </div>
                <div class="module-arrow">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

        </div>

        <!-- Tech Stack Badges -->
        <div class="stack-section">
            <p class="stack-label">Built With</p>
            <div class="stack-badges">
                <span class="badge-pill badge-laravel"><i class="fa-brands fa-laravel me-1"></i>Laravel 12</span>
                <span class="badge-pill badge-vue"><i class="fa-brands fa-vuejs me-1"></i>Vue.js 3</span>
                <span class="badge-pill badge-bootstrap"><i class="fa-brands fa-bootstrap me-1"></i>Bootstrap 5</span>
                <span class="badge-pill badge-mysql"><i class="fa-solid fa-database me-1"></i>MySQL</span>
                <span class="badge-pill badge-axios"><i class="fa-solid fa-bolt me-1"></i>Axios</span>
            </div>
        </div>

    </div>
</template>

<style scoped>
.home-wrapper {
    min-height: calc(100vh - 64px);
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3.5rem;
}

/* Hero */
.hero-section {
    text-align: center;
    max-width: 700px;
    animation: fadeInUp 0.7s ease both;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: #a5b4fc;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.4rem 1rem;
    border-radius: 100px;
    margin-bottom: 1.5rem;
    letter-spacing: 0.5px;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    color: #f8fafc;
    line-height: 1.2;
    margin-bottom: 1.2rem;
}

.gradient-text {
    background: linear-gradient(135deg, #a855f7, #6366f1, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: rgba(241, 245, 249, 0.6);
    line-height: 1.7;
    max-width: 580px;
    margin: 0 auto;
}

/* Module Cards */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    width: 100%;
    max-width: 900px;
    animation: fadeInUp 0.8s ease 0.15s both;
}

.module-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.module-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(99, 102, 241, 0.03) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.module-card:hover {
    transform: translateY(-6px);
    border-color: rgba(99, 102, 241, 0.3);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(99, 102, 241, 0.15);
}

.module-card:hover::before {
    opacity: 1;
}

.module-card:hover .module-arrow {
    opacity: 1;
    transform: translateX(0);
}

.module-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}

.posts-icon {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.08));
    border: 1px solid rgba(99, 102, 241, 0.25);
    color: #818cf8;
}

.profiles-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.08));
    border: 1px solid rgba(16, 185, 129, 0.25);
    color: #34d399;
}

.module-info {
    flex: 1;
}

.module-info h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #f8fafc;
    margin-bottom: 0.4rem;
}

.module-info p {
    font-size: 0.87rem;
    color: rgba(241, 245, 249, 0.5);
    line-height: 1.55;
    margin: 0;
}

.module-arrow {
    color: rgba(99, 102, 241, 0.8);
    font-size: 1.1rem;
    opacity: 0;
    transform: translateX(-6px);
    transition: all 0.3s ease;
}

/* Stack Badges */
.stack-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    animation: fadeInUp 0.9s ease 0.3s both;
}

.stack-label {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(241, 245, 249, 0.35);
    margin: 0;
}

.stack-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    justify-content: center;
}

.badge-pill {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.9rem;
    border-radius: 100px;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.3px;
}

.badge-laravel  { background: rgba(255, 45, 32, 0.15);  border: 1px solid rgba(255, 45, 32, 0.3);  color: #ff6b6b; }
.badge-vue      { background: rgba(79, 192, 141, 0.15); border: 1px solid rgba(79, 192, 141, 0.3); color: #4fc08d; }
.badge-bootstrap { background: rgba(86, 61, 124, 0.15); border: 1px solid rgba(86, 61, 124, 0.3); color: #b597f6; }
.badge-mysql    { background: rgba(0, 115, 183, 0.15);  border: 1px solid rgba(0, 115, 183, 0.3);  color: #60b4e8; }
.badge-axios    { background: rgba(244, 196, 0, 0.1);   border: 1px solid rgba(244, 196, 0, 0.25); color: #f5cc40; }

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .modules-grid {
        grid-template-columns: 1fr;
    }

    .hero-title {
        font-size: 2rem;
    }

    .home-wrapper {
        padding: 2rem 1.25rem;
    }
}
</style>