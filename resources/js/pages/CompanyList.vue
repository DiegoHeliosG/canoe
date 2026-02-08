<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Companies</h1>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6 max-w-md">
            <h2 class="text-lg font-semibold mb-3">{{ editing ? 'Edit' : 'Add' }} Company</h2>
            <form @submit.prevent="saveCompany" class="flex items-end space-x-3">
                <div class="flex-1">
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Company name"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer">
                    {{ editing ? 'Update' : 'Add' }}
                </button>
                <button v-if="editing" type="button" @click="cancelEdit" class="text-gray-500 hover:text-gray-700 px-3 py-2 cursor-pointer">
                    Cancel
                </button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="company in companies" :key="company.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ company.name }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button @click="editCompany(company)" class="text-blue-600 hover:text-blue-800 cursor-pointer">Edit</button>
                            <button @click="deleteCompany(company)" class="text-red-600 hover:text-red-800 cursor-pointer">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="companies.length === 0">
                        <td colspan="2" class="px-6 py-4 text-center text-gray-500">No companies found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '../api';

const companies = ref([]);
const editing = ref(null);
const errors = ref({});
const form = reactive({ name: '' });

async function fetchCompanies() {
    const { data } = await api.get('/companies');
    companies.value = data.data;
}

function editCompany(company) {
    editing.value = company.id;
    form.name = company.name;
}

function cancelEdit() {
    editing.value = null;
    form.name = '';
    errors.value = {};
}

async function saveCompany() {
    errors.value = {};
    try {
        if (editing.value) {
            await api.put(`/companies/${editing.value}`, form);
        } else {
            await api.post('/companies', form);
        }
        cancelEdit();
        fetchCompanies();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    }
}

async function deleteCompany(company) {
    if (!confirm(`Delete "${company.name}"?`)) return;
    await api.delete(`/companies/${company.id}`);
    fetchCompanies();
}

const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') fetchCompanies();
};

onMounted(() => {
    fetchCompanies();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>
