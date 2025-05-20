<template>
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Tasks</h2>
      <div>
        <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
        <router-link v-else to="/admin/task" class="btn btn-primary ms-2">
          Task List
        </router-link>
      </div>
    </div>

    <div class="kanban-scroll-wrapper overflow-auto border border-solid border-gray-400">
      <div class="d-flex gap-3 flex-nowrap">
        <div
          v-for="(tasks, status) in board"
          :key="status"
          class="kanban-column border-0 shadow-sm"
          @dragover.prevent
          @drop="onDrop($event, status)"
        >
          <div class="bg-primary text-white text-center p-3 mb-1">
            <strong>{{ getColumnTitle(status) }}</strong>
          </div>
          <div class="kanban-card">
            <div
              class="border border-solid border-gray-400 rounded p-3 mb-1 cursor-pointer"
              v-for="task in tasks"
              :key="task.id"
              draggable="true"
              @dragstart="onDragStart($event, task, status)"
            >
                <small>
                  {{ task.title }}
                </small>
            </div>
            <div v-if="tasks.length === 0" class="text-muted text-center mt-3">
              No tasks in this column.
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
const task = useTaskStore();
const loading = ref(false);
const board = computed(() => task?.kanban);
const draggedTask = ref(null);
const draggedFrom = ref(null);

const getColumnTitle = (status) => {
  switch (status) {
    case "pending":
      return "Pending";
    case "in-progress":
      return "In Progress";
    case "hold":
      return "On Hold";
    case "review":
      return "Review";
    case "cancel":
      return "Cancelled";
    case "completed":
      return "Completed";
    default:
      return status;
  }
};

const fetchTasks = async () => {
  try {
    loading.value = true;
    const params = {
      isKanban: true,
    };
    await task.loadTasks(params);
    loading.value = false;
  } catch (err) {
    loading.value = false;
    alert("Failed to fetch kanban tasks:", err);
  }
};

const onDragStart = (event, task, status) => {
  draggedTask.value = task;
  draggedFrom.value = status;
};

const onDrop = async (event, status) => {
  if (!draggedTask.value || !draggedFrom.value) return;

  const fromList = board.value[draggedFrom.value];
  const toList = board.value[status];
  const index = fromList.findIndex((t) => t.id === draggedTask.value.id);

  if (index > -1) {
    toList.push(draggedTask.value);
    fromList.splice(index, 1);

    try {
      await task.changeStatus(draggedTask.value.id, status);
    } catch (err) {
      alert("Failed to update task status:", err);
    }
  }

  draggedTask.value = null;
  draggedFrom.value = null;
};

onMounted(fetchTasks);
</script>
