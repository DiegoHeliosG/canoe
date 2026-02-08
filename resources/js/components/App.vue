<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-8">
                        <router-link to="/funds" class="text-xl font-bold text-gray-800">Canoe</router-link>
                        <router-link
                            v-for="link in navLinks"
                            :key="link.to"
                            :to="link.to"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium"
                            active-class="text-blue-600 border-b-2 border-blue-600"
                        >
                            {{ link.label }}
                        </router-link>
                    </div>
                </div>
            </div>
        </nav>
        <main class="max-w-7xl mx-auto px-4 py-6">
            <router-view />
        </main>
    </div>
</template>

<script setup>
const navLinks = [
    { to: '/funds', label: 'Funds' },
    { to: '/fund-managers', label: 'Fund Managers' },
    { to: '/companies', label: 'Companies' },
    { to: '/duplicate-warnings', label: 'Duplicate Warnings' },
];
</script>
