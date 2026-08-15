<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { setAlbumAuthToken, setAlbumUser } from '@/bootstrap';

const name = ref('');
const email = ref('');
const password = ref('');
const errors = ref({});
const loading = ref(false);

async function submit() {
    errors.value = {};
    loading.value = true;

    try {
        const { data } = await window.axios.post('/api/register', {
            name: name.value,
            email: email.value,
            password: password.value,
        });

        setAlbumAuthToken(data.token);
        setAlbumUser(data.user);
        await window.axios.post('/session/login', { token: data.token });
        router.visit('/');
    } catch (e) {
        errors.value = e.response?.status === 422 ? e.response.data : { general: [e.response?.data?.message || 'Registration failed.'] };
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Register" />

    <div class="max-w-md mx-auto px-4 py-16">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Create your account</h1>
        <p class="text-gray-500 mb-8">Register to purchase and download engineering drawings.</p>

        <form class="space-y-4" @submit.prevent="submit">
            <div v-if="Object.keys(errors).length" class="rounded-xl bg-rose-50 text-rose-700 text-sm px-4 py-3">
                <p v-for="(msgs, field) in errors" :key="field">{{ Array.isArray(msgs) ? msgs[0] : msgs }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                <input v-model="name" type="text" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="email" type="email" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input v-model="password" type="password" minlength="6" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none" />
            </div>

            <button type="submit" :disabled="loading" class="w-full rounded-full bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white font-semibold py-3 transition">
                {{ loading ? 'Creating account…' : 'Register' }}
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-6 text-center">
            Already have an account? <Link href="/login" class="text-rose-600 font-medium">Login</Link>
        </p>
    </div>
</template>
