<template>
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Tasks</h2>
      <div>
        <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
        <div v-else>
          <router-link to="/admin/task/kanban" class="btn btn-info">
            Kanban View
          </router-link>
          <router-link to="/admin/task/create" class="btn btn-primary ms-2">
            + New Task
          </router-link>
        </div>
      </div>
    </div>

    <div class="table-responsive bg-blue-200" style="min-height: 400px">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th scope="col">Title</th>
            <th scope="col">Assign To</th>
            <th scope="col">Category</th>
            <th scope="col">Priority</th>
            <th scope="col">Status</th>
            <th scope="col">Completed At</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in tasks" :key="item.id">
            <td>{{ item.title }}</td>
            <td>
              <span
                class="badge rounded-pill bg-light text-black"
                v-for="user in item?.users"
                :key="user.id"
              >
                {{ user.name }}
              </span>
            </td>
            <td>
              <span
                class="badge rounded-pill bg-info"
                v-for="category in item?.categories"
                :key="category.id"
              >
                {{ category.name }}
              </span>
            </td>
            <td>
              <span class="badge bg-primary" v-if="item.priority === 'Low'">Low</span>
              <span class="badge bg-warning" v-else-if="item.priority === 'Medium'"
                >Medium</span
              >
              <span class="badge bg-danger" v-else-if="item.priority === 'High'"
                >High</span
              >
            </td>
            <td @click="openStatusModal(item)" class="cursor-pointer">
              <span class="badge bg-primary" v-if="item.status === 'pending'"
                >Pending</span
              >
              <span class="badge bg-warning" v-else-if="item.status === 'in-progress'"
                >In Progress</span
              >
              <span class="badge bg-danger" v-else-if="item.status === 'hold'">Hold</span>
              <span class="badge bg-info" v-else-if="item.status === 'review'"
                >Review</span
              >
              <span class="badge bg-dark" v-else-if="item.status === 'cancel'"
                >Cancel</span
              >
              <span class="badge bg-success" v-else-if="item.status === 'completed'"
                >Completed</span
              >
            </td>
            <td>
              {{ item.completed_at ? formatDate(item.completed_at) : "-" }}
            </td>
            <td>
              <div class="dropdown me-1">
                <button
                  type="button"
                  class="btn btn-light dropdown-toggle"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="true"
                  aria-expanded="false"
                ></button>
                <ul class="dropdown-menu">
                  <li>
                    <router-link class="dropdown-item" :to="`/admin/task/${item.id}`">
                      Details
                    </router-link>
                  </li>
                  <li>
                    <router-link
                      class="dropdown-item"
                      :to="`/admin/task/${item.id}/edit`"
                    >
                      Edit
                    </router-link>
                  </li>
                  <li>
                    <a class="dropdown-item cursor-pointer" @click="deleteTask(item.id)">
                      Delete
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
          <tr v-if="tasks?.length === 0">
            <td colspan="7" class="text-center text-muted py-4">No tasks yet.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav v-if="meta?.last_page > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: meta?.current_page === 1 }">
          <a
            class="page-link"
            href="#"
            @click.prevent="changePage(meta?.current_page - 1)"
            >Previous</a
          >
        </li>

        <li
          class="page-item"
          v-for="page in meta?.last_page"
          :key="page"
          :class="{ active: meta?.current_page === page }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>

        <li
          class="page-item"
          :class="{ disabled: meta?.current_page === meta?.last_page }"
        >
          <a
            class="page-link"
            href="#"
            @click.prevent="changePage(meta?.current_page + 1)"
            >Next</a
          >
        </li>
      </ul>
    </nav>

    <div
      class="modal fade"
      id="statusModal"
      tabindex="-1"
      aria-labelledby="statusModalLabel"
      aria-hidden="true"
      ref="statusModalRef"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="statusModalLabel">Change Task Status</h5>
            <button
              type="button"
              class="btn-close"
              @click="hideModal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <select v-model="selectedStatus" class="form-select">
              <option disabled value="">Select new status</option>
              <option value="pending">Pending</option>
              <option value="in-progress">In Progress</option>
              <option value="hold">Hold</option>
              <option value="review">Review</option>
              <option value="cancel">Cancel</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="modal-footer">
            <span v-if="statusLoading" class="badge bg-primary me-2">Loading...</span>
            <div v-else>
              <button type="button" class="btn btn-secondary" @click="hideModal">
                Cancel
              </button>
              <button
                type="button"
                class="btn btn-primary ms-2"
                @click="submitStatusChange"
              >
                Change
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useTaskStore } from "@/store/task";
import { Modal } from "bootstrap";

const task = useTaskStore();
const loading = ref(false);
const statusLoading = ref(false);
const selectedStatus = ref("");
const selectedTask = ref(null);
const statusModalRef = ref(null);
let modalInstance = null;
const params = ref({ ...task.params });
const tasks = computed(() => task?.tasks?.results?.task);
const meta = computed(() => task?.tasks?.meta);

const deleteTask = async (id) => {
  if (confirm("Are you sure you want to delete this task?")) {
    task.deleteTask(id);
  }
};

const openStatusModal = (task) => {
  selectedStatus.value = task.status;
  selectedTask.value = task;
  modalInstance?.show();
};

const hideModal = () => {
  modalInstance?.hide();
};

const submitStatusChange = async () => {
  try {
    statusLoading.value = true;
    if (selectedTask.value && selectedStatus.value) {
      await task.changeStatus(selectedTask.value.id, selectedStatus.value);
    }
    statusLoading.value = false;
    hideModal();
  } catch (error) {
    statusLoading.value = false;
    alert(error?.response?.data?.message || "Something went wrong");
  }
};

const formatDate = (date) => {
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "numeric",
    minute: "numeric",
  };
  return new Date(date).toLocaleDateString("en-US", options);
};

const changePage = async (page) => {
  if (page < 1 || page > task.meta?.last_page) return;

  loading.value = true;
  params.value.page = page;
  await task.loadTasks(params.value);
  task.setParams(params.value);

  loading.value = false;
};
onMounted(async () => {
  loading.value = true;
  params.value = { ...task.params };

  await task.loadTasks(params.value);
  if (statusModalRef.value) {
    modalInstance = new Modal(statusModalRef.value);
  }
  loading.value = false;
});
</script>
