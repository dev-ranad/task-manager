<template>
  <header
    class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom bg-white fixed-top shadow-sm"
  >
    <router-link
      to="/"
      class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none"
    >
      <svg class="bi me-2" width="40" height="32">
        <use xlink:href="#bootstrap" />
      </svg>
      <span class="fs-4">Task Manager</span>
    </router-link>

    <ul class="nav nav-pills">
      <li class="nav-item">
        <button
          v-if="route.path == '/' && auth.token"
          @click="goProfile"
          class="nav-link active"
        >
          Profile
        </button>
        <router-link v-else to="/" class="btn">Home</router-link>
      </li>
      <li v-if="auth.token" class="nav-item">
        <router-link to="/admin/task" class="nav-link mr-2">Tasks</router-link>
      </li>
      <li v-if="auth.token" class="nav-item">
        <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
        <button v-else @click="logout" class="btn ml-2">Logout</button>
      </li>
      <li v-if="!auth.token">
        <router-link to="/login" class="nav-link">Login</router-link>
      </li>
      <li v-if="!auth.token">
        <router-link to="/register" class="nav-link mr-2">Register</router-link>
      </li>
    </ul>
  </header>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "../store/auth";
import { useRoute, useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const loading = ref(false);

const logout = async () => {
  loading.value = true;
  await auth.logout(router);
  loading.value = false;
};
const goProfile = () => {
  if (auth.token) {
    router.push("admin");
  }
};
</script>
