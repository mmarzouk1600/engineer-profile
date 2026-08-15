<script setup>
defineProps({
    meta: { type: Object, required: true }, // Laravel paginator meta: {current_page, last_page, links, ...}
});
const emit = defineEmits(['change']);

function go(page) {
    if (page && page >= 1) emit('change', page);
}
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="flex items-center justify-center gap-1 py-6" dir="ltr">
        <button
            type="button"
            class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 disabled:opacity-40 hover:bg-gray-50"
            :disabled="meta.current_page <= 1"
            @click="go(meta.current_page - 1)"
        >
            ‹
        </button>

        <button
            v-for="p in meta.last_page"
            :key="p"
            type="button"
            class="min-w-[36px] px-3 py-1.5 rounded-lg text-sm font-medium border"
            :class="p === meta.current_page
                ? 'bg-rose-600 text-white border-rose-600'
                : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
            @click="go(p)"
        >
            {{ p }}
        </button>

        <button
            type="button"
            class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 disabled:opacity-40 hover:bg-gray-50"
            :disabled="meta.current_page >= meta.last_page"
            @click="go(meta.current_page + 1)"
        >
            ›
        </button>
    </div>
</template>
