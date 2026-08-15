<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const stats = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await window.axios.get('/api/admin/dashboard/stats');
        stats.value = data;
    } finally {
        loading.value = false;
    }
});

const cards = [
    { key: 'total_albums', label: 'Total Albums', icon: '🗂️' },
    { key: 'published_albums', label: 'Published Albums', icon: '✅' },
    { key: 'total_customers', label: 'Total Customers', icon: '👥' },
    { key: 'total_purchases', label: 'Total Purchases', icon: '🧾' },
    { key: 'successful_payments', label: 'Successful Payments', icon: '💳' },
    { key: 'pending_payments', label: 'Pending Payments', icon: '⏳' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

        <div v-if="loading" class="text-gray-400 text-sm">Loading statistics…</div>

        <template v-else-if="stats">
            <div class="rounded-2xl bg-gradient-to-br from-rose-600 to-rose-500 text-white p-6 mb-6">
                <p class="text-rose-100 text-sm font-medium">Total Revenue</p>
                <p class="text-4xl font-extrabold mt-1">{{ stats.currency || 'SAR' }} {{ Number(stats.total_revenue ?? 0).toLocaleString() }}</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="c in cards" :key="c.key" class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="text-2xl mb-2">{{ c.icon }}</div>
                    <p class="text-2xl font-bold text-gray-900">{{ stats[c.key] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ c.label }}</p>
                </div>
            </div>
        </template>
    </div>
</template>
