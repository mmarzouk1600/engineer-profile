<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';

const props = defineProps({
    albumSlug: { type: String, default: null },
});

const isEdit = !!props.albumSlug;
const currentSlug = ref(props.albumSlug);
const loading = ref(isEdit);
const saving = ref(false);
const errors = ref({});

const form = reactive({
    title: '',
    description: '',
    price: '',
    currency: 'SAR',
    status: 'draft',
});

const album = ref(null); // full loaded album (images/files/cover) once created

async function loadAlbum() {
    if (!currentSlug.value) return;
    loading.value = true;
    const { data } = await window.axios.get(`/api/admin/albums/${currentSlug.value}`);
    album.value = data.data;
    form.title = data.data.title;
    form.description = data.data.description;
    form.price = data.data.price;
    form.currency = data.data.currency;
    form.status = data.data.status;
    loading.value = false;
}

onMounted(loadAlbum);

async function saveDetails() {
    saving.value = true;
    errors.value = {};
    try {
        if (isEdit && currentSlug.value) {
            const { data } = await window.axios.put(`/api/admin/albums/${currentSlug.value}`, form);
            album.value = { ...album.value, ...data.data };
        } else {
            const { data } = await window.axios.post('/api/admin/albums', form);
            album.value = data.data;
            currentSlug.value = data.data.slug;
            window.history.replaceState({}, '', `/dashboard/albums/${data.data.slug}/edit`);
        }
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors || {};
    } finally {
        saving.value = false;
    }
}

// ---------------- Images ----------------
const imageInput = ref(null);
const uploadingImages = ref(false);
const imageDragOver = ref(false);

async function uploadImages(fileList) {
    if (!currentSlug.value || !fileList?.length) return;
    uploadingImages.value = true;
    const fd = new FormData();
    Array.from(fileList).forEach((f) => fd.append('images[]', f));

    try {
        const { data } = await window.axios.post(`/api/admin/albums/${currentSlug.value}/images`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        album.value.images = [...(album.value.images || []), ...data.data];
        if (data.album?.cover_image_id && !album.value.cover_image) {
            album.value.cover_image = data.data.find((i) => i.id === data.album.cover_image_id) || album.value.images[0];
        }
    } finally {
        uploadingImages.value = false;
    }
}

function onImageDrop(e) {
    imageDragOver.value = false;
    uploadImages(e.dataTransfer.files);
}

async function deleteImage(image) {
    if (!confirm('Delete this image?')) return;
    await window.axios.delete(`/api/admin/albums/${currentSlug.value}/images/${image.id}`);
    album.value.images = album.value.images.filter((i) => i.id !== image.id);
}

async function setCover(image) {
    const { data } = await window.axios.post(`/api/admin/albums/${currentSlug.value}/images/${image.id}/cover`);
    album.value.cover_image = data.data;
}

// ---------------- Files ----------------
const fileInput = ref(null);
const uploadingFiles = ref(false);
const fileDragOver = ref(false);

async function uploadFiles(fileList) {
    if (!currentSlug.value || !fileList?.length) return;
    uploadingFiles.value = true;
    const fd = new FormData();
    Array.from(fileList).forEach((f) => fd.append('files[]', f));

    try {
        const { data } = await window.axios.post(`/api/admin/albums/${currentSlug.value}/files`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        album.value.files = [...(album.value.files || []), ...data.data];
    } finally {
        uploadingFiles.value = false;
    }
}

function onFileDrop(e) {
    fileDragOver.value = false;
    uploadFiles(e.dataTransfer.files);
}

async function deleteFile(file) {
    if (!confirm(`Delete "${file.original_name}"?`)) return;
    await window.axios.delete(`/api/admin/albums/${currentSlug.value}/files/${file.id}`);
    album.value.files = album.value.files.filter((f) => f.id !== file.id);
}

function fileIcon(ext) {
    const map = { pdf: '📄', dwg: '📐', dxf: '📐', zip: '📦', doc: '📝', docx: '📝', xls: '📊', xlsx: '📊' };
    return map[(ext || '').toLowerCase()] || '📎';
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Album' : 'New Album'" />

    <div class="max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ isEdit ? 'Edit Album' : 'Create Album' }}</h1>

        <div v-if="loading" class="text-gray-400 text-sm">Loading…</div>

        <template v-else>
            <!-- Details -->
            <section class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Album details</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input v-model="form.title" type="text" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-rose-400 outline-none" />
                        <p v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title[0] }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-rose-400 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input v-model="form.price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-rose-400 outline-none" />
                        <p v-if="errors.price" class="text-xs text-red-500 mt-1">{{ errors.price[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <input v-model="form.currency" type="text" maxlength="3" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-rose-400 outline-none uppercase" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-rose-400 outline-none">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="mt-5">
                    <button type="button" :disabled="saving" class="rounded-full bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white text-sm font-semibold px-6 py-2.5 transition" @click="saveDetails">
                        {{ saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Create album') }}
                    </button>
                </div>
            </section>

            <template v-if="currentSlug">
                <!-- Images -->
                <section class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Images</h2>

                    <div
                        class="rounded-2xl border-2 border-dashed p-8 text-center cursor-pointer transition"
                        :class="imageDragOver ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'"
                        @dragover.prevent="imageDragOver = true"
                        @dragleave.prevent="imageDragOver = false"
                        @drop.prevent="onImageDrop"
                        @click="imageInput.click()"
                    >
                        <p class="text-gray-500 text-sm">Drag & drop images here, or click to upload</p>
                        <p class="text-xs text-gray-400 mt-1">{{ uploadingImages ? 'Uploading…' : 'JPG, PNG, WEBP' }}</p>
                        <input ref="imageInput" type="file" accept="image/*" multiple class="hidden" @change="uploadImages($event.target.files)" />
                    </div>

                    <div v-if="album?.images?.length" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                        <div v-for="img in album.images" :key="img.id" class="relative group rounded-xl overflow-hidden border border-gray-100">
                            <img :src="img.thumbnail_url" class="w-full h-28 object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-1">
                                <button type="button" class="text-white text-xs font-semibold" @click="setCover(img)">Set as cover</button>
                                <button type="button" class="text-red-300 text-xs font-semibold" @click="deleteImage(img)">Delete</button>
                            </div>
                            <span v-if="album.cover_image?.id === img.id" class="absolute top-1.5 left-1.5 bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">COVER</span>
                        </div>
                    </div>
                </section>

                <!-- Files -->
                <section class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Engineering files</h2>

                    <div
                        class="rounded-2xl border-2 border-dashed p-8 text-center cursor-pointer transition"
                        :class="fileDragOver ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'"
                        @dragover.prevent="fileDragOver = true"
                        @dragleave.prevent="fileDragOver = false"
                        @drop.prevent="onFileDrop"
                        @click="fileInput.click()"
                    >
                        <p class="text-gray-500 text-sm">Drag & drop files here, or click to upload</p>
                        <p class="text-xs text-gray-400 mt-1">{{ uploadingFiles ? 'Uploading…' : 'PDF, DWG, DXF, ZIP, DOC, XLS' }}</p>
                        <input ref="fileInput" type="file" multiple class="hidden" @change="uploadFiles($event.target.files)" />
                    </div>

                    <ul v-if="album?.files?.length" class="divide-y divide-gray-100 mt-5 border border-gray-100 rounded-xl overflow-hidden">
                        <li v-for="file in album.files" :key="file.id" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ fileIcon(file.extension) }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ file.original_name }}</p>
                                    <p class="text-xs text-gray-400 uppercase">{{ file.extension }} · {{ Math.round(file.size / 1024) }} KB</p>
                                </div>
                            </div>
                            <button type="button" class="text-red-500 hover:text-red-700 text-xs font-medium" @click="deleteFile(file)">Delete</button>
                        </li>
                    </ul>
                </section>
            </template>
            <p v-else class="text-sm text-gray-400 mb-6">Save the album details first to unlock image and file uploads.</p>
        </template>
    </div>
</template>
