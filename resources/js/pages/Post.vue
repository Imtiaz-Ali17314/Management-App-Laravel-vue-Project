<script>
import axios from "axios";

const API_BASE = `${window.location.origin}/api`;

export default {
    name: "PostPage",
    data() {
        return {
            title: "",
            content: "",
            posts: [],
            links: [],
            editingPostId: null,
            loading: false,
        };
    },
    mounted() {
        this.getPosts();
    },
    methods: {
        async getPosts(url = `${API_BASE}/posts`) {
            this.loading = true;
            try {
                const res = await axios.get(url);
                this.posts = res.data.data;
                this.links = res.data.links;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async createPost() {
            if (!this.title || !this.content) {
                alert("Title and Content are required!");
                return;
            }
            this.loading = true;
            try {
                const response = await axios.post(`${API_BASE}/posts`, {
                    title: this.title,
                    content: this.content,
                });
                this.posts.unshift(response.data);
                this.title = "";
                this.content = "";
            } catch (error) {
                console.error(error);
                alert("An error occurred while creating the post.");
            } finally {
                this.loading = false;
            }
        },

        async deletePost(id) {
            if (!confirm("Are you sure you want to delete this post?")) return;
            this.loading = true;
            try {
                await axios.delete(`${API_BASE}/posts/${id}`);
                this.posts = this.posts.filter((p) => p.id !== id);
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        startEdit(post) {
            this.editingPostId = post.id;
            this.title = post.title;
            this.content = post.content;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },

        async updatePost() {
            this.loading = true;
            try {
                const res = await axios.put(
                    `${API_BASE}/posts/${this.editingPostId}`,
                    { title: this.title, content: this.content }
                );
                const index = this.posts.findIndex(
                    (p) => p.id === this.editingPostId
                );
                if (index !== -1) {
                    this.posts[index] = res.data;
                }
                this.resetForm();
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.title = "";
            this.content = "";
            this.editingPostId = null;
        },
    },
};
</script>

<template>
    <div class="post-page-wrapper">

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
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <h1 class="page-title">Post Management</h1>
                <p class="page-subtitle">Create, edit, and manage your content posts via REST API.</p>
            </div>
        </div>

        <div class="post-layout">

            <!-- Left Panel: Form -->
            <div class="form-panel">
                <div class="glass-panel">
                    <div class="panel-header" :class="editingPostId ? 'header-warning' : 'header-primary'">
                        <i :class="editingPostId ? 'fa-solid fa-pen-to-square' : 'fa-solid fa-plus-circle'" class="me-2"></i>
                        {{ editingPostId ? "Edit Post" : "Create New Post" }}
                    </div>
                    <div class="panel-body">
                        <form @submit.prevent="editingPostId ? updatePost() : createPost()">
                            <div class="field-group">
                                <label class="field-label">Post Title</label>
                                <input
                                    type="text"
                                    class="glass-field"
                                    v-model="title"
                                    placeholder="Enter post title..."
                                    required
                                    :disabled="loading"
                                />
                            </div>
                            <div class="field-group">
                                <label class="field-label">Content</label>
                                <textarea
                                    class="glass-field"
                                    v-model="content"
                                    rows="5"
                                    placeholder="Write your post content here..."
                                    required
                                    :disabled="loading"
                                ></textarea>
                            </div>
                            <div class="form-actions">
                                <button
                                    type="submit"
                                    class="btn-action"
                                    :class="editingPostId ? 'btn-warning-glow' : 'btn-primary-glow'"
                                    :disabled="loading"
                                >
                                    <i :class="editingPostId ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-paper-plane'" class="me-2"></i>
                                    {{ editingPostId ? "Update Post" : "Publish Post" }}
                                </button>
                                <button
                                    v-if="editingPostId"
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

            <!-- Right Panel: Posts Feed -->
            <div class="feed-panel">

                <div v-if="posts.length" class="posts-feed">
                    <div
                        v-for="post in posts"
                        :key="post.id"
                        class="post-card"
                    >
                        <div class="post-card-body">
                            <div class="post-id-badge">#{{ post.id }}</div>
                            <h5 class="post-card-title">{{ post.title }}</h5>
                            <p class="post-card-content">{{ post.content }}</p>
                        </div>
                        <div class="post-card-footer">
                            <button
                                class="post-btn post-btn-edit"
                                @click="startEdit(post)"
                                :disabled="loading"
                            >
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </button>
                            <button
                                class="post-btn post-btn-delete"
                                @click="deletePost(post.id)"
                                :disabled="loading"
                            >
                                <i class="fa-solid fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <i class="fa-regular fa-file-lines empty-icon"></i>
                    <p>No posts yet. Create your first post!</p>
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
                        @click="link.url && getPosts(link.url)"
                        v-html="link.label"
                    ></button>
                </div>

            </div>

        </div>
    </div>
</template>

<style scoped>
.post-page-wrapper {
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
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(99, 102, 241, 0.08));
    border: 1px solid rgba(99, 102, 241, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #818cf8;
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

/* Two-Column Layout */
.post-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
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
}

.panel-header {
    padding: 1.2rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-primary {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.18), rgba(99, 102, 241, 0.05));
    color: #a5b4fc;
}

.header-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.05));
    color: #fbbf24;
}

.panel-body {
    padding: 1.5rem;
}

/* Fields */
.field-group {
    margin-bottom: 1.2rem;
}

.field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(241, 245, 249, 0.7);
    margin-bottom: 0.5rem;
    letter-spacing: 0.3px;
}

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
    resize: vertical;
}

.glass-field:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
    background: rgba(15, 23, 42, 0.7);
}

.glass-field::placeholder { color: rgba(255, 255, 255, 0.3); }
.glass-field:disabled { opacity: 0.5; cursor: not-allowed; }

/* Buttons */
.form-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
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

.btn-primary-glow {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: white;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
}
.btn-primary-glow:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
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

/* Feed */
.feed-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.posts-feed {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.post-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease both;
}

.post-card:hover {
    border-color: rgba(99, 102, 241, 0.25);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.post-card-body {
    padding: 1.2rem 1.4rem 0.8rem;
    position: relative;
}

.post-id-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(99, 102, 241, 0.7);
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.2);
    padding: 0.15rem 0.55rem;
    border-radius: 100px;
    margin-bottom: 0.55rem;
    letter-spacing: 0.5px;
}

.post-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #f8fafc;
    margin-bottom: 0.4rem;
}

.post-card-content {
    font-size: 0.87rem;
    color: rgba(241, 245, 249, 0.55);
    line-height: 1.6;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card-footer {
    display: flex;
    gap: 0.6rem;
    padding: 0.8rem 1.4rem;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    background: rgba(255, 255, 255, 0.02);
}

.post-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.9rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}
.post-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.post-btn-edit {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.25);
    color: #a5b4fc;
}
.post-btn-edit:hover:not(:disabled) { background: rgba(99, 102, 241, 0.25); color: #c7d2fe; }

.post-btn-delete {
    background: rgba(244, 63, 94, 0.12);
    border: 1px solid rgba(244, 63, 94, 0.22);
    color: #fca5a5;
}
.post-btn-delete:hover:not(:disabled) { background: rgba(244, 63, 94, 0.22); color: #fecaca; }

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
    border: 3px solid rgba(99, 102, 241, 0.2);
    border-top-color: #6366f1;
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
.page-btn-active { background: linear-gradient(135deg, #6366f1, #4f46e5) !important; border-color: transparent !important; color: white !important; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4); }
.page-btn-disabled { opacity: 0.3; cursor: not-allowed; }

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 900px) {
    .post-layout { grid-template-columns: 1fr; }
    .post-page-wrapper { padding: 1.5rem 1rem; }
}
</style>
