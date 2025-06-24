<script setup lang="ts">
import { ref } from 'vue';
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import Swal from 'sweetalert2';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as User;

// Define ratings as a reactive variable
const bukudipinjam = ref<any[]>([]);
const id_user = user.id;

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
    fetchBukuDipinjam();
});

const buku = useForm({
    id_riwayat: '',
    id_buku: '',
    id_anggota_pinjaman: id_user,
    status: '',
});

const isLoadingBukuDipinjam = ref(true);

async function fetchBukuDipinjam() {
    isLoadingBukuDipinjam.value = true;
    try {
        const response = await fetch(`/api/pinjam/${id_user}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        bukudipinjam.value = data.pinjam;
        const bukuPromises = bukudipinjam.value.map(async (pinjam: any) => {
            const bukuResponse = await fetch(`/api/books/${pinjam.id_buku}`);
            if (!bukuResponse.ok) {
                throw new Error('Network response was not ok');
            }
            const bukuData = await bukuResponse.json();
            return {
                ...pinjam,
                judul: bukuData.buku.judul,
                penulis: bukuData.buku.penulis,
            };
        });

        bukudipinjam.value = await Promise.all(bukuPromises);
        bukudipinjam.value = bukudipinjam.value.map((pinjam: any) => {
            const buku = bukudipinjam.value.find((buku: any) => buku.id_buku === pinjam.id_buku);
            return {
                ...pinjam,
                judul: buku?.judul || 'Buku tidak ditemukan',
                penulis: buku?.penulis || 'Penulis tidak ditemukan',
            };
        });
    } catch (error) {
        console.error('Error fetching buku dipinjam:', error);
    } finally {
        isLoadingBukuDipinjam.value = false;
    }
}

const showReturnModal = () => {
    if (isLoadingBukuDipinjam.value) {
        Swal.fire({
            title: 'Memuat data buku...',
            timer: 6000,
            showConfirmButton: false,
            background: document.documentElement.classList.contains('dark') ? '#1f2937' : undefined,
            color: document.documentElement.classList.contains('dark') ? '#fff' : undefined,
            willOpen: () => {
            Swal.showLoading();
            },
        }).then(() => {
            showReturnModal();
        });
        return;
    }
    if (!bukudipinjam.value.length) {
        Swal.fire({
            title: 'Tidak ada buku yang dipinjam.',
            icon: 'info',
            background: document.documentElement.classList.contains('dark') ? '#1f2937' : undefined,
            color: document.documentElement.classList.contains('dark') ? '#fff' : undefined,
        });
        return;
    }

    const options = bukudipinjam.value
        .map((buku: any) => `<option value="${buku.id_riwayat}" data-id-buku="${buku.id_buku}">${buku.judul} - ${buku.penulis}</option>`)
        .join('');

    const isDark = document.documentElement.classList.contains('dark');

    Swal.fire({
        title: 'Kembalikan Buku',
        html: `
            <div class="flex flex-col gap-2">
                <p>Berikut adalah buku yang sedang Anda pinjam:</p>
                <select id="bukuSelect" class="swal2-input" style="background-color: ${isDark ? '#374151' : '#fff'}; color: ${isDark ? '#fff' : '#000'};">
                    ${options}
                </select>
                <p>Silakan pilih buku yang ingin Anda kembalikan.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Kembalikan',
        cancelButtonText: 'Batal',
        background: isDark ? '#1f2937' : undefined,
        color: isDark ? '#fff' : undefined,
        preConfirm: () => {
            buku.id_riwayat = (document.getElementById('bukuSelect') as HTMLSelectElement).value;
            buku.status = 'Kembali';
            buku.id_buku = (document.getElementById('bukuSelect') as HTMLSelectElement).selectedOptions[0].dataset.idBuku;
            if (!buku.id_riwayat) {
                Swal.showValidationMessage('Silakan pilih buku yang ingin dikembalikan.');
                return false;
            }
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Use fetch instead of buku.put to avoid Inertia response requirement
            fetch('/api/pinjam/return', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_riwayat: buku.id_riwayat,
                    id_buku: buku.id_buku,
                    id_anggota_pinjaman: buku.id_anggota_pinjaman,
                    status: buku.status,
                }),
            })
            .then(async (response) => {
                const data = await response.json();
                if (response.ok && data.result === 'OK') {
                    Swal.fire({
                        title: 'Buku berhasil dikembalikan!',
                        icon: 'success',
                        background: isDark ? '#1f2937' : undefined,
                        color: isDark ? '#fff' : undefined,
                        showCancelButton: true,
                        confirmButtonText: 'Review Buku',
                        cancelButtonText: 'Tutup',
                    }).then((reviewResult) => {
                        if (reviewResult.isConfirmed) {
                            // Tampilkan modal review buku
                            Swal.fire({
                                title: 'Review Buku',
                                html: `
                                    <div class="flex flex-col gap-2">
                                        <label for="ratingInput">Rating (1-5):</label>
                                        <input id="ratingInput" type="number" min="1" max="5" class="swal2-input" />
                                        <label for="commentInput">Komentar:</label>
                                        <textarea id="commentInput" class="swal2-textarea"></textarea>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Kirim Review',
                                cancelButtonText: 'Batal',
                                background: isDark ? '#1f2937' : undefined,
                                color: isDark ? '#fff' : undefined,
                                preConfirm: () => {
                                    const rating = (document.getElementById('ratingInput') as HTMLInputElement).value;
                                    const comment = (document.getElementById('commentInput') as HTMLTextAreaElement).value;
                                    if (!rating || Number(rating) < 1 || Number(rating) > 5) {
                                        Swal.showValidationMessage('Rating harus antara 1 sampai 5.');
                                        return false;
                                    }
                                    return { rating, comment };
                                }
                            }).then((reviewSubmit) => {
                                if (reviewSubmit.isConfirmed) {
                                    fetch('/api/ratings', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            book_id: buku.id_buku,
                                            user_id: buku.id_anggota_pinjaman,
                                            rating: reviewSubmit.value.rating,
                                            comment: reviewSubmit.value.comment,
                                        }),
                                    })
                                    .then(async (res) => {
                                        const reviewData = await res.json();
                                        if (res.ok && reviewData.result === 'OK') {
                                            Swal.fire({
                                                title: 'Review berhasil dikirim!',
                                                icon: 'success',
                                                background: isDark ? '#1f2937' : undefined,
                                                color: isDark ? '#fff' : undefined,
                                            });
                                            fetchRatings();
                                        } else {
                                            Swal.fire({
                                                title: 'Gagal mengirim review',
                                                text: reviewData && reviewData.message ? reviewData.message : 'Terjadi kesalahan saat mengirim review.',
                                                icon: 'error',
                                                background: isDark ? '#1f2937' : undefined,
                                                color: isDark ? '#fff' : undefined,
                                            });
                                        }
                                    })
                                    .catch((err) => {
                                        Swal.fire({
                                            title: 'Gagal mengirim review',
                                            text: err.message || 'Terjadi kesalahan saat mengirim review.',
                                            icon: 'error',
                                            background: isDark ? '#1f2937' : undefined,
                                            color: isDark ? '#fff' : undefined,
                                        });
                                    });
                                }
                            });
                        }
                        // Refresh the buku dipinjam list after returning the book
                        fetchBukuDipinjam();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal mengembalikan buku',
                        text: data && data.message ? data.message : 'Terjadi kesalahan saat mengembalikan buku.',
                        icon: 'error',
                        background: isDark ? '#1f2937' : undefined,
                        color: isDark ? '#fff' : undefined,
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Gagal mengembalikan buku',
                    text: error.message || 'Terjadi kesalahan saat mengembalikan buku.',
                    icon: 'error',
                    background: isDark ? '#1f2937' : undefined,
                    color: isDark ? '#fff' : undefined,
                });
            });
        }
    });
};
</script>
<template>    

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid gap-4 md:grid-cols-3 auto-rows-max items-start">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col">
                    <div class="flex flex-col justify-center items-center p-4 flex-1">
                        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white w-full text-left">Buku yang sedang dipinjam</h2>
                        <div class="flex gap-4 w-full flex-wrap">
                            <template v-if="bukudipinjam.length">
                                <div
                                    class="card bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 flex flex-col w-full max-w-xs"
                                >
                                    <div class="mb-4 flex justify-center items-center">
                                        <p class="text-4xl font-extrabold text-gray-900 dark:text-white text-center">{{ bukudipinjam.length }}</p>
                                    </div>
                                    <div class="mb-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Buku sedang dipinjam</p>
                                    </div>
                                    <div class="mt-4 flex flex-col gap-2">
                                        <button @click="showReturnModal" class="inline-block bg-blue-500 text-white text-sm px-4 py-2 rounded hover:bg-blue-600 w-full">
                                            Kembalikan Buku
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
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
                            </template>
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