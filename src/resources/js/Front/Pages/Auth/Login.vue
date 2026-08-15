<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { setAlbumAuthToken, setAlbumUser } from '@/bootstrap';

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const redirectTo = new URLSearchParams(window.location.search).get('redirect') || '/';

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        const { data } = await window.axios.post('/api/login', {
            email: email.value,
            password: password.value,
        });

        setAlbumAuthToken(data.token);
        setAlbumUser(data.user);

        // Also establish a real "web" session — Inertia's own page
        // navigation (below) doesn't carry the JWT bearer header, so the
        // admin dashboard route needs a session cookie to authenticate.
        await window.axios.post('/session/login', { token: data.token });

        if (data.user?.role === 'admin') {
            // Crossing into the admin bundle needs a full page load (it's a
            // separate Vite entry from the public site), not an Inertia
            // client-side visit.
            window.location.href = '/dashboard';
        } else {
            router.visit(redirectTo);
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Invalid email or password.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Login" />

    <div class="max-w-md mx-auto px-4 py-16">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Welcome back</h1>
        <p class="text-gray-500 mb-8">Login to purchase and download engineering drawings.</p>

        <form class="space-y-4" @submit.prevent="submit">
            <div v-if="error" class="rounded-xl bg-rose-50 text-rose-700 text-sm px-4 py-3">{{ error }}</div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="email" type="email" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input v-model="password" type="password" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none" />
            </div>

            <button type="submit" :disabled="loading" class="w-full rounded-full bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white font-semibold py-3 transition">
                {{ loading ? 'Logging in…' : 'Login' }}
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-6 text-center">
            Don't have an account? <Link href="/register" class="text-rose-600 font-medium">Register</Link>
        </p>
    </div>
</template>
