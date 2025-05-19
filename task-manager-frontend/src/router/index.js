import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../store/auth";

import Login from "../views/auth/Login.vue";
import Register from "../views/auth/Register.vue";

import Home from "../views/web/Home.vue";
// import TaskDetail from "../views/admin/TaskDetail.vue";
import Dashboard from "../views/admin/Dashboard.vue";
import TaskList from "../views/admin/task/TaskList.vue";
import TaskDetail from "../views/admin/task/TaskDetail.vue";
import TaskCreate from "../views/admin/task/TaskCreate.vue";
import TaskEdit from "../views/admin/task/TaskEdit.vue";

const routes = [
    {
        path: "/",
        component: Home,
    },
    {
        path: "/login",
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: "/register",
        component: Register,
        meta: { guestOnly: true },
    },
    {
        path: "/admin",
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                component: Dashboard,
            },
            {
                path: "task",
                component: TaskList,
            },
            {
                path: "task/create",
                component: TaskCreate,
            },
            {
                path: "task/:id",
                component: TaskDetail,
            },
            {
                path: "task/:id/edit",
                component: TaskEdit,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();
    if (to.meta.requiresAuth && !auth.token) {
        next("/login");
    } else if (to.meta.guestOnly && auth.token) {
        next("/admin");
    } else {
        next();
    }
});

export default router;
