<template>
  <form
    @submit.prevent="submit"
    enctype="multipart/form-data"
    class="p-4 bg-white rounded shadow-sm"
  >
    <div class="mb-3 text-center" v-if="attachment_url">
      <img :src="attachment_url" width="200" alt="Preview" class="img-thumbnail" />
    </div>

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input
        type="text"
        v-model="form.title"
        class="form-control"
        placeholder="Enter task title"
      />
      <div v-if="errors?.title" class="text-danger small mt-1">
        <div v-for="error in errors.title" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea
        v-model="form.description"
        class="form-control"
        rows="5"
        placeholder="Write task details"
      ></textarea>
      <div v-if="errors?.description" class="text-danger small mt-1">
        <div v-for="error in errors.description" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Task Category</label>
      <multiselect
        v-model="form.categories"
        :options="task?.categories"
        :multiple="true"
        :close-on-select="false"
        :clear-on-select="false"
        :preserve-search="true"
        placeholder="Select categories"
        label="name"
        track-by="id"
      />
      <div v-if="errors?.categories" class="text-danger small mt-1">
        <div v-for="error in errors.categories" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Assign To</label>
      <multiselect
        v-model="form.users"
        :options="task?.users"
        :multiple="true"
        :close-on-select="false"
        :clear-on-select="false"
        :preserve-search="true"
        placeholder="Select users"
        label="name"
        track-by="id"
      />
      <div v-if="errors?.users" class="text-danger small mt-1">
        <div v-for="error in errors.users" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Priority</label>
      <select v-model="form.priority" class="form-select">
        <option disabled value="">Select priority</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
      </select>
      <div v-if="errors?.priority" class="text-danger small mt-1">
        <div v-for="error in errors.priority" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select v-model="form.status" class="form-select">
        <option disabled value="">Select status</option>
        <option value="pending">Pending</option>
        <option value="in-progress">In Progress</option>
        <option value="hold">Hold</option>
        <option value="review">Review</option>
        <option value="cancel">Cancel</option>
        <option value="completed">Completed</option>
      </select>
      <div v-if="errors?.status" class="text-danger small mt-1">
        <div v-for="error in errors.status" :key="error">{{ error }}</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Image (optional)</label>
      <input type="file" class="form-control" @change="handleFile" />
      <div v-if="errors?.image" class="text-danger small mt-1">
        <div v-for="error in errors.image" :key="error">{{ error }}</div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
      {{ isEdit ? "Update" : "Create" }} Task
    </button>
  </form>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useTaskStore } from "@/store/task";

const props = defineProps({
  form: Object,
  errors: Object,
  isEdit: Boolean,
  submit: Function,
});

const attachment_url = ref(null);

const task = useTaskStore();

onMounted(() => {
  task.loadCategories();
  task.loadUsers();
});

watch(
  () => props.form.image,
  (newVal) => {
    if (newVal && typeof newVal === "string") {
      attachment_url.value = newVal;
    }
  },
  { immediate: true }
);

function handleFile(event) {
  const file = event.target.files[0];
  if (file) {
    props.form.image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      attachment_url.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}
</script>
