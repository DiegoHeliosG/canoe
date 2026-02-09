<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Funds</h1>
            <router-link
                to="/funds/create"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer"
            >
                Create Fund
            </router-link>
        </div>

        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.name"
                    placeholder="Filter by name..."
                    class="border rounded px-3 py-2"
                    @input="debouncedFetch"
                />
                <select v-model="filters.fund_manager_id" class="border rounded px-3 py-2" @change="fetchFunds">
                    <option value="">All Managers</option>
                    <option v-for="m in managers" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
                <input
                    v-model="filters.year"
                    type="number"
                    placeholder="Filter by year..."
                    class="border rounded px-3 py-2"
                    @input="debouncedFetch"
                />
                <select v-model="filters.company_id" class="border rounded px-3 py-2" @change="fetchFunds">
                    <option value="">All Companies</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aliases</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Companies</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="fund in funds" :key="fund.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ fund.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ fund.manager?.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ fund.start_year }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span
                                v-for="alias in fund.aliases"
                                :key="alias.id"
                                class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-1 mb-1"
                            >
                                {{ alias.name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span
                                v-for="company in fund.companies"
                                :key="company.id"
                                class="inline-block bg-blue-100 rounded px-2 py-1 text-xs mr-1 mb-1"
                            >
                                {{ company.name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <router-link
                                :to="`/funds/${fund.id}/edit`"
                                class="text-blue-600 hover:text-blue-800 cursor-pointer"
                            >
                                Edit
                            </router-link>
                            <button
                                @click="deleteFund(fund)"
                                class="text-red-600 hover:text-red-800 cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="funds.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No funds found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
            <button
                v-for="page in pagination.last_page"
                :key="page"
                @click="goToPage(page)"
                :class="[
                    'px-3 py-1 rounded text-sm cursor-pointer',
                    page === pagination.current_page
                        ? 'bg-blue-600 text-white'
                        : 'bg-white text-gray-700 border hover:bg-gray-50'
                ]"
            >
                {{ page }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '../api';
import { parseCollection } from '../jsonapi';

const funds = ref([]);
const managers = ref([]);
const companies = ref([]);
const pagination = reactive({ current_page: 1, last_page: 1 });
const filters = reactive({ name: '', fund_manager_id: '', year: '', company_id: '' });

let debounceTimer = null;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchFunds, 300);
};

async function fetchFunds(page = 1) {
    const params = { page: typeof page === 'number' ? page : 1 };
    if (filters.name) params.name = filters.name;
    if (filters.fund_manager_id) params.fund_manager_id = filters.fund_manager_id;
    if (filters.year) params.year = filters.year;
    if (filters.company_id) params.company_id = filters.company_id;

    const { data } = await api.get('/funds', { params });
    const result = parseCollection(data);
    funds.value = result.items;
    pagination.current_page = result.meta.current_page;
    pagination.last_page = result.meta.last_page;
}

function goToPage(page) {
    fetchFunds(page);
}

async function deleteFund(fund) {
    if (!confirm(`Delete "${fund.name}"?`)) return;
    await api.delete(`/funds/${fund.id}`);
    fetchFunds(pagination.current_page);
}

const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') fetchFunds(pagination.current_page);
};

onMounted(async () => {
    document.addEventListener('visibilitychange', onVisibilityChange);
    const [, managersRes, companiesRes] = await Promise.all([
        fetchFunds(),
        api.get('/fund-managers', { params: { per_page: 100 } }),
        api.get('/companies', { params: { per_page: 100 } }),
    ]);
    managers.value = parseCollection(managersRes.data).items;
    companies.value = parseCollection(companiesRes.data).items;
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>
