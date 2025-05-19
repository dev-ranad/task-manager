<template>
    <div class="p-4 max-w-md mx-auto">
        <h2 class="text-xl font-bold mb-4">Register</h2>
        <form @submit.prevent="register">
            <div class="mb-3">
                <input type="text" v-model="formData.name" class="form-control" placeholder="Name"
                    aria-describedby="nameHelp">
                <div v-if="errors?.name">
                    <span v-for="error in errors?.name" :key="error" class="text-danger">{{ error }}</span>
                </div>
            </div>
            <div class="mb-3">
                <input type="email" v-model="formData.email" class="form-control" placeholder="Email address"
                    aria-describedby="emailHelp">
                <div v-if="errors?.email">
                    <span v-for="error in errors?.email" :key="error" class="text-danger">{{ error }}</span>
                </div>
            </div>
            <div class="mb-3">
                <input type="password" v-model="formData.password" class="form-control" placeholder="Password"
                    aria-describedby="passwordHelp">
                <div v-if="errors?.password">
                    <span v-for="error in errors?.password" :key="error" class="text-danger">{{ error }}</span>
                </div>
            </div>
            <div class="mb-3">
                <input type="password" v-model="formData.password_confirmation" class="form-control"
                    placeholder="Confirm Password" aria-describedby="password_confirmationHelp">
                <div v-if="errors?.password_confirmation">
                    <span v-for="error in errors?.password_confirmation" :key="error" class="text-danger">{{ error
                        }}</span>
                </div>
            </div>

            <span v-if="loading" class="badge bg-primary me-2">Loading...</span>
            <button v-else type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import api from '../../api/axios'
import { useRouter } from 'vue-router'

const formData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})
const errors = reactive({})
const router = useRouter()
const loading = ref(false)

const register = async () => {
    try {
        loading.value = true
        await api.post('/auth/register', formData).then((response) => {
            if (response.status === 200) {
                alert('Registration successful. Please login.')
            }
            router.push('/login')
        }).catch((error) => {
            Object.assign(errors, error?.response?.data?.errors);
            alert(error?.response?.data?.message || 'Registration failed. Please try again.');
        })
        loading.value = false
    } catch (err) {
        loading.value = false
        console.error(err.response?.data)
        alert('Registration failed.')
    }
}
</script>
