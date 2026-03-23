<script>
import axios from "axios";

export default {
    name: "PostPage",
    data() {
        return {
            title: "",
            content: "",
            posts: [],
            links: [],
            editingPostId: null, // null means not editing
            loading: false, // Loader flag
        };
    },
    mounted() {
        this.getPosts();
    },
    methods: {
        async getPosts(url = "http://127.0.0.1:8000/api/posts") {
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
                alert("Title aur Content required hain!");
                return;
            }
            this.loading = true;
            try {
                const response = await axios.post(
                    "http://127.0.0.1:8000/api/posts",
                    {
                        title: this.title,
                        content: this.content,
                    }
                );
                this.posts.unshift(response.data);
                this.title = "";
                this.content = "";
            } catch (error) {
                console.error(error);
                alert("Post create karte waqt error aaya.");
            } finally {
                this.loading = false;
            }
        },

        async deletePost(id) {
            if (!confirm("Are you sure?")) return;
            this.loading = true;
            try {
                await axios.delete(`http://127.0.0.1:8000/api/posts/${id}`);
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
        },

        async updatePost() {
            this.loading = true;
            try {
                const res = await axios.put(
                    `http://127.0.0.1:8000/api/posts/${this.editingPostId}`,
                    {
                        title: this.title,
                        content: this.content,
                    }
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

<style scoped>
.card {
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
}

.inputs {
 background: rgba(255, 255, 255, 0.08);
 border: 1px solid rgba(255,255,255,0.1);
 color: white;
}

.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>

<template>
    <div class="container mt-4">
        <!-- Loader -->
        <div v-if="loading" class="loader-overlay">
            <div
                class="spinner-border text-primary"
                style="width: 3rem; height: 3rem"
            >
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Post Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                {{ editingPostId ? "Edit Post" : "Create New Post" }}
            </div>
            <div class="card-body">
                <form
                    @submit.prevent="
                        editingPostId ? updatePost() : createPost()
                    "
                >
                    <div class="mb-3">
                        <label class="form-label">Post Title</label>
                        <input
                            type="text"
                            class="form-control inputs"
                            v-model="title"
                            required
                        />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea
                            class="form-control inputs"
                            v-model="content"
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    <button
                        class="btn"
                        :class="editingPostId ? 'btn-warning' : 'btn-success'"
                    >
                        <i
                            :class="
                                editingPostId ? 'fas fa-save' : 'fas fa-plus'
                            "
                        ></i>
                        {{ editingPostId ? "Update" : "Create" }}
                    </button>
                    <button
                        v-if="editingPostId"
                        type="button"
                        class="btn btn-secondary ms-2"
                        @click="resetForm"
                    >
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <!-- Posts List -->
        <div v-if="posts.length">
            <div
                v-for="post in posts"
                :key="post.id"
                class="card mb-3 shadow-sm"
            >
                <div class="card-body">
                    <h5 class="card-title">{{ post.title }}</h5>
                    <p class="card-text">{{ post.content }}</p>
                    <button
                        class="btn btn-primary btn-sm me-2"
                        @click="startEdit(post)"
                    >
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button
                        class="btn btn-danger btn-sm"
                        @click="deletePost(post.id)"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-muted">No posts yet.</div>

        <!-- Pagination -->
        <div
            v-if="links.length"
            class="d-flex justify-content-center align-items-center my-5"
        >
            <button
                v-for="(link, index) in links"
                :key="index"
                class="px-4 py-2 rounded border mx-1"
                :disabled="!link.url"
                :class="{
                    'btn btn-primary text-white': link.active,
                    'btn btn-secondary text-white': !link.active && link.url,
                    'btn btn-light text-gray': !link.url,
                }"
                @click="link.url && getPosts(link.url)"
                v-html="link.label"
            ></button>
        </div>
    </div>
</template>
