<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Duplicate Fund Warnings</h1>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Fund</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Existing Fund</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matched Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="warning in warnings" :key="warning.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ warning.fund?.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ warning.duplicate_fund?.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ warning.matched_name }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ warning.fund_manager?.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(warning.created_at).toLocaleDateString() }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button
                                @click="resolve(warning)"
                                class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded text-sm cursor-pointer"
                            >
                                Resolve
                            </button>
                        </td>
                    </tr>
                    <tr v-if="warnings.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No unresolved duplicate warnings.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../api';

const warnings = ref([]);

async function fetchWarnings() {
    const { data } = await api.get('/duplicate-warnings');
    warnings.value = data.data;
}

async function resolve(warning) {
    await api.patch(`/duplicate-warnings/${warning.id}/resolve`);
    fetchWarnings();
}

const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') fetchWarnings();
};

onMounted(() => {
    fetchWarnings();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>
