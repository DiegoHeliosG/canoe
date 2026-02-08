<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Fund</h1>

        <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-2xl">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" type="text" required class="w-full border rounded px-3 py-2" />
                    <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Year</label>
                    <input v-model.number="form.start_year" type="number" required class="w-full border rounded px-3 py-2" />
                    <p v-if="errors.start_year" class="text-red-500 text-sm mt-1">{{ errors.start_year[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fund Manager</label>
                    <select v-model="form.fund_manager_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select a manager</option>
                        <option v-for="m in managers" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                    <p v-if="errors.fund_manager_id" class="text-red-500 text-sm mt-1">{{ errors.fund_manager_id[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aliases</label>
                    <div v-for="(alias, index) in form.aliases" :key="index" class="flex items-center space-x-2 mb-2">
                        <input v-model="form.aliases[index]" type="text" class="flex-1 border rounded px-3 py-2" placeholder="Alias name" />
                        <button type="button" @click="form.aliases.splice(index, 1)" class="text-red-500 hover:text-red-700 px-2 cursor-pointer">Remove</button>
                    </div>
                    <button type="button" @click="form.aliases.push('')" class="text-blue-600 hover:text-blue-800 text-sm cursor-pointer">
                        + Add Alias
                    </button>
                    <p v-if="aliasErrors.length" class="text-red-500 text-sm mt-1">{{ aliasErrors[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Companies</label>
                    <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto border rounded p-2">
                        <label v-for="c in companies" :key="c.id" class="flex items-center space-x-2 text-sm">
                            <input type="checkbox" :value="c.id" v-model="form.company_ids" />
                            <span>{{ c.name }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <router-link to="/funds" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50 cursor-pointer">Cancel</router-link>
                <button type="submit" :disabled="submitting" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 cursor-pointer">
                    {{ submitting ? 'Creating...' : 'Create Fund' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const managers = ref([]);
const companies = ref([]);
const submitting = ref(false);
const errors = ref({});

const form = reactive({
    name: '',
    start_year: new Date().getFullYear(),
    fund_manager_id: '',
    aliases: [],
    company_ids: [],
});

const aliasErrors = computed(() => {
    return Object.keys(errors.value)
        .filter(k => k.startsWith('aliases'))
        .map(k => errors.value[k][0]);
});

async function submit() {
    submitting.value = true;
    errors.value = {};
    try {
        const payload = { ...form, aliases: form.aliases.filter(a => a.trim()) };
        await api.post('/funds', payload);
        router.push('/funds');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    const [managersRes, companiesRes] = await Promise.all([
        api.get('/fund-managers', { params: { per_page: 100 } }),
        api.get('/companies', { params: { per_page: 100 } }),
    ]);
    managers.value = managersRes.data.data;
    companies.value = companiesRes.data.data;
});
</script>
