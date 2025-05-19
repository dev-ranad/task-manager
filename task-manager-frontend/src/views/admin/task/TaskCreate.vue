<template>
  <div class="card-body max-w-5xl">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Create Task</h2>
      <div>
        <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
        <router-link to="/admin/task" class="btn btn-primary ms-2">
          Task List
        </router-link>
      </div>
    </div>
    <TaskForm :form="form" :errors="errors" :submit="submitTask" />
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import TaskForm from "@/components/TaskForm.vue";
import { useTaskStore } from "@/store/task";
import { useRouter } from "vue-router";

const taskStore = useTaskStore();
const router = useRouter();
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
const loading = ref(false);

const submitTask = async () => {
  try {
    loading.value = true;
    await taskStore.submitTask(form);
    router.push("/admin/task");
  } catch (error) {
    loading.value = false;
    console.log(error);
    Object.assign(errors, error?.response?.data?.errors);
    alert(error?.response?.data?.message || "Something went wrong");
  }
};
</script>
