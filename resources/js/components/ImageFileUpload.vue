<script>
import axios from "axios";

export default {
    name: "UsersPage",
    data() {
        return {
            name: "",
            email: "",
            city: "",
            image: null,
            previewImage: null,
            users: [],
            links: [],
            editingUserId: null,
            loading: false, // loader for all CRUD
            validationErrors: {},
        };
    },

    computed: {
        imagePreviewUrl() {
            console.log("Computed chal rahi hai...");
            if (!this.previewImage) return null;
            if (this.previewImage instanceof File) {
                return URL.createObjectURL(this.previewImage);
            }
            return `http://127.0.0.1:8000/storage/${this.previewImage}`;
        },
    },

    mounted() {
        this.getUsers();
    },

    methods: {
        async getUsers(url = "http://127.0.0.1:8000/api/users") {
            this.loading = true;
            try {
                const res = await axios.get(url);
                this.users = res.data.data;
                console.log(this.users);
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
                this.previewImage = file; // File object store
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
                await axios.post("http://127.0.0.1:8000/api/users", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                await this.getUsers();
                this.resetForm();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    // Ye line errors console mein dikhayegi
                    console.error(
                        "Validation Errors:",
                        error.response.data.errors
                    );

                    // Aap alert mein ya UI mein dikhana chahte hain to:
                    alert(JSON.stringify(error.response.data.errors));

                    // Ya agar aap errors ko form ke neeche dikhana chahte hain,
                    // to aap errors ko data property mein store karke template mein show kar sakte hain
                    this.validationErrors = error.response.data.errors;
                } else {
                    console.error(error);
                }
            } finally {
                this.loading = false;
            }
        },

        async deleteUser(id) {
            if (!confirm("Are you sure?")) return;
            this.loading = true;
            try {
                await axios.delete(`http://127.0.0.1:8000/api/users/${id}`);
                await this.getUsers();
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        startEdit(user) {
            console.log(user);
            this.editingUserId = user.id;
            this.name = user.name;
            this.email = user.email;
            this.city = user.city;
            this.previewImage = user.image_path;
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
                    `http://127.0.0.1:8000/api/users/${this.editingUserId}?_method=PUT`,
                    formData,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
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
        resetForm() {
            this.name = "";
            this.email = "";
            this.city = "";
            this.image = null;
            this.previewImage = null;
            this.editingUserId = null;
        },
    },
};
</script>

<template>
    <div class="container mt-4 position-relative">
        <!-- Full Page Loader -->
        <div v-if="loading" class="loader-overlay">
            <div
                class="spinner-border text-light"
                style="width: 3rem; height: 3rem"
                role="status"
            >
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <h2 class="mb-4 text-center">Users Management</h2>

        <!-- User Form -->
        <div class="card shadow-sm mb-4 ">
            <div class="card-header bg-primary text-white">
                {{ editingUserId ? "Edit User" : "Add New User" }}
            </div>
            <div class="card-body card">
                <form
                    @submit.prevent="
                        editingUserId ? updateUser() : createUser()
                    "
                    enctype="multipart/form-data"
                >
                    <div class="row g-3">
                        <!-- Name Filed -->
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input
                                type="text"
                                v-model="name"
                                class="form-control inputs"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div v-if="validationErrors.name" class="text-danger">
                            <div
                                v-for="err in validationErrors.name"
                                :key="err"
                            >
                                {{ err }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                v-model="email"
                                class="form-control inputs"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div v-if="validationErrors.email" class="text-danger">
                            <div
                                v-for="err in validationErrors.email"
                                :key="err"
                            >
                                {{ err }}
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input
                                type="text"
                                v-model="city"
                                class="form-control inputs"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div v-if="validationErrors.city" class="text-danger">
                            <div
                                v-for="err in validationErrors.city"
                                :key="err"
                            >
                                {{ err }}
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input
                                type="file"
                                @change="onImageChange"
                                class="form-control inputs"
                                :required="!editingUserId"
                                :disabled="loading"
                            />
                            <div v-if="previewImage" class="mt-2">
                                <img
                                    :src="imagePreviewUrl"
                                    alt="Preview"
                                    class="img-thumbnail"
                                    style="max-width: 150px"
                                />
                            </div>
                        </div>
                        <div v-if="validationErrors.image" class="text-danger">
                            <div
                                v-for="err in validationErrors.image"
                                :key="err"
                            >
                                {{ err }}
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-4">
                        <button
                            type="submit"
                            class="btn"
                            :class="
                                editingUserId ? 'btn-warning' : 'btn-success'
                            "
                            :disabled="loading"
                        >
                            <i
                                :class="
                                    editingUserId
                                        ? 'fas fa-save'
                                        : 'fas fa-plus'
                                "
                            ></i>
                            {{ editingUserId ? "Update" : "Create" }}
                        </button>
                        <button
                            v-if="editingUserId"
                            type="button"
                            class="btn btn-secondary ms-2"
                            @click="resetForm"
                            :disabled="loading"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Users List</div>
            <div class="card-body p-0">
                <table
                    class="table table-striped table-hover mb-0"
                    v-if="users.length"
                >
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>{{ user.id }}</td>
                            <td>
                                <img
                                    :src="`http://127.0.0.1:8000/storage/${user.image_path}`"
                                    alt="User"
                                    class="rounded"
                                    style="
                                        width: 50px;
                                        height: 50px;
                                        object-fit: cover;
                                    "
                                />
                            </td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.city }}</td>
                            <td>
                                <button
                                    class="btn btn-primary btn-sm me-1"
                                    @click="startEdit(user)"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    class="btn btn-danger btn-sm"
                                    @click="deleteUser(user.id)"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-center py-4">No users found.</p>
            </div>

            <!-- Pagination -->
            <!-- <div v-if="links.length" class="card-footer text-center">
                <ul class="pagination justify-content-center mb-0">
                    <li
                        v-for="link in links"
                        :key="link.url"
                        :class="[
                            'page-item',
                            { active: link.active, disabled: !link.url },
                        ]"
                    >
                        <button
                            class="page-link"
                            v-html="link.label"
                            @click="link.url && getUsers(link.url)"
                            :disabled="loading || !link.url"
                        ></button>
                    </li>
                </ul>
            </div> -->

        </div>

        <!-- Pagination -->
        <div v-if="links.length" class="d-flex justify-content-center align-items-center my-4">
            <button v-for="(link, index) in links" :key="index" class="px-4 py-2 rounded border mx-1"
                :disabled="!link.url" :class="{
                    'btn btn-primary text-white': link.active,
                    'btn btn-secondary text-white': !link.active && link.url,
                    'btn btn-light text-gray': !link.url,
                }" @click="link.url && getUsers(link.url)" v-html="link.label"></button>
        </div>
    </div>
</template>

<style scoped>
.card {
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    color: white;
}

.inputs {
 background: rgba(255, 255, 255, 0.08);
 color: white;
}

.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
