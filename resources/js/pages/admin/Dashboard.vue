<script setup lang="ts">
import { ref } from 'vue';
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

// Define ratings as a reactive variable
const ratings = ref<any[]>([]);
async function fetchRatings() {
    try {
        const response = await fetch(`/api/ratings/page/1`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        // print the fetched data to console
        console.log('Fetched Ratings:', data);
        ratings.value = data.ratings; // Assuming the API returns an object with a 'ratings' property
    } catch (error) {
        console.error('Error fetching ratings:', error);
    }
}

onMounted(() => {
    fetchRatings();
});

</script>
<template>    

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid gap-4 md:grid-cols-3 auto-rows-max items-start">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col">
                    <div class="flex flex-col justify-center items-center p-4 flex-1">
                        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Buku yang sedang dipinjam</h2>
                        <!-- Kontennya kalau ada buku dipinjem, kasih cardnya, kalau ga ada munculkan card dengan tulisan "Tidak ada buku yang dipinjam" lalu munculkan tombol Pinjam-->
                        <div class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 flex flex-col w-full max-w-xs">
                            <div class="mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak ada buku yang dipinjam</h3>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-600 dark:text-gray-400">Silakan pinjam buku baru.</p>
                            </div>
                            <div class="mt-4">
                                <button class="inline-block bg-blue-500 text-white text-sm px-4 py-2 rounded hover:bg-blue-600 w-full">
                                    Pinjam Buku
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col md:col-span-2">
                    <div class="flex flex-col justify-center items-center p-4 flex-1">
                        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Buku yang sedang dipinjam</h2>
                        <!-- Kontennya kalau ada buku dipinjem, kasih cardnya, kalau ga ada munculkan card dengan tulisan "Tidak ada buku yang dipinjam" lalu munculkan tombol Pinjam-->
                        <div class="flex gap-4 w-full">
                            <div class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 flex flex-col w-full max-w-xs">
                                <div class="mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak ada buku yang dipinjam</h3>
                                </div>
                                <div class="flex-1">
                                    <p class="text-gray-600 dark:text-gray-400">Silakan pinjam buku baru.</p>
                                </div>
                                <div class="mt-4">
                                    <button class="inline-block bg-blue-500 text-white text-sm px-4 py-2 rounded hover:bg-blue-600 w-full">
                                        Pinjam Buku
                                    </button>
                                </div>
                            </div>
                            <div class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 flex flex-col w-full max-w-xs">
                                <div class="mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak ada buku yang dipinjam</h3>
                                </div>
                                <div class="flex-1">
                                    <p class="text-gray-600 dark:text-gray-400">Silakan pinjam buku baru.</p>
                                </div>
                                <div class="mt-4">
                                    <button class="inline-block bg-blue-500 text-white text-sm px-4 py-2 rounded hover:bg-blue-600 w-full">
                                        Pinjam Buku
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <!-- print data rating -->
                <div class="absolute top-4 left-4">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Review Buku</h2>
                </div>
                <div class="absolute top-4 right-4 text-gray-500 dark:text-gray-400">
                    <p v-if="ratings.length">Showing {{ ratings.length }} ratings</p>
                    <p v-else>No ratings available</p>
                </div>
                <!-- Menggunakan card dari Tailwind CSS (atau bisa diganti dengan komponen card lain jika ada) -->
                <div class="relative mt-16 px-4 text-gray-500 dark:text-gray-400">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="rating in ratings"
                            :key="rating.id"
                            class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6 flex flex-col"
                        >
                            <div class="mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ rating.book_id }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ rating.user_id }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-600 dark:text-gray-400">{{ rating.comment }}</p>
                            </div>
                            <div class="mt-4">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded dark:bg-blue-900 dark:text-blue-200">
                                    Rating: {{ rating.rating }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>