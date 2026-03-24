<script>
import axios from "axios";

export default {
    data() {
        return {
            files: [],
            q: "",
            loading: false,
            folderStack: [],
        };
    },
    computed: {
        // Search filter logic
        filtered() {
            const q = this.q.trim().toLowerCase();
            if (!q) return this.files;
            return this.files.filter((f) =>
                (f.name || "").toLowerCase().includes(q)
            );
        },
    },
    methods: {
        // Root options load karna
        async loadRootOptions() {
            this.files = [
                { id: "mydrive", name: "My Files", isFolder: true },
                { id: "shared", name: "Shared with Me", isFolder: true },
            ];
        },

        // API se folder contents lana
        async fetchFiles(apiUrl, pushStack = true) {
            this.loading = true;
            try {
                const { data } = await axios.get(apiUrl);
                if (pushStack) this.folderStack.push(apiUrl);
                this.files = data;
            } finally {
                this.loading = false;
            }
        },

        // Item open karna
        openItem(f) {
            if (f.isFolder) {
                if (f.id === "mydrive") {
                    this.fetchFiles("/google/drive/root");
                } else if (f.id === "shared") {
                    this.fetchFiles("/google/drive/shared");
                } else {
                    this.fetchFiles(`/google/drive/folder/${f.id}`);
                }
            } else {
                window.open(f.webViewLink, "_blank"); // Preview open kare
            }
        },

        // Back navigation
        goBack() {
            if (this.folderStack.length > 1) {
                this.folderStack.pop();
                const prevUrl = this.folderStack[this.folderStack.length - 1];
                this.fetchFiles(prevUrl, false);
            } else {
                this.folderStack = [];
                this.loadRootOptions();
            }
        },

        // Font Awesome icon ka naam return kare
        getFileIcon(f) {
            if (f.isFolder) return "fas fa-folder text-warning";

            const ext = (f.name || "").split(".").pop().toLowerCase();
            const extIcons = {
                pdf: "fas fa-file-pdf text-danger",
                doc: "fas fa-file-word text-primary",
                docx: "fas fa-file-word text-primary",
                xls: "fas fa-file-excel text-success",
                xlsx: "fas fa-file-excel text-success",
                ppt: "fas fa-file-powerpoint text-warning",
                pptx: "fas fa-file-powerpoint text-warning",
                jpg: "fas fa-file-image text-info",
                jpeg: "fas fa-file-image text-info",
                png: "fas fa-file-image text-info",
                gif: "fas fa-file-image text-info",
                txt: "fas fa-file-alt text-secondary",
                zip: "fas fa-file-archive text-muted",
                rar: "fas fa-file-archive text-muted",
            };
            return extIcons[ext] || "fas fa-file text-dark";
        },
    },
    mounted() {
        this.loadRootOptions();
    },
};
</script>

<template>
    <div class="container mt-4">
        <!-- Header -->
       <div class="d-flex align-items-center justify-content-between mb-3 text-white">
            <h4>
                <i class="fab fa-google-drive text-success"></i> Google Drive
                Browser
            </h4>
           <a href="/auth/google" class="btn btn-danger reconnect-btn">
               <i class="fas fa-sync-alt"></i> Connect / Reconnect
            </a>
        </div>

        <!-- Search & Back -->
       <div class="mb-3 d-flex text-white">
           <button v-if="folderStack.length > 0" @click="goBack" class="btn btn-secondary btn-sm me-2">
                <i class="fas fa-arrow-left"></i> Back
            </button>
           <input v-model="q" class="form-control inputs text-white" placeholder="🔍 Search by name..." />
        </div>

        <!-- Files Grid -->
        <div class="row g-3">
           <div v-for="(f, i) in filtered" :key="f.id" class="col-6 col-md-3">
               <div class="card h-100 p-3 text-center shadow-sm border-0 " style="cursor: pointer; border-radius: 10px"
                    @dblclick="openItem(f)">
                    <!-- Icon -->
                    <i :class="getFileIcon(f)" style="font-size: 40px"></i>
                    <!-- Name -->
                    <div class="mt-2 small text-truncate fw-semibold">
                        {{ f.name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="text-center py-4">
            <i class="fas fa-spinner fa-spin"></i> Loading…
        </div>
    </div>
</template>

<style scoped>
.inputs {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

.inputs::placeholder {
    color: white;
}

.card {
    background: rgba(255, 255, 255, 0.05);
    /* glass effect */
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #fff;
    transition: all 0.25s ease;
}

/* Hover effect */
.card:hover {
    transform: translateY(-5px) scale(1.02);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

/* Icon styling */
.card i {
    color: #38bdf8;
    /* soft blue accent */
}

/* Text */
.card .small {
    color: rgba(255, 255, 255, 0.85);
}

.reconnect-btn {
    transition: all 0.25s ease;
}

.reconnect-btn:hover {
    transform: translateY(-1px) scale(1.02);
}
</style>