<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-gray-800 mb-4">Silitech</h1>
            <p class="text-xl text-gray-600 mb-8">Laravel + Vue + Vite</p>
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
                <p v-if="user" class="text-gray-700 mb-4">
                    User from MySQL: <span class="font-bold text-green-600 text-2xl">{{ user.name }}</span>
                </p>
                <p v-else class="text-gray-400 mb-4">Loading user...</p>
                <p class="text-gray-700 mb-4">Counter: <span class="font-bold text-blue-600 text-2xl">{{ count }}</span></p>
                <button
                    @click="count++"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200"
                >
                    Click me
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const count = ref(0);
const user = ref(null);

onMounted(async () => {
    const response = await fetch('/api/user');
    user.value = await response.json();
});
</script>
