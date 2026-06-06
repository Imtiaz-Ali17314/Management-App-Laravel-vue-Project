# 🗄️ LaraVue Portal

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-ff2d20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue.js-3.x-4fc08d?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Bootstrap-5.x-7952b3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/MySQL-Supported-00758f?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License">
</p>

<p align="center">
  <img src="public/images/screenshot-home.png" alt="LaraVue Portal - Home Page Preview" width="100%">
</p>

---

**LaraVue Portal** is a full-stack content and profile management workspace built with **Laravel 12** on the backend and **Vue.js 3** on the frontend. This project demonstrates practical, real-world application of the Laravel + Vue ecosystem — covering RESTful APIs, Eloquent ORM, file storage, Vue Router, reactive components, Axios-based CRUD operations, and frontend/backend integration.

This is my first dedicated Laravel + Vue full-stack project after mastering Vue.js independently, serving as a crucial step up from frontend-only development toward building complete web applications.

🔗 **Repository:** [https://github.com/Imtiaz-Ali17314/LaraVue-Portal](https://github.com/Imtiaz-Ali17314/LaraVue-Portal)

---

## 🗺️ Journey Milestone & Context

> **"Knowing a frontend framework is powerful. Connecting it to a real backend is transformative."**

After completing my **Learn-Vue-Js-Practice-App** — which covered Vue fundamentals with a Laravel backend — I built **LaraVue Portal** as a more structured, purposeful full-stack application. Where the learning project was sandbox-focused, this one is feature-focused, demonstrating clean API design, multi-file form handling, image storage, and a full CRUD workflow for two different data domains.

This project marks my transition into **Professional Full-Stack development**, combining both ends of the application stack under a single cohesive Laravel project.

---

## ✨ Core Features

### 📝 Post Management (Full CRUD)
- Create, read, update, and delete posts through a **dedicated RESTful API**.
- Two-column UI: sticky form panel on the left, live post feed on the right.
- Paginated post listing with smooth navigation.
- Edit mode automatically populates the form and scrolls to the top.
- Inline loader overlays during API requests to prevent duplicate submissions.

### 👤 User Profile Management (Full CRUD + Image Upload)
- Create and manage user profiles storing **name, email, city, and profile image**.
- Profile image uploads handled via `multipart/form-data` sent to a Laravel storage endpoint.
- Uploaded images are stored under `public/storage/image/` using Laravel's **local disk** with symbolic link.
- Old images are **automatically deleted** from disk when a user updates their profile picture.
- Server-side **validation** (422 responses) is caught and displayed in the form as inline field errors.
- Profiles are rendered as a **card grid** with gradient-bordered circular avatars.
- Graceful image fallback via `ui-avatars.com` if the storage image is missing.

### 🎨 Premium UI Design
- Dark glassmorphism design with `backdrop-filter: blur()` panels throughout.
- Deep space gradient background with subtle radial color blobs.
- **Outfit** typeface from Google Fonts for a modern, premium aesthetic.
- Smooth hover animations, glowing buttons, and active link indicators.
- Sticky glassmorphic navbar with gradient brand title.
- Custom scrollbars, animated page loaders, and responsive card layouts.

---

## 📂 Project Architecture

```
File-Manager-Laravel-vue-Project/
│
├── app/Http/Controllers/
│   ├── PostController.php      # REST API for Posts (CRUD)
│   └── UserController.php      # REST API for Users (CRUD + Image Upload/Delete)
│
├── resources/js/
│   ├── App.vue                 # Root component with router-view & page transitions
│   ├── router.js               # Vue Router route definitions
│   ├── app.js                  # App entry point (Vue + Router mount)
│   ├── pages/
│   │   ├── Home.vue            # Dashboard with hero section & module cards
│   │   └── Post.vue            # Post Management page (two-column CRUD layout)
│   └── components/
│       ├── NavBar.vue          # Sticky glassmorphic navigation bar
│       └── ImageFileUpload.vue # User Profile Management with card grid
│
├── resources/sass/app.scss     # Global SCSS: dark theme, glassmorphism, buttons, scrollbars
├── resources/views/welcome.blade.php  # SPA entry blade file
├── routes/api.php              # API routes: /posts, /users
├── routes/web.php              # SPA catch-all route → serves Vue
├── database/migrations/        # Users and Posts table migrations
└── webpack.mix.js              # Laravel Mix: Vue + SASS compilation
```

---

## 🛠️ Technology Stack

| Layer | Technology | Purpose |
|:--|:--|:--|
| **Backend Framework** | Laravel 12 | Application logic, routing, validation |
| **API Style** | RESTful API (`apiResource`) | CRUD endpoint definitions |
| **ORM** | Eloquent ORM | Database models & queries |
| **File Storage** | Laravel `Storage::disk('public')` | Profile image upload & deletion |
| **Frontend Framework** | Vue.js 3 (Options API) | Reactive SPA components |
| **HTTP Client** | Axios | Frontend-to-API communication |
| **Routing** | Vue Router 4 | Client-side page navigation |
| **CSS Framework** | Bootstrap 5 + Custom SCSS | Grid, layout, utility classes |
| **Typography** | Outfit (Google Fonts) | Premium font across the app |
| **Icons** | Font Awesome 6 + Bootstrap Icons | UI icons throughout the app |
| **Database** | MySQL | Persistent storage for users and posts |

---

## ⚙️ Setup & Installation

### Prerequisites
Ensure the following are installed on your system:
- **PHP** (v8.2+)
- **Composer**
- **Node.js** (v16+)
- **MySQL**
- **Git**

---

### 1. Clone the Repository

```bash
git clone https://github.com/Imtiaz-Ali17314/LaraVue-Portal.git
cd LaraVue-Portal
```

---

### 2. Install PHP Dependencies

```bash
composer install
```

---

### 3. Configure Environment

```bash
copy .env.example .env
php artisan key:generate
```

Edit the `.env` file and configure your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_vue
DB_USERNAME=root
DB_PASSWORD=
```

---

### 4. Run Database Migrations

```bash
php artisan migrate
```

---

### 5. Create Storage Symbolic Link

```bash
php artisan storage:link
```

This allows uploaded profile images to be publicly accessible via `public/storage/`.

---

### 6. Install Node Dependencies & Compile Assets

```bash
npm install
npm run dev
```

---

### 7. Start the Laravel Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser to access **LaraVue Portal**.

---

## 🔑 Key Technical Decisions

- **Dynamic API Origin:** All frontend API calls use `window.location.origin` instead of hardcoded `localhost` URLs, making the app portable across different environments.
- **Image Fallback Strategy:** When a profile image fails to load (e.g., after a server reset), the avatar gracefully falls back to a generated avatar from [ui-avatars.com](https://ui-avatars.com).
- **Sticky Form Panel:** The user profile form panel stays fixed in view (`position: sticky`) as you scroll through the profile card grid below, keeping the creation flow accessible at all times.
- **Responsive Grid:** The profile card grid uses `auto-fill` with `minmax()` columns, adapting naturally from 1 to 3+ columns depending on screen width.

---

## 📄 License

This project is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
