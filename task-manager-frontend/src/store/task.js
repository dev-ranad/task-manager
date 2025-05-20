import { defineStore } from "pinia";
import api from "../api/axios";

export const useTaskStore = defineStore("task", {
    state: () => ({
        categories: [],
        users: [],
        tasks: [],
        task: null,
        params: {
            isKanban: false,
            perPage: 5,
            page: 1,
        },
        kanban: {
            pending: [],
            "in-progress": [],
            hold: [],
            review: [],
            cancel: [],
            completed: [],
        },
    }),
    actions: {
        setParams(params) {
            this.params = params;
        },
        async loadUsers() {
            this.users = [];
            await api
                .get("/users")
                .then((res) => {
                    this.users = res?.data?.data?.results?.user;
                    console.log(this.users);
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
        async loadCategories() {
            this.categories = [];
            await api
                .get("/categories")
                .then((res) => {
                    this.categories = res?.data?.data?.results?.category;
                    console.log(this.categories);
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
        async loadTasks(params = {}) {
            await api
                .get("/tasks", { params: params })
                .then((res) => {
                    if (params.isKanban) {
                        console.log(res);
                        if (res?.data?.data?.results?.task) {
                            Object.entries(res?.data?.data?.results?.task).forEach(([status, tasks]) => {
                                if (this.kanban[status]) {
                                    this.kanban[status] = tasks;
                                }
                            });
                        }
                    } else {
                        console.log(res);
                        this.tasks = res?.data?.data;
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
        async loadTask(id) {
            this.task = null;
            await api
                .get(`/tasks/${id}`)
                .then((res) => {
                    this.task = res?.data?.data;
                    console.log(this.task);
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
        async submitTask(data, id = null) {
            try {
                const formData = new FormData();

                formData.append("title", data.title);
                formData.append("description", data.description);
                formData.append("priority", data.priority);
                formData.append("status", data.status);

                data.categories?.forEach((cat) => {
                    formData.append("categories[]", cat.id);
                });

                data.users?.forEach((user) => {
                    formData.append("users[]", user.id);
                });

                const currentAttachment = this.task?.attachments?.[0];
                if (data.image && typeof data.image !== "string") {
                    formData.append("image", data.image);
                    if (currentAttachment?.id) {
                        formData.append(
                            "deleteAttachmentIds[]",
                            currentAttachment.id
                        );
                    }
                }

                const url = id ? `/tasks/${id}` : "/tasks";
                if (id) {
                    formData.append("_method", "PUT");
                }

                const response = await api.post(url, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                alert(
                    response?.data?.message ||
                        (id
                            ? "Task updated successfully."
                            : "Task created successfully.")
                );
            } catch (err) {
                throw err;
            }
        },
        async changeStatus(id, status) {
            await api
                .post(`/tasks/${id}`, {
                    _method: "PUT",
                    status: status,
                })
                .then((res) => {
                    this.loadTasks(this.params);
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
        async deleteTask(id) {
            await api
                .delete(`/tasks/${id}`)
                .then((res) => {
                    if (res.status === 200) {
                        // this.tasks = this.tasks.filter(
                        //     (task) => task.id !== id
                        // );
                        alert("Task deleted successfully.");
                        this.loadTasks(this.params);
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert(
                        err?.response?.data?.message || "Something went wrong"
                    );
                });
        },
    },
});
