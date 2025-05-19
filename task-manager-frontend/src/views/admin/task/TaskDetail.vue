<template>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Details Task</h2>
            <div>
                <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
                <router-link v-else to="/admin/task" class="btn btn-primary ms-2">
                    Task List
                </router-link>
            </div>
            </div>
            <div v-if="details" :key="details.id">
                <div class="row">
                    <div class="col-12">
                        <h2 class="text-3xl font-bold">{{ details.title }}</h2>
                        <small class="text-gray mb-4">{{ details.author.name ? 'By ' + details.author.name : ''
                            }}</small>
                        <div>
                            <span class="badge rounded-pill bg-info" v-for="category in details?.categories"
                                :key="category.id">
                                {{ category.name }}
                            </span>
                            <span class="badge rounded-pill bg-light text-black" v-for="user in details?.users"
                                :key="user.id">
                                {{ user.name }}
                            </span>
                        </div>
                        <hr>
                    </div>
                </div>


                <p class="mb-8">{{ details.description }}</p>
                <img v-if="details?.image" :src="details?.image" width="200" class="rounded max-h-96 mb-4" />
            </div>
            <div v-else>Loading...</div>
        </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '@/store/auth'
import { useTaskStore } from '@/store/task'

const route = useRoute()
const loading = ref(false)
const auth = useAuthStore()
const task = useTaskStore()
const details = reactive({
    title: '',
    content: '',
    image: '',
    categories: [],
    comments: [],
    author: {}
})
onMounted(async () => {
    loadAndUpdateTask();
})


const loadAndUpdateTask = async () => {
    loading.value = true;
    await task.loadTask(route.params.id);
    if (task.task) {
        details.title = task.task.title;
        details.description = task.task.description;
        details.priority = task.task.priority;
        details.status = task.task.status;
        details.categories = task.task.categories;
        details.users = task.task.users;
        details.image = task.task.attachments[0]?.url || null;
        details.comments = task.task.comments;
        details.author = task.task.author;
    } else {
        alert('Task not found or failed to load.');
    }
    loading.value = false;
};

</script>

