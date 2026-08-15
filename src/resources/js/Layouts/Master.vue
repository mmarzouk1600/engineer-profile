<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { getAlbumUser, setAlbumAuthToken, setAlbumUser } from '@/bootstrap';

const page = usePage();
const user = ref(getAlbumUser());
const sidebarOpen = ref(false);

const nav = [
    { label: 'Dashboard', href: '/dashboard', icon: '📊' },
    { label: 'Albums', href: '/dashboard/albums', icon: '🗂️' },
    { label: 'Purchases & Sales', href: '/dashboard/purchases', icon: '💳' },
];

const currentUrl = computed(() => page.url);

function isActive(href) {
    return currentUrl.value === href || (href !== '/dashboard' && currentUrl.value.startsWith(href));
}

function logout() {
    Promise.allSettled([window.axios.post('/api/logout'), window.axios.post('/session/logout')]).finally(() => {
        setAlbumAuthToken(null);
        setAlbumUser(null);
        window.location.href = '/login';
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex" dir="ltr">
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-gray-200 transform transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="h-16 flex items-center gap-2 px-6 font-bold text-white text-lg border-b border-gray-800">
                <span class="w-8 h-8 rounded-xl bg-rose-600 flex items-center justify-center text-sm">EO</span>
                Admin
            </div>
            <nav class="p-4 space-y-1">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition"
                    :class="isActive(item.href) ? 'bg-rose-600 text-white' : 'hover:bg-gray-800 text-gray-300 text-white'"
                    @click="sidebarOpen = false"
                >
                    <span>{{ item.icon }}</span> <span style="color:#fff">{{ item.label}}</span> 
                </Link>
                <a href="/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800 text-gray-400 mt-4">
                    <span>🌐</span> <span style="color:#fff">  View public site </span>
                </a>
            </nav>
        </aside>

        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-30 lg:hidden" @click="sidebarOpen = false"></div>

        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
            <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
                <button class="lg:hidden text-gray-500" @click="sidebarOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="hidden lg:block"></div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ user?.name }}</span>
                    <button class="text-sm font-medium text-rose-600 hover:text-rose-700" @click="logout">Logout</button>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
