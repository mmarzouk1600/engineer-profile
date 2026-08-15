<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const purchases = ref([]);
const meta = ref(null);
const loading = ref(true);
const status = ref('');

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await window.axios.get('/api/admin/purchases', {
            params: { status: status.value || undefined, page },
        });
        purchases.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

watch(status, () => load(1));
onMounted(() => load());

const statusStyles = {
    paid: 'bg-emerald-50 text-emerald-700',
    pending: 'bg-amber-50 text-amber-700',
    failed: 'bg-red-50 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
    refunded: 'bg-sky-50 text-sky-700',
};
</script>

<template>
    <Head title="Purchases" />

    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Purchases & Sales</h1>
            <select v-model="status" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-rose-400">
                <option value="">All statuses</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Album</th>
                        <th class="px-5 py-3 font-medium">Amount</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Paid at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="loading"><td colspan="5" class="px-5 py-8 text-center text-gray-400">Loading…</td></tr>
                    <tr v-else-if="purchases.length === 0"><td colspan="5" class="px-5 py-8 text-center text-gray-400">No purchases found.</td></tr>
                    <tr v-for="p in purchases" :key="p.id" v-else class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ p.user?.name }}</p>
                            <p class="text-xs text-gray-400">{{ p.user?.email }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ p.album?.title }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ p.currency }} {{ Number(p.amount).toLocaleString() }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="statusStyles[p.status] || 'bg-gray-100 text-gray-600'">
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ p.paid_at ? new Date(p.paid_at).toLocaleDateString() : '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" @change="load" />
    </div>
</template>
