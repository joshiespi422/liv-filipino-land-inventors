<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import type { GalleryItem } from '@/types/landing/index';

const props = defineProps<{
    items?: GalleryItem[];
}>();

const searchQuery = ref('');
const sortBy = ref('date');
const isVideoModalOpen = ref(false);
const isPhotoModalOpen = ref(false);

// --- MOCK DATA (Tailored for Tutorials) ---
const sampleItems: GalleryItem[] = [
    {
        id: 'v3',
        type: 'video',
        title: 'Navigating the Store',
        subtitle: 'Mar 2025',
        description: 'Learn how to browse products, add items to your cart, and complete your purchase securely using the member-exclusive mobile store.',
        media_path: '/assets/tutorial-store.jpg'
    },
    {
        id: 'v2',
        type: 'video',
        title: 'Account Registration',
        subtitle: 'Feb 2025',
        description: 'A step-by-step guide on how to register your account, verify your identity, and upgrade your status to unlock member benefits.',
        media_path: '/assets/tutorial-register.jpg'
    },
    {
        id: 'v1',
        type: 'video',
        title: 'Downloading the App',
        subtitle: 'Jan 2025',
        description: 'Watch this quick guide to learn how to securely download and install the FISMPC APK directly to your Android device.',
        media_path: '/assets/tutorial-download.jpg'
    },
    // Photos
    {
        id: 'p1',
        type: 'photo',
        is_highlight: true,
        title: 'Dashboard Overview',
        subtitle: 'Feb 2025',
        description: 'A quick look at the main dashboard features.',
        media_path: '/assets/tut-photo-1.jpg' 
    },
    { id: 'p2', type: 'photo', title: 'Profile Settings', subtitle: 'Jan 2025', media_path: '/assets/tut-photo-2.jpg' },
    { id: 'p3', type: 'photo', title: 'QR Code Generation', subtitle: 'Jan 2025', media_path: '/assets/tut-photo-3.jpg' },
    { id: 'p4', type: 'photo', title: 'Checking Balance', subtitle: 'Mar 2025', media_path: '/assets/tut-photo-4.jpg' },
    { id: 'p5', type: 'photo', title: 'Transaction History', subtitle: 'Mar 2025', media_path: '/assets/tut-photo-5.jpg' },
];

// --- COMPUTED DATA & FILTERING ---
const processedItems = computed(() => {
    // 1. Get Base Items
    let items = (props.items && props.items.length > 0) ? props.items : sampleItems;

    // 2. Apply Search Filter
    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();
        items = items.filter(item => {
            const titleMatch = item.title?.toLowerCase().includes(query) ?? false;
            const descMatch = item.description?.toLowerCase().includes(query) ?? false;
            return titleMatch || descMatch;
        });
    }

    // 3. Apply Sorting
    return [...items].sort((a, b) => {
        if (sortBy.value === 'name') {
            return (a.title || '').localeCompare(b.title || '');
        } else {
            // Sort by Date (Simulated descending using string IDs for recent logic)
            // If you add a real `created_at` date to GalleryItem in the future, use it here.
            return String(b.id).localeCompare(String(a.id));
        }
    });
});

// Segmenting the processed items
const videos = computed(() => processedItems.value.filter(item => item.type === 'video'));
const allPhotos = computed(() => processedItems.value.filter(item => item.type === 'photo'));

const highlightPhoto = computed(() => {
    // Pick the first one marked as highlight, or just the first available photo
    return allPhotos.value.find(photo => photo.is_highlight) || allPhotos.value[0] || null;
});

const gridPhotos = computed(() => {
    if (!highlightPhoto.value) return [];
    // Show up to 4 photos excluding the highlight
    return allPhotos.value.filter(photo => photo.id !== highlightPhoto.value!.id).slice(0, 4);
});

// --- HELPER METHODS ---
const getImageUrl = (path?: string) => {
    if (!path) return '/assets/placeholder.jpg';
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = '/assets/placeholder.jpg';
};

const toggleModal = (type: 'video' | 'photo', show: boolean) => {
    if (type === 'video') isVideoModalOpen.value = show;
    if (type === 'photo') isPhotoModalOpen.value = show;
    document.body.style.overflow = show ? 'hidden' : '';
};

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <div id="mobile-tutorial" class="overflow-x-hidden pb-8">
        
        <!-- Hero Section -->
        <div class="relative h-48 sm:h-64 md:h-80 lg:h-96 w-full">
            <img src="/assets/tutorial-bg.jpg" alt="Mobile Tutorial Background" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-center justify-center px-4 sm:px-6 text-center bg-black/40">
                <h1 class="text-white text-3xl md:text-5xl font-bold drop-shadow-lg mb-2 sm:mb-4">Mobile App Tutorials</h1>
                <p class="text-white text-xs sm:text-sm md:text-lg max-w-4xl drop-shadow-md leading-relaxed font-medium">
                    Learn how to navigate and maximize the FISMPC mobile app. Browse our step-by-step video guides and photo walkthroughs to get started.
                </p>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 px-4 sm:px-6 md:px-12 lg:px-20 mt-6 md:mt-8">
            <form class="w-full md:w-auto" @submit.prevent>
                <div class="flex items-center rounded-full overflow-hidden shadow-sm border border-gray-300 bg-white focus-within:ring-2 focus-within:ring-[#033E94]/50 transition-shadow">
                    <!-- Added v-model for real-time searching -->
                    <input 
                        type="search" 
                        id="search" 
                        v-model="searchQuery"
                        class="w-full md:w-72 lg:w-80 px-5 py-2.5 text-sm focus:outline-none border-none" 
                        placeholder="Search tutorials (e.g. login, store)..." 
                    />
                    <button type="button" class="flex items-center gap-2 px-5 py-2.5 bg-[#033E94] hover:bg-blue-800 text-white font-medium transition-colors">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg> 
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-center">
                <!-- Layout Toggles (UI only for now) -->
                <div class="flex items-center gap-1 bg-gray-100 rounded-full p-1 shadow-inner shrink-0">
                    <button class="p-1.5 sm:p-2 rounded-full hover:bg-white hover:shadow-sm transition-all text-gray-700">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <button class="p-1.5 sm:p-2 rounded-full hover:bg-white hover:shadow-sm transition-all text-[#033E94]">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                        </svg>
                    </button>
                </div>
                <!-- Added v-model for sorting -->
                <div class="flex-1 max-w-48">
                    <select id="sort" v-model="sortBy" class="w-full rounded-full border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer hover:border-gray-400 transition-colors">
                        <option value="date">Sort by Latest</option>
                        <option value="name">Sort by Name (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Video Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-12 lg:px-20 mt-10 md:mt-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 text-[#033E94] dark:text-white">Video Guides</h2>

            <div class="relative w-full">
                <div v-if="videos.length > 0">
                    <BaseCarousel 
                        :items="videos" 
                        :autoplayDelay="4000"
                        slide-class="w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1.33rem)]"
                    >                        
                        <template #prev-arrow>
                            <img src="/assets/leftarrow.png" class="w-10 h-10 md:w-12 md:h-12 bg-white hover:bg-gray-100 shadow-lg rounded-full cursor-pointer transition-transform active:scale-95" alt="Prev" />
                        </template>

                        <template #next-arrow>
                            <img src="/assets/rightarrow.png" class="w-10 h-10 md:w-12 md:h-12 bg-white hover:bg-gray-100 shadow-lg rounded-full cursor-pointer transition-transform active:scale-95" alt="Next" />
                        </template>

                        <template #slide="{ slide: video }">
                            <div class="p-3 md:p-4 h-full w-full">
                                <div class="bg-white rounded-2xl md:rounded-3xl shadow-md hover:shadow-xl transform transition duration-300 hover:-translate-y-1 p-4 sm:p-5 h-full border border-gray-100 flex flex-col w-full">
                                    
                                    <a href="#" @click.prevent="toggleModal('video', true)" class="relative block aspect-video overflow-hidden rounded-xl md:rounded-2xl shadow-sm shrink-0 bg-gray-100 group">
                                        <img 
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                            :src="getImageUrl(video.media_path)" 
                                            @error="handleImageError" 
                                            :alt="video.title" 
                                        />
                                        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition duration-300"></div>
                                        <img class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 md:w-14 md:h-14 object-contain drop-shadow-lg transition-transform duration-300 group-hover:scale-110" src="/assets/video.png" alt="Play" />
                                    </a>

                                    <div class="mt-4 sm:mt-5 flex flex-col flex-1">
                                        <div class="flex justify-between items-start gap-3 mb-2">
                                            <h5 class="text-[#033E94] text-base sm:text-lg lg:text-xl font-bold line-clamp-2 leading-tight">
                                                {{ video.title }}
                                            </h5>
                                            <span class="text-xs sm:text-sm font-medium text-gray-500 shrink-0 mt-1">
                                                {{ video.subtitle }}
                                            </span>
                                        </div>
                                        <p class="text-gray-600 line-clamp-3 text-xs sm:text-sm leading-relaxed">
                                            {{ video.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </BaseCarousel>
                </div>

                <div v-else class="text-center py-10 md:py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700">
                    <p class="text-lg">No video tutorials found matching your search.</p>
                </div>
            </div>

            <div v-if="videos.length > 0" class="flex justify-center mt-4 sm:mt-6">
                <button @click="toggleModal('video', true)" class="text-white bg-[#033E94] hover:bg-blue-800 dark:bg-white dark:text-[#033E94] shadow-md rounded-xl font-semibold text-base md:text-lg px-6 md:px-8 py-2.5 transition active:scale-95">
                    View all Videos
                </button>
            </div>
        </div>

        <!-- Photo Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-12 lg:px-20 mt-12 md:mt-16 mb-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 text-[#033E94] dark:text-white">Photo Guides</h2>

            <div v-if="allPhotos.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                
                <!-- Highlighted Photo -->
                <div v-if="highlightPhoto" class="relative w-full aspect-[4/3] md:aspect-video lg:aspect-auto lg:h-full group overflow-hidden rounded-2xl shadow-lg cursor-pointer" @click="toggleModal('photo', true)">
                    <img :src="getImageUrl(highlightPhoto.media_path)" @error="handleImageError" :alt="highlightPhoto.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl md:text-2xl font-bold">{{ highlightPhoto.title }}</h3>
                        <p class="text-sm md:text-base opacity-90 line-clamp-2">{{ highlightPhoto.description }}</p>
                    </div>
                    <div class="absolute top-4 left-0 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-4 sm:px-6 py-1.5 sm:py-2 rounded-r-full text-sm sm:text-base md:text-lg font-bold uppercase shadow-md">
                        FEATURED
                    </div>
                </div>

                <!-- Grid Photos -->
                <div v-if="gridPhotos.length > 0" class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                    <div v-for="photo in gridPhotos" :key="photo.id" class="relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl shadow-md group cursor-pointer" @click="toggleModal('photo', true)">
                        <img :src="getImageUrl(photo.media_path)" @error="handleImageError" :alt="photo.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                            <span class="text-white font-semibold text-center px-2">{{ photo.title }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-2 flex justify-center mt-4 sm:mt-6">
                    <button @click="toggleModal('photo', true)" class="text-white bg-[#033E94] hover:bg-blue-800 dark:bg-white dark:text-[#033E94] shadow-md font-semibold rounded-xl text-base md:text-lg px-6 md:px-8 py-2.5 transition active:scale-95">
                        View all Photos
                    </button>
                </div>
            </div>
            
            <div v-else class="text-center py-10 md:py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700">
                <p class="text-lg">No photo guides found matching your search.</p>
            </div>
        </div>

        <!-- Modals -->
        <teleport to="body">
            
            <!-- Videos Modal -->
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isVideoModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="toggleModal('video', false)"></div>
                    
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8">
                        <div class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl border-t-8 border-[#033E94] flex flex-col max-h-[90vh]">
                            
                            <div class="bg-white px-5 sm:px-6 py-4 sm:py-5 flex justify-between items-center border-b border-gray-100 shrink-0">
                                <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#033E94]">All Video Tutorials</h3>
                                <button @click="toggleModal('video', false)" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center transition text-xl sm:text-2xl font-bold">&times;</button>
                            </div>
                            
                            <div class="p-4 sm:p-6 md:p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-8 bg-gray-50 grow">
                                <div v-for="video in videos" :key="video.id" class="bg-white rounded-xl sm:rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100 flex flex-col group">
                                    <div class="relative aspect-video shrink-0 bg-gray-200 overflow-hidden cursor-pointer">
                                        <img :src="getImageUrl(video.media_path)" @error="handleImageError" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition"></div>
                                        <img src="/assets/video.png" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-10 h-10 sm:w-14 sm:h-14 object-contain opacity-90 drop-shadow-lg group-hover:scale-110 transition duration-300">
                                    </div>
                                    <div class="p-4 sm:p-5 flex-1 flex flex-col">
                                        <div class="flex justify-between items-start gap-3 mb-2">
                                            <h4 class="font-bold text-[#033E94] text-base sm:text-lg line-clamp-2 leading-tight">{{ video.title }}</h4>
                                            <span class="text-xs font-medium text-gray-500 shrink-0 mt-1">{{ video.subtitle }}</span>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-600 line-clamp-3">{{ video.description }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white border-t border-gray-100 px-5 sm:px-6 py-3 sm:py-4 flex justify-end shrink-0">
                                <button @click="toggleModal('video', false)" class="bg-[#033E94] text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-xl hover:bg-blue-800 font-semibold transition shadow-sm text-sm sm:text-base">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Photos Modal -->
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isPhotoModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="toggleModal('photo', false)"></div>
                    
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8">
                        <div class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl border-t-8 border-[#033E94] flex flex-col max-h-[90vh]">
                            
                            <div class="bg-white px-5 sm:px-6 py-4 sm:py-5 flex justify-between items-center border-b border-gray-100 shrink-0">
                                <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#033E94]">All Photo Guides</h3>
                                <button @click="toggleModal('photo', false)" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center transition text-xl sm:text-2xl font-bold">&times;</button>
                            </div>
                            
                            <div class="p-4 sm:p-6 md:p-8 overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-6 bg-gray-50 grow">
                                <div v-for="photo in allPhotos" :key="photo.id" class="relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl shadow-sm hover:shadow-lg transition bg-gray-200 group">
                                    <img :src="getImageUrl(photo.media_path)" @error="handleImageError" :alt="photo.title" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-500 cursor-pointer">
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                        <p class="text-white text-xs font-semibold">{{ photo.title }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white border-t border-gray-100 px-5 sm:px-6 py-3 sm:py-4 flex justify-end shrink-0">
                                <button @click="toggleModal('photo', false)" class="bg-[#033E94] text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-xl hover:bg-blue-800 font-semibold transition shadow-sm text-sm sm:text-base">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>