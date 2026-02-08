<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Fund Managers</h1>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6 max-w-md">
            <h2 class="text-lg font-semibold mb-3">{{ editing ? 'Edit' : 'Add' }} Fund Manager</h2>
            <form @submit.prevent="saveManager" class="flex items-end space-x-3">
                <div class="flex-1">
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Manager name"
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funds</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="manager in managers" :key="manager.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ manager.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ manager.funds_count }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button @click="editManager(manager)" class="text-blue-600 hover:text-blue-800 cursor-pointer">Edit</button>
                            <button @click="deleteManager(manager)" class="text-red-600 hover:text-red-800 cursor-pointer">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="managers.length === 0">
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No fund managers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="deleteError" class="text-red-500 mt-4">{{ deleteError }}</p>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '../api';

const managers = ref([]);
const editing = ref(null);
const errors = ref({});
const deleteError = ref('');
const form = reactive({ name: '' });

async function fetchManagers() {
    const { data } = await api.get('/fund-managers');
    managers.value = data.data;
}

function editManager(manager) {
    editing.value = manager.id;
    form.name = manager.name;
}

function cancelEdit() {
    editing.value = null;
    form.name = '';
    errors.value = {};
}

async function saveManager() {
    errors.value = {};
    try {
        if (editing.value) {
            await api.put(`/fund-managers/${editing.value}`, form);
        } else {
            await api.post('/fund-managers', form);
        }
        cancelEdit();
        fetchManagers();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    }
}

async function deleteManager(manager) {
    if (!confirm(`Delete "${manager.name}"?`)) return;
    deleteError.value = '';
    try {
        await api.delete(`/fund-managers/${manager.id}`);
        fetchManagers();
    } catch (e) {
        if (e.response?.status === 409) {
            deleteError.value = e.response.data.message;
        }
    }
}

const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') fetchManagers();
};

onMounted(() => {
    fetchManagers();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>
