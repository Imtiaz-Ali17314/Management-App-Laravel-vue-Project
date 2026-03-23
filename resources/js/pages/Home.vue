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
    <div class="dashboard">

        <div class="card" @click="goTo('/posts')">
            <div class="icon">📝</div>
            <h3>Post Management</h3>
        </div>

        <div class="card" @click="goTo('/uploads')">
            <!-- <div class="icon">👤</div> -->
             <i class="fa-solid fa-users icon"></i>
            <h3>User Management</h3>
        </div>

        <div class="card" @click="goTo('/gdrive')">
            <!-- <div class="icon">☁️</div> -->
            <!-- <i class="fab fa-google-drive text-success icon"></i> -->
             <i class="fa-brands fa-google-drive icon"></i>
            <h3>Google Drive</h3>
        </div>

        <div class="card" @click="goTo('/odrive')">
            <!-- <div class="icon">📁</div> -->
               <img
                    src="/storage/image/onedrvie.png"
                    alt="OneDrive"
                    width="60"
                    height="50"
                    class="me-2 icon"
                />
            <h3>One Drive</h3>
        </div>

    </div>
</template>

<style scoped>
/* Remove scroll completely */
.dashboard {
    height: calc(100vh - 56px);
    overflow: hidden;

    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, 1fr);

    gap: 15px;
    padding: 15px;

    background: linear-gradient(135deg, #1e293b, #0f172a);
}

/* Compact Card */
.card {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 15px;
    color: white;
    cursor: pointer;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    transition: 0.3s ease;
    border: 1px solid rgba(255,255,255,0.1);
}

/* Smaller icon */
.icon {
    font-size: 40px;
    margin-bottom: 8px;
}

/* Smaller text */
.card h3 {
    font-size: 16px;
}

/* Hover */
.card:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

/* Mobile */
@media (max-width: 768px) {
    .dashboard {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(4, 1fr);
    }
}
</style>