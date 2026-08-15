<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { getAlbumUser, setAlbumAuthToken, setAlbumUser } from '@/bootstrap';

const page = usePage();
const user = ref(getAlbumUser());
const search = ref(new URLSearchParams(window.location.search).get('search') || '');
const mobileMenuOpen = ref(false);

const isLoggedIn = computed(() => !!user.value);

function submitSearch() {
    console.log("Search submitted from master");
    
    // ✅ Update URL first
    const url = new URL(window.location.href);
    if (search.value) {
        url.searchParams.set('search', search.value);
    } else {
        url.searchParams.delete('search');
    }
    window.history.pushState({}, '', url.toString());
    
    // ✅ Dispatch custom event to notify Home component
    window.dispatchEvent(new CustomEvent('search-updated', { 
        detail: { search: search.value } 
    }));
    
    // ✅ Also navigate with Inertia
    router.visit('/', {
        data: { search: search.value || undefined },
        preserveState: true,
        replace: true,
        only: ['albums'],
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('search-updated', { 
                detail: { search: search.value } 
            }));
        }
    });
}

function logout() {
    Promise.allSettled([window.axios.post('/api/logout'), window.axios.post('/session/logout')]).finally(() => {
        setAlbumAuthToken(null);
        setAlbumUser(null);
        user.value = null;
        window.location.href = '/';
    });
}

// ✅ Watch for search changes
watch(() => search.value, (newSearch, oldSearch) => {
    const urlSearch = new URLSearchParams(window.location.search).get('search') || '';
    if (newSearch !== urlSearch) {
        submitSearch();
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
                <Link href="/" class="shrink-0 flex items-center gap-2 font-bold text-xl text-gray-900">
                    <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-sm">EO</span>
                    <span class="hidden sm:inline">Engineering Office</span>
                </Link>

                <form class="flex-1 max-w-xl" @submit.prevent="submitSearch">
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search albums by title or description…"
                            class="w-full rounded-full border border-gray-200 bg-gray-100 focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 pl-4 pr-10 py-2.5 text-sm outline-none transition"
                        />
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                            </svg>
                        </button>
                    </div>
                </form>

                <nav class="hidden sm:flex items-center gap-3 shrink-0">
                    <template v-if="isLoggedIn">
                        <a href="/dashboard" v-if="user?.role === 'admin'" class="text-sm font-medium text-gray-600 hover:text-gray-900">Admin</a>
                        <span class="text-sm text-gray-600">Hi, {{ user?.name }}</span>
                        <button class="text-sm font-medium text-rose-600 hover:text-rose-700" @click="logout">Logout</button>
                    </template>
                    <template v-else>
                        <Link href="/login" class="text-sm font-medium text-gray-600 hover:text-gray-900">Login</Link>
                        <Link href="/register" class="text-sm font-semibold bg-rose-600 text-white px-4 py-2 rounded-full hover:bg-rose-700 transition">Register</Link>
                    </template>
                </nav>

                <button class="sm:hidden text-gray-500" @click="mobileMenuOpen = !mobileMenuOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div v-if="mobileMenuOpen" class="sm:hidden border-t border-gray-100 px-4 py-3 flex flex-col gap-2">
                <template v-if="isLoggedIn">
                    <a href="/dashboard" v-if="user?.role === 'admin'" class="text-sm font-medium text-gray-700">Admin dashboard</a>
                    <button class="text-sm font-medium text-rose-600 text-left" @click="logout">Logout</button>
                </template>
                <template v-else>
                    <Link href="/login" class="text-sm font-medium text-gray-700">Login</Link>
                    <Link href="/register" class="text-sm font-medium text-gray-700">Register</Link>
                </template>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-gray-100 bg-white mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>&copy; {{ new Date().getFullYear() }} Engineering Office. All rights reserved.</span>
                <span>Structural, civil & architectural drawings, delivered securely.</span>
            </div>
        </footer>
    </div>
</template>
