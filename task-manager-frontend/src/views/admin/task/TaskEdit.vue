<template>
  <div class="card-body max-w-5xl">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Edit Task</h2>
      <div>
        <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
        <router-link v-else to="/admin/task" class="btn btn-primary ms-2">
          Task List
        </router-link>
      </div>
    </div>
    <TaskForm :form="form" :errors="errors" :submit="updateTask" :isEdit="true" />
  </div>
</template>

<script setup>
import { reactive, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import TaskForm from "@/components/TaskForm.vue";
import api from "@/api/axios";
import { useTaskStore } from "@/store/task";

const taskStore = useTaskStore();
const route = useRoute();
const router = useRouter();
const loading = ref(false);
const form = reactive({
  title: "",
  description: "",
  image: "",
  categories: [],
  users: [],
  priority: "Low",
  status: "pending",
});

const errors = reactive({});

const loadTask = async () => {
  loading.value = true;
  await taskStore.loadTask(route.params.id);

  const task = taskStore.task;
  if (task) {
    form.title = task.title;
    form.description = task.description;
    form.categories = task.categories || [];
    form.users = task.users || [];
    form.priority = task.priority;
    form.status = task.status;
    form.image = task.attachments?.[0]?.url || null;
  } else {
    alert("Failed to load task.");
  }

  loading.value = false;
};

const updateTask = async () => {
    try {
        loading.value = true;
        await taskStore.submitTask(form, route.params.id);
        router.push("/admin/task");
    } catch (error) {
        loading.value = false;
        console.log(error);
        Object.assign(errors, error?.response?.data?.errors);
        alert(
            error?.response?.data?.message ||
            "Something went wrong"
        );
    }
};

onMounted(loadTask);
</script>
