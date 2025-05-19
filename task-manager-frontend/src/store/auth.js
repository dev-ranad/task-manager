import { defineStore } from "pinia";
import api from "../api/axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: JSON.parse(localStorage.getItem("user")) || null,
        token: localStorage.getItem("token") || null,
    }),
    actions: {
        async setUser(user) {
            localStorage.setItem("user", JSON.stringify(user));
            this.user = user;
            console.log(this.user);
        },
        async setToken(token) {
            localStorage.setItem("token", token);
            this.token = token;
            console.log(this.token);
        },
        async logout(router) {
            try {
                await api.post("/auth/logout", {}, [
                    {
                        headers: {
                            Authorization: `Bearer ${this.token}`,
                            "Content-Type": "application/json",
                        },
                    },
                ]).then((response) => {
                    if (response.status === 200) {
                        this.user = null;
                        this.token = null;
                        localStorage.removeItem("user");
                        localStorage.removeItem("token");

                        alert("Logout successful");
                        router.push("/login");
                    }
                });
            } catch (error) {
                console.log(
                    "Logout failed:",
                    error.response?.data?.message || error.message
                );
                alert(
                    "Logout failed: " +
                        (error.response?.data?.message || error.message)
                );
            }
        },
    },
});
