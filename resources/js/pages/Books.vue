<script setup lang="ts">
import { ref } from 'vue';
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { TailwindPagination } from 'laravel-vue-pagination';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Books',
        href: '/book',
    },
];

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as User;

// Define buku as a reactive variable
const buku = ref({});

const fetchBuku = async (page = 1) => {
    try {
        const response = await fetch(`/api/books?page=${page}`, {
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
        console.log('Fetched Buku:', data);
        buku.value = data.buku; // Assuming the API returns an object with a 'buku' property
    } catch (error) {
        console.error('Error fetching buku:', error);
    }
}

onMounted(() => {
    fetchBuku();
});

const pinjambukuform = useForm({
    id_buku: '',
    id_anggota_pinjam: user.id,
    tanggal_pinjam: '',
    tanggal_kembali: '',
    status: '',
});

import Swal from 'sweetalert2';
import { identity } from '@vueuse/core';
const showPinjamBukuModal = async (Buku: any) => {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Pinjam Buku',
        html: `
            <div class="mb-6 flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <span class="inline-block bg-blue-100 text-blue-800 text-xl px-4 py-2 rounded-full dark:bg-blue-900 dark:text-blue-200 font-bold">
                        ID: ${Buku.id_buku ?? Buku}
                    </span>
                    <span class="text-xl font-extrabold text-gray-900 dark:text-white">
                        ${Buku.judul ?? ''}
                    </span>
                </div>
                <div class="flex-shrink-0 ml-8">
                    <span class="block text-base text-gray-500 dark:text-gray-400 mb-2">Stok</span>
                    <span class="text-xl font-extrabold text-gray-800 dark:text-gray-200 px-4 py-2 bg-gray-100 dark:bg-gray-900 rounded">
                        ${Buku.stok ?? ''}
                    </span>
                </div>
            </div>
            <div class="mb-6 flex flex-col gap-4">
                <div>
                    <label for="tanggal_pinjam" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tanggal Pinjam</label>
                    <input
                        type="date"
                        id="tanggal_pinjam"
                        class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 transition-all shadow-sm"
                        value="${Date.now ? new Date(Date.now()).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]}"
                        readonly
                    >
                </div>
                <div>
                    <label for="tanggal_kembali" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tanggal Kembali</label>
                    <input
                        type="date"
                        id="tanggal_kembali"
                        class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-400 transition-all shadow-sm"
                        value="${new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().split('T')[0]}"
                        min="${new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().split('T')[0]}"
                    >
                </div>
            </div>
        `,
        background: isDark ? '#1e293b' : '#fff', // slate-800 for dark, white for light
        color: isDark ? '#f1f5f9' : '#111827',   // slate-100 for dark, gray-900 for light
        customClass: {
            popup: isDark ? 'swal2-dark' : ''
        },
        focusConfirm: false,
        preConfirm: () => {
            const tanggal_pinjam = (document.getElementById('tanggal_pinjam') as HTMLInputElement)?.value;
            const tanggal_kembali = (document.getElementById('tanggal_kembali') as HTMLInputElement)?.value;
            if (!tanggal_pinjam || !tanggal_kembali) {
                Swal.showValidationMessage('Tanggal pinjam dan tanggal kembali harus diisi');
                return false;
            }
            pinjambukuform.id_buku = Buku.id_buku ?? Buku;
            pinjambukuform.tanggal_pinjam = tanggal_pinjam;
            pinjambukuform.tanggal_kembali = tanggal_kembali;
            pinjambukuform.status = 'Pinjam';
        },
        showCancelButton: true,

        confirmButtonText: 'Pinjam Buku',
        cancelButtonText: 'Batal',
        confirmButtonColor: isDark ? '#3b82f6' : '#2563eb', // blue-500 for dark, blue-600 for light
        cancelButtonColor: isDark ? '#f87171' : '#ef4444', // red-500 for dark, red-600 for light
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/pinjam', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(pinjambukuform),
            })
            .then(async (response) => {
                const data = await response.json();
                const isDark = document.documentElement.classList.contains('dark');
                if (response.ok) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Buku berhasil dipinjam.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        background: isDark ? '#1e293b' : '#fff',
                        color: isDark ? '#f1f5f9' : '#111827',
                        customClass: {
                            popup: isDark ? 'swal2-dark' : ''
                        }
                    });
                    fetchBuku();
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan saat meminjam buku.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        background: isDark ? '#1e293b' : '#fff',
                        color: isDark ? '#f1f5f9' : '#111827',
                        customClass: {
                            popup: isDark ? 'swal2-dark' : ''
                        }
                    });
                }
            })
            .catch(() => {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Tidak dapat terhubung ke server.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    background: isDark ? '#1e293b' : '#fff',
                    color: isDark ? '#f1f5f9' : '#111827',
                    customClass: {
                        popup: isDark ? 'swal2-dark' : ''
                    }
                });
            });
        }
        else if (result.isDismissed) {
            console.log('Pinjam buku dibatalkan');
        }
    });
};

</script>
<template>    

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col md:col-span-2">
                <div class="flex flex-col justify-center items-center p-4 flex-1">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Rekomendasi Buku</h2>
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
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <!-- print data rating -->
                <div class="absolute top-4 left-4">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Semua Buku</h2>
                </div>
                <!-- Pagination -->
                <div class="flex justify-end px-4 pt-4">
                    <TailwindPagination
                        :data="buku"
                        @pagination-change-page="fetchBuku"
                    />
                </div>
                <!-- Menggunakan card dari Tailwind CSS (atau bisa diganti dengan komponen card lain jika ada) -->
                <div class="relative mt-16 px-4 text-gray-500 dark:text-gray-400">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="buku in buku.data"
                            :key="buku.id"
                            class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6 flex flex-col"
                        >
                            <div class="mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ buku.judul }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ buku.penerbit }} - {{ buku.tahun_terbit }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-600 dark:text-gray-400">{{ buku.penulis }}</p>
                            </div>
                            <div class="mt-4 flex flex-row gap-2">
                                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded dark:bg-gray-900 dark:text-gray-200">
                                    Stok: {{ buku.stok }}
                                </span>
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded dark:bg-blue-900 dark:text-blue-200">
                                    {{ buku.genre }}
                                </span>
                            </div>
                            <!--Tombol Pinjam -->
                            <div class="mt-4">
                                <button @click="showPinjamBukuModal(buku)" class="inline-block bg-blue-500 text-white text-sm px-4 py-2 rounded hover:bg-blue-600 w-full">
                                    Pinjam Buku
                                </button>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="flex justify-center mt-4 mb-6">
                    <TailwindPagination
                        :data="buku"
                        @pagination-change-page="fetchBuku"
                    />
                </div>
            </div>
        </div>

    </AppLayout>
</template>