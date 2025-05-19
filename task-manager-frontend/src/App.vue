<script setup>
import Navbar from './components/Navbar.vue'
</script>

<template>
    <div>
        <Navbar />
        <main class="container">
            <div class="main-container">
                <div class="card shadow-sm">
                    <router-view />
                </div>
            </div>
        </main>
    </div>
</template>
