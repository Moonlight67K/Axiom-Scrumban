<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    boards: Array,
});
</script>

<template>
    <Head title="Boards" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Boards</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="board in boards" :key="board.id" class="border p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <h3 class="text-lg font-bold">{{ board.name }}</h3>
                            <p class="text-sm text-gray-500">{{ board.description }}</p>
                            <Link :href="route('boards.show', board.id)" class="text-indigo-600 hover:text-indigo-900 mt-2 block">
                                Open Board
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
