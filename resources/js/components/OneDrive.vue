<template>
    <div class="explorer-container">
        <!-- Header -->
       <div class="explorer-header text-center text-white py-4 animate-fade-in">
            <h2 class="fw-bold m-0 d-flex align-items-center justify-content-center">
                <img src="/storage/image/onedrvie.png" alt="OneDrive" width="50" height="40" class="me-2" />
                My OneDrive
            </h2>

            <p class="mb-0 opacity-75">Manage your files with ease</p>
        </div>

        <!-- Toolbar -->
        <div
           class="toolbar d-flex flex-wrap gap-2 justify-content-between align-items-center p-3 text-white shadow animate-slide-up">
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-primary" @click="goHome">🏠 Home</button>

                <!-- Breadcrumbs -->
               <nav aria-label="breadcrumb text-white" class="ms-2">
                    <ol class="breadcrumb mb-0">
                       <li class="breadcrumb-item" :class="{ active: pathSegments.length === 0 }">
                            <a href="#" @click.prevent="goHome" v-if="pathSegments.length">root</a>
                            <span v-else>root</span>
                        </li>
                       <li v-for="(seg, idx) in pathSegments" :key="idx" class="breadcrumb-item"
                            :class="{ active: idx === pathSegments.length - 1 }">
                            <a v-if="idx !== pathSegments.length - 1" href="#" @click.prevent="goToCrumb(idx)">
                                {{ seg }}
                            </a>
                            <span v-else>{{ seg }}</span>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Right tools -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
               <div>
                    <input v-model="query" type="text" class="form-control" placeholder=" 🔍 Search files & folders…"
                        style="width: 25rem;" />
                </div>

              <div class="custom-file-upload" style="display: inline-block; position: relative;">
                    <!-- Hidden file input -->
                    <input type="file" id="fileInput" multiple @change="uploadFiles" style="display: none;" />

                  <!-- Custom button -->
                    <label for="fileInput" class="btn btn-primary"
                        style="width: 150px; text-align: center; cursor: pointer;">
                        <i class="fa-solid fa-upload"></i> Uploads
                    </label>
                </div>
                <button class="btn btn-success" @click="createFolderPrompt">
                   <i class="fa-solid fa-plus"></i> New Folder
                </button>
               <a href="/auth/onedrive" class="btn btn-danger reconnect-btn">
                    <i class="fas fa-sync-alt"></i> Connect / Reconnect
                </a>
            </div>
        </div>

        <!-- Empty / Error states -->
       <div v-if="!loading && filteredFiles.length === 0" class="empty-state text-white-50 text-center py-5">
            <i class="bi bi-inboxes fs-1 d-block mb-2"></i>
            <div class="fs-5">No items found here.</div>
        </div>

        <!-- Grid -->
        <div class="row g-3">
           <div v-for="item in filteredFiles" :key="item.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="file-card shadow-sm p-3 rounded text-center animate-pop" @dblclick="openItem(item)">
                    <div class="file-icon-wrap mb-2">
                        <!-- ✅ Hamesha icon hi dikhana hai -->
                        <i :class="fileIcon(item)" class="fs-1"></i>
                    </div>

                  <div class="file-name text-truncate fw-semibold" :title="item.name">
                        {{ item.name }}
                    </div>

                  <div class="text-muted small" v-if="!item.folder && humanSize(item.size)">
                        {{ humanSize(item.size) }}
                    </div>

                    <!-- Actions -->
                   <div class="file-actions mt-3 d-flex justify-content-center gap-2">
                       <button class="btn btn-sm btn-outline-success" title="Open / Preview"
                            @click.stop="preview(item)">
                            <i class="bi bi-eye"></i>
                        </button>
                       <button class="btn btn-sm btn-outline-primary" title="Download" @click.stop="download(item)">
                            <i class="bi bi-arrow-down-circle"></i>
                        </button>
                       <button class="btn btn-sm btn-outline-danger" title="Delete" @click.stop="deleteItem(item)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="loading-overlay">
            <div class="spinner-border" role="status" />
            <div class="mt-2 fw-semibold">Loading…</div>
        </div>

        <!-- Toast -->
        <div v-if="toast.show" class="toast-wrap">
            <div class="toast-inner" :class="toast.type">
                {{ toast.message }}
            </div>
        </div>

        <!-- Preview Modal -->
       <div v-if="showPreview" class="preview-backdrop" @click.self="closePreview">
            <div class="preview-modal">
               <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold text-truncate">
                        {{ previewItem?.name }}
                    </div>
                   <button class="btn btn-sm btn-outline-secondary" @click="closePreview">
                        ✕
                    </button>
                </div>
                <!-- Use webUrl for embedded preview (Office online / image render) -->
               <iframe v-if="previewUrl" :src="previewUrl" class="preview-frame" referrerpolicy="no-referrer"></iframe>
                <div v-else class="text-center py-4 text-muted">
                    Preview not available. Try Download.
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "OneDrive",
    data() {
        return {
            files: [],
            loading: false,
            error: "",
            query: "",
            currentPath: "/", // Graph path
            showPreview: false,
            previewItem: null,
            previewUrl: "",
            toast: { show: false, type: "ok", message: "" },
            API_BASE: "http://localhost:8000/api/onedrive",
            isConnected: false,
        };
    },
    computed: {
        pathSegments() {
            if (!this.currentPath || this.currentPath === "/") return [];
            return this.currentPath.split("/").filter(Boolean);
        },
        filteredFiles() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.files;
            return this.files.filter((x) =>
                (x.name || "").toLowerCase().includes(q)
            );
        },
    },
    mounted() {
        this.fetchFiles("/");
    },
    methods: {
        // ------------ UI helpers ------------
        connectOneDrive() {
            window.location.href = "/auth/onedrive";
        },

        toastOk(msg) {
            this.toastShow(msg, "ok");
        },
        toastErr(msg) {
            this.toastShow(msg, "err");
        },
        toastShow(message, type = "ok") {
            this.toast = { show: true, message, type };
            setTimeout(() => (this.toast.show = false), 2200);
        },
        humanSize(bytes) {
            if (!bytes && bytes !== 0) return "";
            const sizes = ["B", "KB", "MB", "GB", "TB"];
            const i =
                bytes === 0 ? 0 : Math.floor(Math.log(bytes) / Math.log(1024));
            return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`;
        },
        fileIcon(item) {
            if (item.folder) return "bi bi-folder-fill text-warning";
            const name = (item.name || "").toLowerCase();
            if (/\.(jpg|jpeg|png|gif|webp|bmp|tiff)$/i.test(name))
                return "bi bi-file-image text-info";
            if (name.endsWith(".pdf")) return "bi bi-file-pdf text-danger";
            if (/\.(doc|docx)$/i.test(name))
                return "bi bi-file-word text-primary";
            if (/\.(xls|xlsx)$/i.test(name))
                return "bi bi-file-excel text-success";
            if (/\.(ppt|pptx)$/i.test(name))
                return "bi bi-file-ppt text-warning";
            if (/\.(txt|md|json|xml|yml|yaml|csv|log)$/i.test(name))
                return "bi bi-file-text text-secondary";
            if (/\.(js|ts|vue|php|py|rb|java|c|cpp|cs|go|rs)$/i.test(name))
                return "bi bi-file-code text-secondary";
            if (/\.(mp4|mkv|mov|avi|webm)$/i.test(name))
                return "bi bi-file-play text-muted";
            if (/\.(mp3|wav|m4a|flac)$/i.test(name))
                return "bi bi-file-music text-muted";
            return "bi bi-file-earmark text-secondary";
        },

        // ------------ Path helpers ------------
        normalizeFromParent(parentPath) {
            if (!parentPath) return "/";
            const idx = parentPath.indexOf("/drive/root:");
            if (idx === -1) return "/";
            const sub =
                parentPath.substring(idx + "/drive/root:".length) || "/";
            return sub === "" ? "/" : sub;
        },
        goHome() {
            this.currentPath = "/";
            this.fetchFiles("/");
        },
        goToCrumb(idx) {
            const segs = this.pathSegments.slice(0, idx + 1);
            const to = "/" + segs.join("/");
            this.currentPath = to || "/";
            this.fetchFiles(this.currentPath);
        },

        isImage(item) {
            if (!item.name) return false;
            return /\.(jpg|jpeg|png|gif|webp|bmp|tiff)$/i.test(item.name);
        },

        // ------------ API calls ------------
        async fetchFiles(path = "/") {
            try {
                this.loading = true;
                // ✅ FIX: use /files (not /list)
                const res = await axios.get(`${this.API_BASE}/files`, {
                    params: { path },
                });
                this.files = res.data.value || [];
                this.currentPath = path;
            } catch (error) {
                console.error(
                    "files error",
                    error?.response?.data || error.message
                );
                this.toastErr("Failed to load files");
            } finally {
                this.loading = false;
            }
        },

        async uploadFiles(event) {
            try {
                const files = Array.from(event.target.files || []);
                if (!files.length) return;
                this.loading = true;

                await Promise.all(
                    files.map(async (f) => {
                        const form = new FormData();
                        form.append("file", f);
                        form.append("path", this.currentPath || "/");
                        // ✅ FIX: keep old /upload route
                        await axios.post(`${this.API_BASE}/upload`, form, {
                            headers: { "Content-Type": "multipart/form-data" },
                        });
                    })
                );

                this.toastOk("Upload complete");
                this.fetchFiles(this.currentPath);
                event.target.value = ""; // reset
            } catch (e) {
                console.error(e);
                this.toastErr("Upload failed");
            } finally {
                this.loading = false;
            }
        },

        async createFolderPrompt() {
            const name = prompt("Enter folder name:");
            if (!name) return;
            try {
                this.loading = true;
                // ✅ FIX: use /folder (not /create-folder)
                await axios.post(`${this.API_BASE}/folder`, {
                    name,
                    path: this.currentPath || "/",
                });
                this.toastOk("Folder created");
                this.fetchFiles(this.currentPath);
            } catch (e) {
                console.error(e);
                this.toastErr("Failed to create folder");
            } finally {
                this.loading = false;
            }
        },

        async download(item) {
            window.location.href = `${this.API_BASE}/download/${item.id}`;
        },

        async deleteItem(item) {
            if (!confirm(`Delete "${item.name}"?`)) return;
            try {
                this.loading = true;
                await axios.delete(`${this.API_BASE}/delete/${item.id}`);
                this.toastOk("Deleted");
                this.fetchFiles(this.currentPath);
            } catch (e) {
                console.error(e);
                this.toastErr("Delete failed");
            } finally {
                this.loading = false;
            }
        },

        openItem(item) {
            if (item.folder) {
                const parent = this.normalizeFromParent(
                    item.parentReference?.path || "/"
                );
                const next = this.joinPath(parent, item.name);
                this.fetchFiles(next);
            } else {
                this.preview(item);
            }
        },

        // ------------ Preview ------------
        preview(item) {
            if (item.webUrl) {
                this.previewItem = item;
                this.previewUrl = item.webUrl;
                this.showPreview = true;
            } else {
                window.open(`${this.API_BASE}/download/${item.id}`, "_blank");
            }
        },
        closePreview() {
            this.showPreview = false;
            this.previewItem = null;
            this.previewUrl = "";
        },

        // ------------ utils ------------
        joinPath(base, name) {
            if (!base || base === "/") return `/${name}`;
            return `${base.replace(/\/+$/, "")}/${name}`;
        },
    },
};
</script>

<style scoped>
/* Page bg */
.explorer-container {
    min-height: 100vh;
    padding: 20px;
}

/* Header glass */
.explorer-header {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    margin-bottom: 1rem;
}

/* Toolbar */
.toolbar {
    color: rgba(255, 255, 255, 0.85);
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    box-shadow: none;
    padding: 10px 15px;
    margin-bottom: 1rem;
}

.toolbar:hover {
    background: rgba(255, 255, 255, 0.05);
}

.toolbar input {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

.toolbar input::placeholder {
    color: rgba(255, 255, 255, 0.589);
}

/* Breadcrumbs */
.breadcrumb {
    background: transparent;
}

.breadcrumb-item a {
    color: #fff !important;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.7) !important;
}

.breadcrumb-item+.breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.5);
}

/* File cards */
.file-card {
    border-radius: 14px;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: pointer;
    border: 1px solid #f1f3f7;
}

.file-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(22, 34, 68, 0.12);
}

.file-icon-wrap i {
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.1));
}

.file-name {
    font-size: 14px;
    white-space: nowrap;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(16, 22, 48, 0.22);
    backdrop-filter: blur(2px);
    z-index: 50;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Toast */
.toast-wrap {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 60;
}

.toast-inner {
    padding: 10px 14px;
    border-radius: 10px;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
}

.toast-inner.ok {
    background: #22bb66;
}

.toast-inner.err {
    background: #e05666;
}

/* Preview modal */
.preview-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(7, 10, 22, 0.55);
    display: grid;
    place-items: center;
    z-index: 70;
    padding: 24px;
}

.preview-modal {
    width: min(1100px, 96vw);
    height: min(80vh, 900px);
    background: #fff;
    border-radius: 14px;
    padding: 12px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    display: flex;
    flex-direction: column;
}

.preview-frame {
    flex: 1;
    width: 100%;
    border: 0;
    border-radius: 10px;
}

.thumb-preview {
    max-width: 64px;
    max-height: 64px;
    border-radius: 8px;
    object-fit: cover;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Animations */
.animate-fade-in {
    animation: fadeIn 0.5s ease both;
}

.animate-slide-up {
    animation: slideUp 0.4s ease both;
}

.animate-pop {
    animation: pop 0.25s ease both;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes pop {
    from {
        opacity: 0;
        transform: scale(0.98);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
