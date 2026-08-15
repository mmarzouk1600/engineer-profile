<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    meta: { type: Object, default: () => ({}) },
});

const albums = ref([]);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const search = ref(new URLSearchParams(window.location.search).get('search') || '');
const sentinel = ref(null);
let observer;
let stopNavigateListener;
let searchEventListener;

async function loadPage(reset = false) {
    if (loading.value) return;
    loading.value = true;

    try {
        const { data } = await window.axios.get('/api/albums', {
            params: { 
                search: search.value || undefined, 
                page: reset ? 1 : page.value 
            },
        });

        albums.value = reset ? data.data : [...albums.value, ...data.data];
        page.value = data.meta.current_page + 1;
        lastPage.value = data.meta.last_page;
    } catch (error) {
        console.error('Error loading albums:', error);
    } finally {
        loading.value = false;
    }
}

function onSearch() {
    console.log("onSearch called from Home component");
    
    // ✅ Update search from URL
    search.value = new URLSearchParams(window.location.search).get('search') || '';
    
    // ✅ Reset and load
    page.value = 1;
    loadPage(true);
}

// ✅ Listen for search-updated event from master
function handleSearchUpdate(event) {
    console.log("Search update received in Home:", event.detail.search);
    search.value = event.detail.search || '';
    onSearch();
}

onMounted(() => {
    loadPage(true);

    // ✅ Listen for custom event from master
    searchEventListener = window.addEventListener('search-updated', handleSearchUpdate);

    // ✅ Listen for Inertia navigation
    let isFirstNavigate = true;
    stopNavigateListener = router.on('navigate', (event) => {
        if (isFirstNavigate) {
            isFirstNavigate = false;
            return;
        }
        
        const urlSearch = new URLSearchParams(window.location.search).get('search') || '';
        if (search.value !== urlSearch) {
            search.value = urlSearch;
            onSearch();
        }
    });

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && page.value <= lastPage.value && !loading.value) {
            loadPage();
        }
    });
    if (sentinel.value) observer.observe(sentinel.value);
});

onUnmounted(() => {
    observer?.disconnect();
    stopNavigateListener?.();
    if (searchEventListener) {
        window.removeEventListener('search-updated', handleSearchUpdate);
    }
});

function formatPrice(album) {
    return `${album.currency} ${Number(album.price).toLocaleString()}`;
}
</script>


<template>
    <Head :title="meta.title || 'Home'">
        <meta v-if="meta.description" name="description" :content="meta.description" />
    </Head>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                Engineering Drawings & Project Albums
            </h1>
            <p class="mt-3 text-gray-500 max-w-2xl mx-auto">
                Browse structural, architectural and civil engineering project albums. Preview the images, then purchase to download the full drawing set.
            </p>
        </div>

        <div class="flex justify-center mb-10 sm:hidden">
            <div class="relative w-full max-w-md">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search albums…"
                    class="w-full rounded-full border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-rose-400"
                    @keyup.enter="onSearch"
                />
            </div>
        </div>

        <div v-if="!loading && albums.length === 0" class="text-center py-24 text-gray-400">
            No albums found{{ search ? ` for "${search}"` : '' }}.
        </div>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-5 [column-fill:_balance]">
            <Link
                v-for="album in albums"
                :key="album.id"
                :href="`/albums/${album.slug}`"
                class="group block mb-5 break-inside-avoid rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-lg transition-shadow border border-gray-100"
            >
                <div class="relative overflow-hidden bg-gray-100">
                    <img
                        v-if="album.cover_image"
                        :src="album.cover_image.thumbnail_url || album.cover_image.url"
                        :alt="album.title"
                        loading="lazy"
                        class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div v-else class="w-full aspect-[4/3] flex items-center justify-center text-gray-300 text-4xl">📐</div>

                    <span class="absolute top-3 right-3 bg-white/95 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full shadow">
                        {{ formatPrice(album) }}
                    </span>
                </div>

                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 line-clamp-1">{{ album.title }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ album.description }}</p>
                    <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                        <span>🖼️ {{ album.images_count }} images</span>
                        <span>📄 {{ album.files_count }} files</span>
                    </div>
                </div>
            </Link>
        </div>

        <div ref="sentinel" class="h-10"></div>
        <div v-if="loading" class="text-center py-6 text-gray-400 text-sm">Loading more albums…</div>
    </div>
</template>