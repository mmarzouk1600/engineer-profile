<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { getAlbumUser } from '@/bootstrap';

const props = defineProps({
    slug: { type: String, required: true },
    meta: { type: Object, default: () => ({}) },
});

const page = usePage();
const album = ref(null);
const loading = ref(true);
const purchasing = ref(false);
const lightboxImage = ref(null);
const user = ref(getAlbumUser());

const flash = computed(() => page.props.flash || {});

async function loadAlbum() {
    loading.value = true;
    try {
        const { data } = await window.axios.get(`/api/albums/${props.slug}`);
        album.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(loadAlbum);

function fileIcon(ext) {
    const map = { pdf: '📄', dwg: '📐', dxf: '📐', zip: '📦', doc: '📝', docx: '📝', xls: '📊', xlsx: '📊' };
    return map[(ext || '').toLowerCase()] || '📎';
}

async function purchase() {
    if (!user.value) {
        router.visit(`/login?redirect=/albums/${props.slug}`);
        return;
    }

    purchasing.value = true;
    try {
        const { data } = await window.axios.post(`/api/albums/${props.slug}/purchase`);

        if (data.already_purchased) {
            await loadAlbum();
            return;
        }

        window.location.href = data.payment_url;
    } catch (e) {
        alert(e.response?.data?.message || 'Could not start checkout. Please try again.');
    } finally {
        purchasing.value = false;
    }
}

function downloadFile(file) {
    // Authorization header already attached globally via axios defaults,
    // so a plain navigation would 401. Stream through axios instead and
    // save the blob — the server still enforces ownership + payment status.
    window.axios
        .get(`/api/albums/${props.slug}/files/${file.id}/download`, { responseType: 'blob' })
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const a = document.createElement('a');
            a.href = url;
            a.download = file.original_name;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(() => alert('Download failed. Please make sure your purchase is complete.'));
}
</script>

<template>
    <Head :title="meta.title">
        <meta v-if="meta.description" name="description" :content="meta.description" />
    </Head>

    <div v-if="flash.message" class="max-w-4xl mx-auto mt-4 px-4">
        <div class="rounded-xl px-4 py-3 text-sm font-medium" :class="flash.icon === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
            {{ flash.message }}
        </div>
    </div>

    <div v-if="loading" class="max-w-5xl mx-auto px-4 py-24 text-center text-gray-400">Loading album…</div>

    <div v-else-if="album" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="rounded-3xl overflow-hidden bg-gray-100 mb-8 aspect-[16/7] relative">
            <img
                v-if="album.cover_image"
                :src="album.cover_image.url"
                :alt="album.title"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-6xl text-gray-300">📐</div>
        </div>

        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">{{ album.title }}</h1>
                <p class="mt-3 text-gray-600 max-w-2xl whitespace-pre-line">{{ album.description }}</p>
                <div class="flex gap-4 mt-4 text-sm text-gray-400">
                    <span>🖼️ {{ album.images_count ?? album.images?.length }} images</span>
                    <span>📄 {{ album.files_count ?? album.files?.length }} files</span>
                </div>
            </div>

            <div class="shrink-0 w-full md:w-72 rounded-2xl border border-gray-100 shadow-sm p-6 bg-white">
                <div class="text-3xl font-extrabold text-gray-900 mb-4">{{ album.currency }} {{ Number(album.price).toLocaleString() }}</div>

                <template v-if="album.purchased">
                    <div class="rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold px-3 py-2 mb-4 text-center">✓ Purchased</div>
                </template>
                <button
                    v-else
                    type="button"
                    :disabled="purchasing"
                    class="w-full rounded-full bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white font-semibold py-3 transition"
                    @click="purchase"
                >
                    {{ purchasing ? 'Redirecting…' : 'Purchase & Download' }}
                </button>
                <p class="text-xs text-gray-400 mt-3 text-center">Secure checkout via Tap Payments.</p>
            </div>
        </div>

        <section class="mb-12">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Images</h2>
            <div class="columns-2 sm:columns-3 gap-3 [column-fill:_balance]">
                <button
                    v-for="img in album.images"
                    :key="img.id"
                    type="button"
                    class="block w-full mb-3 break-inside-avoid rounded-xl overflow-hidden bg-gray-100"
                    @click="lightboxImage = img"
                >
                    <img :src="img.thumbnail_url" :alt="album.title" loading="lazy" class="w-full h-auto hover:opacity-90 transition" />
                </button>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Files</h2>
            <ul class="divide-y divide-gray-100 rounded-2xl border border-gray-100 overflow-hidden bg-white">
                <li v-for="file in album.files" :key="file.id" class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ fileIcon(file.extension) }}</span>
                        <div>
                            <p class="font-medium text-gray-900">{{ file.original_name }}</p>
                            <p class="text-xs text-gray-400 uppercase">{{ file.extension }} · {{ Math.round(file.size / 1024) }} KB</p>
                        </div>
                    </div>
                    <button
                        v-if="album.purchased"
                        type="button"
                        class="text-sm font-semibold text-rose-600 hover:text-rose-700"
                        @click="downloadFile(file)"
                    >
                        Download
                    </button>
                    <span v-else class="text-xs text-gray-400">Purchase to unlock</span>
                </li>
            </ul>
        </section>

        <div
            v-if="lightboxImage"
            class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
            @click="lightboxImage = null"
        >
            <img :src="lightboxImage.url" class="max-h-full max-w-full rounded-lg" />
        </div>
    </div>
</template>
