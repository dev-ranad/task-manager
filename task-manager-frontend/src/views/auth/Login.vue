<template>
    <div class="p-4 max-w-md mx-auto">
        <h2 class="text-xl font-bold mb-4">Login</h2>
        <form @submit.prevent="handleLogin">
            <div class="mb-3">
                <input type="email" v-model="email" class="form-control" placeholder="Email address"
                    aria-describedby="emailHelp">
                <div v-if="errors?.email">
                    <span v-for="error in errors?.email" :key="error" class="text-danger">{{ error }}</span>
                </div>
            </div>
            <div class="mb-3">
                <input type="password" v-model="password" class="form-control" placeholder="Password">
                <div v-if="errors?.password">
                    <span v-for="error in errors?.password" :key="error" class="text-danger">{{ error }}</span>
                </div>
            </div>

            <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
            <button v-else type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'
import api from '../../api/axios'

const email = ref('')
const password = ref('')
const router = useRouter()
const auth = useAuthStore()
const errors = reactive({})
const loading = ref(false)

const handleLogin = async () => {
    try {
        loading.value = true
        await api.post('/auth/login', {
            email: email.value,
            password: password.value,
        }).then((response) => {
            if (response.status === 200) {
                auth.setUser(response?.data?.data?.user)
                auth.setToken(response?.data?.data[0]?.plainTextToken)
                router.push('/admin')
            }
        }).catch((error) => {
            Object.assign(errors, error?.response?.data?.errors);
            alert(error?.response?.data?.message || 'Login failed. Please try again.');
        })
        loading.value = false
    } catch (err) {
        loading.value = false
        console.log(err)
        alert('Login failed.')
    }
}


</script>
