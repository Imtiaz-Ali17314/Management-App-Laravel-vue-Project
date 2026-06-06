<script>
import axios from "axios";

export default {
    name: "UserProfilesPage",
    data() {
        return {
            apiBase: window.location.origin,
            name: "",
            email: "",
            city: "",
            image: null,
            previewImage: null,
            users: [],
            links: [],
            editingUserId: null,
            loading: false,
            validationErrors: {},
        };
    },

    computed: {
        imagePreviewUrl() {
            if (!this.previewImage) return null;
            if (this.previewImage instanceof File) {
                return URL.createObjectURL(this.previewImage);
            }
            return `${this.apiBase}/storage/${this.previewImage}`;
        },
        storageBase() {
            return `${this.apiBase}/storage`;
        }
    },

    mounted() {
        this.getUsers();
    },

    methods: {
        async getUsers(url = `${window.location.origin}/api/users`) {
            this.loading = true;
            try {
                const res = await axios.get(url);
                this.users = res.data.data;
                this.links = res.data.links;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        onImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.image = file;
                this.previewImage = file;
            }
        },

        async createUser() {
            this.loading = true;
            const formData = new FormData();
            formData.append("name", this.name);
            formData.append("email", this.email);
            formData.append("city", this.city);
            formData.append("image", this.image);

            try {
                await axios.post(`${this.apiBase}/api/users`, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                await this.getUsers();
                this.resetForm();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.validationErrors = error.response.data.errors;
                } else {
                    console.error(error);
                }
            } finally {
                this.loading = false;
            }
        },

        async deleteUser(id) {
            if (!confirm("Are you sure you want to delete this profile?")) return;
            this.loading = true;
            try {
                await axios.delete(`${this.apiBase}/api/users/${id}`);
                await this.getUsers();
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        startEdit(user) {
            this.editingUserId = user.id;
            this.name = user.name;
            this.email = user.email;
            this.city = user.city;
            this.previewImage = user.image_path;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },

        async updateUser() {
            this.loading = true;
            const formData = new FormData();
            formData.append("name", this.name);
            formData.append("email", this.email);
            formData.append("city", this.city);
            if (this.image) formData.append("image", this.image);

            try {
                await axios.post(
                    `${this.apiBase}/api/users/${this.editingUserId}?_method=PUT`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );
                await this.getUsers();
                this.resetForm();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.validationErrors = error.response.data.errors;
                }
            } finally {
                this.loading = false;
            }
        },

        avatarFallback(event, name) {
            event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=6366f1&color=fff&size=128`;
        },

        resetForm() {
            this.name = "";
            this.email = "";
            this.city = "";
            this.image = null;
            this.previewImage = null;
            this.editingUserId = null;
            this.validationErrors = {};
        },
    },
};
</script>

<template>
    <div class="profiles-wrapper">

        <!-- Full Page Loader -->
        <div v-if="loading" class="loader-overlay">
            <div class="loader-spinner">
                <div class="spinner-ring"></div>
                <p>Loading...</p>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-icon">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <h1 class="page-title">User Profiles</h1>
                <p class="page-subtitle">Manage user profiles with images, emails, and cities.</p>
            </div>
        </div>

        <div class="profiles-layout">

            <!-- Left: Form Panel -->
            <div class="form-col">
                <div class="glass-panel">
                    <div class="panel-header" :class="editingUserId ? 'header-warning' : 'header-green'">
                        <i :class="editingUserId ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'" class="me-2"></i>
                        {{ editingUserId ? "Edit Profile" : "Add New Profile" }}
                    </div>
                    <div class="panel-body">
                        <form
                            @submit.prevent="editingUserId ? updateUser() : createUser()"
                            enctype="multipart/form-data"
                        >
                            <!-- Name -->
                            <div class="field-group">
                                <label class="field-label">
                                    <i class="fa-solid fa-user me-1 text-muted-icon"></i>Full Name
                                </label>
                                <input type="text" v-model="name" class="glass-field" placeholder="e.g. Ali Hassan" required :disabled="loading" />
                                <div v-if="validationErrors.name" class="field-error">
                                    <span v-for="err in validationErrors.name" :key="err">{{ err }}</span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="field-group">
                                <label class="field-label">
                                    <i class="fa-solid fa-envelope me-1 text-muted-icon"></i>Email Address
                                </label>
                                <input type="email" v-model="email" class="glass-field" placeholder="e.g. ali@example.com" required :disabled="loading" />
                                <div v-if="validationErrors.email" class="field-error">
                                    <span v-for="err in validationErrors.email" :key="err">{{ err }}</span>
                                </div>
                            </div>

                            <!-- City -->
                            <div class="field-group">
                                <label class="field-label">
                                    <i class="fa-solid fa-city me-1 text-muted-icon"></i>City
                                </label>
                                <input type="text" v-model="city" class="glass-field" placeholder="e.g. Lahore" required :disabled="loading" />
                                <div v-if="validationErrors.city" class="field-error">
                                    <span v-for="err in validationErrors.city" :key="err">{{ err }}</span>
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <div class="field-group">
                                <label class="field-label">
                                    <i class="fa-solid fa-image me-1 text-muted-icon"></i>Profile Image
                                </label>
                                <input
                                    type="file"
                                    @change="onImageChange"
                                    class="glass-field file-input"
                                    :required="!editingUserId"
                                    :disabled="loading"
                                    accept="image/*"
                                />
                                <div v-if="previewImage" class="preview-wrap">
                                    <img :src="imagePreviewUrl" alt="Preview" class="avatar-preview" />
                                    <span class="preview-label">Preview</span>
                                </div>
                                <div v-if="validationErrors.image" class="field-error">
                                    <span v-for="err in validationErrors.image" :key="err">{{ err }}</span>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="form-actions">
                                <button
                                    type="submit"
                                    class="btn-action"
                                    :class="editingUserId ? 'btn-warning-glow' : 'btn-green-glow'"
                                    :disabled="loading"
                                >
                                    <i :class="editingUserId ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-user-plus'" class="me-2"></i>
                                    {{ editingUserId ? "Update Profile" : "Create Profile" }}
                                </button>
                                <button
                                    v-if="editingUserId"
                                    type="button"
                                    class="btn-action btn-ghost"
                                    @click="resetForm"
                                    :disabled="loading"
                                >
                                    <i class="fa-solid fa-xmark me-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Profile Cards Grid -->
            <div class="cards-col">

                <div v-if="users.length" class="profiles-grid">
                    <div v-for="user in users" :key="user.id" class="profile-card">

                        <!-- Avatar -->
                        <div class="avatar-ring">
                            <img
                                :src="`${storageBase}/${user.image_path}`"
                                :alt="user.name"
                                class="avatar-img"
                                @error="(e) => avatarFallback(e, user.name)"
                            />
                        </div>

                        <!-- User Info -->
                        <div class="profile-info">
                            <h4 class="profile-name">{{ user.name }}</h4>
                            <p class="profile-detail">
                                <i class="fa-solid fa-envelope me-1"></i>
                                {{ user.email }}
                            </p>
                            <p class="profile-detail">
                                <i class="fa-solid fa-location-dot me-1"></i>
                                {{ user.city }}
                            </p>
                        </div>

                        <!-- ID Badge -->
                        <div class="profile-id">#{{ user.id }}</div>

                        <!-- Actions -->
                        <div class="profile-actions">
                            <button class="profile-btn btn-edit" @click="startEdit(user)" :disabled="loading">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                            </button>
                            <button class="profile-btn btn-delete" @click="deleteUser(user.id)" :disabled="loading">
                                <i class="fa-solid fa-trash me-1"></i>Delete
                            </button>
                        </div>

                    </div>
                </div>

                <div v-else class="empty-state">
                    <i class="fa-solid fa-user-slash empty-icon"></i>
                    <p>No profiles yet. Add your first user!</p>
                </div>

                <!-- Pagination -->
                <div v-if="links.length" class="pagination-wrap">
                    <button
                        v-for="(link, index) in links"
                        :key="index"
                        class="page-btn"
                        :disabled="!link.url || loading"
                        :class="{
                            'page-btn-active': link.active,
                            'page-btn-disabled': !link.url,
                        }"
                        @click="link.url && getUsers(link.url)"
                        v-html="link.label"
                    ></button>
                </div>

            </div>

        </div>
    </div>
</template>

<style scoped>
.profiles-wrapper {
    min-height: calc(100vh - 64px);
    padding: 2.5rem 2rem;
    position: relative;
}

/* Page Header */
.page-header {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    margin-bottom: 2.5rem;
    animation: fadeInUp 0.5s ease both;
}

.page-header-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(16, 185, 129, 0.08));
    border: 1px solid rgba(16, 185, 129, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #34d399;
    flex-shrink: 0;
}

.page-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #f8fafc;
    margin: 0;
}

.page-subtitle {
    font-size: 0.9rem;
    color: rgba(241, 245, 249, 0.5);
    margin: 0.2rem 0 0;
}

/* Layout */
.profiles-layout {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 2rem;
    animation: fadeInUp 0.6s ease 0.1s both;
}

/* Glass Panel */
.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
    position: sticky;
    top: 84px;
}

.panel-header {
    padding: 1.2rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-green {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(16, 185, 129, 0.05));
    color: #34d399;
}

.header-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.05));
    color: #fbbf24;
}

.panel-body { padding: 1.5rem; }

/* Fields */
.field-group { margin-bottom: 1.15rem; }

.field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(241, 245, 249, 0.65);
    margin-bottom: 0.45rem;
}

.text-muted-icon { color: rgba(241, 245, 249, 0.3); }

.glass-field {
    width: 100%;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #f8fafc;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.95rem;
    transition: all 0.25s ease;
    outline: none;
}

.glass-field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
    background: rgba(15, 23, 42, 0.7);
}

.glass-field::placeholder { color: rgba(255, 255, 255, 0.3); }
.glass-field:disabled { opacity: 0.5; cursor: not-allowed; }
.file-input { cursor: pointer; }

.preview-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.75rem;
    padding: 0.65rem 1rem;
    background: rgba(16, 185, 129, 0.06);
    border: 1px solid rgba(16, 185, 129, 0.15);
    border-radius: 10px;
}

.avatar-preview {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(16, 185, 129, 0.4);
}

.preview-label {
    font-size: 0.82rem;
    color: rgba(52, 211, 153, 0.8);
    font-weight: 500;
}

.field-error {
    font-size: 0.8rem;
    color: #fca5a5;
    margin-top: 0.4rem;
}

/* Action Buttons */
.form-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    padding: 0.65rem 1.3rem;
    border: none;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-action:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-green-glow {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
}
.btn-green-glow:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
}

.btn-warning-glow {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
}
.btn-warning-glow:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
}

.btn-ghost {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
}
.btn-ghost:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.12);
    color: white;
    transform: translateY(-2px);
}

/* Profile Cards Grid */
.cards-col {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profiles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.25rem;
}

.profile-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 18px;
    padding: 1.5rem 1.25rem 1.1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.65rem;
    transition: all 0.35s ease;
    position: relative;
    overflow: hidden;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #10b981, #6366f1);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    border-color: rgba(16, 185, 129, 0.25);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
}

.profile-card:hover::before {
    opacity: 1;
}

.avatar-ring {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(135deg, #10b981, #6366f1);
    flex-shrink: 0;
}

.avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(11, 17, 30, 0.9);
}

.profile-info { width: 100%; }

.profile-name {
    font-size: 1rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.4rem;
}

.profile-detail {
    font-size: 0.78rem;
    color: rgba(241, 245, 249, 0.5);
    margin: 0 0 0.25rem;
    word-break: break-word;
}

.profile-id {
    font-size: 0.7rem;
    font-weight: 700;
    color: rgba(16, 185, 129, 0.6);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 0.15rem 0.55rem;
    border-radius: 100px;
}

.profile-actions {
    display: flex;
    gap: 0.5rem;
    width: 100%;
    margin-top: 0.5rem;
}

.profile-btn {
    flex: 1;
    padding: 0.45rem 0.5rem;
    border: none;
    border-radius: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.profile-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-edit {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.25);
    color: #a5b4fc;
}
.btn-edit:hover:not(:disabled) { background: rgba(99, 102, 241, 0.25); color: #c7d2fe; }

.btn-delete {
    background: rgba(244, 63, 94, 0.12);
    border: 1px solid rgba(244, 63, 94, 0.22);
    color: #fca5a5;
}
.btn-delete:hover:not(:disabled) { background: rgba(244, 63, 94, 0.22); color: #fecaca; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: rgba(241, 245, 249, 0.35);
    border: 1px dashed rgba(255, 255, 255, 0.08);
    border-radius: 18px;
}
.empty-icon { font-size: 3rem; display: block; margin-bottom: 1rem; }

/* Loader */
.loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(11, 17, 30, 0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.loader-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: rgba(241, 245, 249, 0.5);
    font-size: 0.9rem;
}

.spinner-ring {
    width: 48px;
    height: 48px;
    border: 3px solid rgba(16, 185, 129, 0.2);
    border-top-color: #10b981;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

/* Pagination */
.pagination-wrap {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding: 1rem 0;
}

.page-btn {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(241, 245, 249, 0.6);
    transition: all 0.25s ease;
}
.page-btn:hover:not(:disabled) { background: rgba(255, 255, 255, 0.12); color: white; }
.page-btn-active { background: linear-gradient(135deg, #10b981, #059669) !important; border-color: transparent !important; color: white !important; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); }
.page-btn-disabled { opacity: 0.3; cursor: not-allowed; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 900px) {
    .profiles-layout { grid-template-columns: 1fr; }
    .glass-panel { position: static; }
    .profiles-wrapper { padding: 1.5rem 1rem; }
}

@media (max-width: 480px) {
    .profiles-grid { grid-template-columns: 1fr 1fr; }
}
</style>
