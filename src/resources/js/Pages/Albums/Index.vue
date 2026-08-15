<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const albums = ref([]);
const meta = ref(null);
const loading = ref(true);
const search = ref('');
const status = ref('');

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await window.axios.get('/api/admin/albums', {
            params: { search: search.value || undefined, status: status.value || undefined, page },
        });
        albums.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

let debounce;
watch([search, status], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => load(1), 300);
});

onMounted(() => load());

async function togglePublish(album) {
    await window.axios.post(`/api/admin/albums/${album.slug}/publish-toggle`);
    load(meta.value?.current_page || 1);
}

async function destroy(album) {
    if (!confirm(`Delete "${album.title}"? This cannot be undone.`)) return;
    await window.axios.delete(`/api/admin/albums/${album.slug}`);
    load(meta.value?.current_page || 1);
}
</script>

<template>
    <Head title="Albums" />

    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Albums</h1>
            <Link href="/dashboard/albums/create" class="rounded-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-5 py-2.5 transition">
                + New Album
            </Link>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <input v-model="search" type="text" placeholder="Search title or description…" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-rose-400" />
            <select v-model="status" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-rose-400">
                <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-medium">Album</th>
                        <th class="px-5 py-3 font-medium">Price</th>
                        <th class="px-5 py-3 font-medium">Images / Files</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="loading"><td colspan="5" class="px-5 py-8 text-center text-gray-400">Loading…</td></tr>
                    <tr v-else-if="albums.length === 0"><td colspan="5" class="px-5 py-8 text-center text-gray-400">No albums found.</td></tr>
                    <tr v-for="album in albums" :key="album.id" v-else class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img v-if="album.cover_image" :src="album.cover_image.thumbnail_url" class="w-10 h-10 rounded-lg object-cover" />
                                <div v-else class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300">📐</div>
                                <span class="font-medium text-gray-900">{{ album.title }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ album.currency }} {{ Number(album.price).toLocaleString() }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ album.images_count }} / {{ album.files_count }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                :class="album.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ album.status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                            <button class="text-gray-500 hover:text-gray-800 text-xs font-medium" @click="togglePublish(album)">
                                {{ album.status === 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                            <Link :href="`/dashboard/albums/${album.slug}/edit`" class="text-rose-600 hover:text-rose-700 text-xs font-medium">Edit</Link>
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium" @click="destroy(album)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" @change="load" />
    </div>
</template>
