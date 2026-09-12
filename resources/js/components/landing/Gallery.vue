<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import GalleryPhotoCard from '@/components/GalleryPhotoCard.vue';
import GalleryVideoCard from '@/components/GalleryVideoCard.vue';
import type { GalleryItem } from '@/types/landing/index';

const props = defineProps<{
    items?: GalleryItem[];
}>();

const sampleItems: GalleryItem[] = [
    { id: 'v1', type: 'video', title: 'Green Tech Initiative', subtitle: 'Jan 2025', description: 'Under the Green Innovation and Renewable Systems program, FISMPC installed solar microgrid systems...', media_path: '/assets/v1.jpg' },
    { id: 'v2', type: 'video', title: 'Water Purification Plant', subtitle: 'Feb 2025', description: 'Bringing clean water solutions to these communities.', media_path: '/assets/v1.jpg' },
    { id: 'v3', type: 'video', title: 'Solar Array Update', subtitle: 'Mar 2025', description: 'New updates to our solar infrastructure.', media_path: '/assets/v1.jpg' },
    { id: 'p1', type: 'photo', is_highlight: true, media_path: '/assets/pp1.jpg', title: 'Annual Meeting' },
    { id: 'p2', type: 'photo', media_path: '/assets/pp1.jpg', title: 'Workshop 1' },
    { id: 'p3', type: 'photo', media_path: '/assets/pp1.jpg', title: 'Workshop 2' },
    { id: 'p4', type: 'photo', media_path: '/assets/pp1.jpg', title: 'Field Visit' },
    { id: 'p5', type: 'photo', media_path: '/assets/pp1.jpg', title: 'Community Outreach' },
];

// --- SEARCH & SORT STATE ---
const searchQuery = ref('');
const sortBy = ref('date');

const displayItems = computed(() => {
    let items = (props.items && props.items.length > 0) ? props.items : sampleItems;
    
    // Filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        items = items.filter(item => 
            item.title?.toLowerCase().includes(query) || 
            item.description?.toLowerCase().includes(query) || 
            item.subtitle?.toLowerCase().includes(query)
        );
    }
    
    // Sort
    let sorted = [...items];
    if (sortBy.value === 'name') {
        sorted.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
    } 
    // Defaults back to original order ('date' or default list order) if date logic isn't specific
    
    return sorted;
});

// --- COMPUTED DATA ---
const videos = computed(() => displayItems.value.filter(item => item.type === 'video'));
const allPhotos = computed(() => displayItems.value.filter(item => item.type === 'photo'));

const highlightPhoto = computed(() => {
    // We prioritize the designated highlight, else fallback to the first available filtered photo
    return allPhotos.value.find(photo => photo.is_highlight) || allPhotos.value[0] || null;
});

const gridPhotos = computed(() => {
    if (!highlightPhoto.value) return [];
    return allPhotos.value.filter(photo => photo.id !== highlightPhoto.value!.id).slice(0, 4);
});

// --- MODAL STATE ---
const isVideoModalOpen = ref(false);
const isPhotoModalOpen = ref(false);

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
    <div id="gallery" class="overflow-x-hidden pb-8">
        
        <div class="relative h-48 sm:h-64 md:h-80 lg:h-96 w-full">
            <img src="/assets/gallery.jpg" alt="Gallery background" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-center justify-center px-4 sm:px-6 text-center dark:bg-black/30">
                <h1 class="text-white text-3xl md:text-5xl font-bold drop-shadow-lg mb-2 sm:mb-4">Gallery</h1>
                <p class="text-white text-xs sm:text-sm md:text-lg max-w-4xl drop-shadow-md leading-relaxed font-medium">
                    Our photo and video gallery showcase the vibrant life of our cooperative. Browse through pictures from past National Inventors Weeks, training workshops, and product launch events.
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 px-4 sm:px-6 md:px-12 lg:px-20 mt-6 md:mt-8">
            <form class="w-full md:w-auto" @submit.prevent>
                <div class="flex items-center rounded-full overflow-hidden shadow-sm border border-gray-300 bg-white focus-within:ring-2 focus-within:ring-[#033E94]/50 transition-shadow">
                    <!-- Added v-model to bind the search text directly -->
                    <input type="search" v-model="searchQuery" id="search" class="w-full md:w-72 lg:w-80 px-5 py-2.5 text-sm focus:outline-none border-none" placeholder="Search gallery..." />
                    <button type="button" class="flex items-center gap-2 px-5 py-2.5 bg-[#033E94] hover:bg-blue-800 text-white font-medium transition-colors">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg> 
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-center">
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
                <div class="flex-1 max-w-48">
                    <!-- Added v-model to dynamically sort -->
                    <select v-model="sortBy" id="sort" class="w-full rounded-full border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer hover:border-gray-400 transition-colors">
                        <option value="date">Sort by Date</option>
                        <option value="name">Sort by Name</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-12 lg:px-20 mt-10 md:mt-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 text-[#033E94] dark:text-white">Video</h2>

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
                                <GalleryVideoCard :video="video" @play="toggleModal('video', true)" />
                            </div>
                        </template>
                    </BaseCarousel>
                </div>

                <div v-else class="text-center py-10 md:py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-500">
                    No videos found matching your search.
                </div>
            </div>

            <div v-if="videos.length > 0" class="flex justify-center mt-4 sm:mt-6">
                <button @click="toggleModal('video', true)" class="text-white bg-[#033E94] hover:bg-blue-800 dark:bg-white dark:text-[#033E94] shadow-md rounded-xl font-semibold text-base md:text-lg px-6 md:px-8 py-2.5 transition active:scale-95">
                    View all Videos
                </button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-12 lg:px-20 mt-12 md:mt-16 mb-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 text-[#033E94] dark:text-white">Photo</h2>

            <div v-if="allPhotos.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                
                <GalleryPhotoCard 
                    v-if="highlightPhoto" 
                    :photo="highlightPhoto" 
                    variant="highlight" 
                    @open="toggleModal('photo', true)" 
                />

                <div v-if="gridPhotos.length > 0" class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                    <GalleryPhotoCard 
                        v-for="photo in gridPhotos" 
                        :key="photo.id" 
                        :photo="photo" 
                        variant="grid"
                        @open="toggleModal('photo', true)" 
                    />
                </div>

                <div class="col-span-1 lg:col-span-2 flex justify-center mt-4 sm:mt-6">
                    <button @click="toggleModal('photo', true)" class="text-white bg-[#033E94] hover:bg-blue-800 dark:bg-white dark:text-[#033E94] shadow-md font-semibold rounded-xl text-base md:text-lg px-6 md:px-8 py-2.5 transition active:scale-95">
                        View all Photos
                    </button>
                </div>
            </div>
            
            <div v-else class="text-center py-10 md:py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-500">
                No photos found matching your search.
            </div>
        </div>

        <teleport to="body">
            
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isVideoModalOpen" class="fixed inset-0 z-100 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="toggleModal('video', false)"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8">
                        <div class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl border-t-8 border-[#033E94] flex flex-col max-h-[90vh]">
                            <div class="bg-white px-5 sm:px-6 py-4 sm:py-5 flex justify-between items-center border-b border-gray-100 shrink-0">
                                <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#033E94]">All Videos</h3>
                                <button @click="toggleModal('video', false)" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center transition text-xl sm:text-2xl font-bold">&times;</button>
                            </div>
                            <div class="p-4 sm:p-6 md:p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-8 bg-gray-50 grow">
                                <GalleryVideoCard v-for="video in videos" :key="video.id" :video="video" :isCompact="true" />
                            </div>
                            <div class="bg-white border-t border-gray-100 px-5 sm:px-6 py-3 sm:py-4 flex justify-end shrink-0">
                                <button @click="toggleModal('video', false)" class="bg-[#033E94] text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-xl hover:bg-blue-800 font-semibold transition shadow-sm text-sm sm:text-base">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isPhotoModalOpen" class="fixed inset-0 z-100 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="toggleModal('photo', false)"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8">
                        <div class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl border-t-8 border-[#033E94] flex flex-col max-h-[90vh]">
                            <div class="bg-white px-5 sm:px-6 py-4 sm:py-5 flex justify-between items-center border-b border-gray-100 shrink-0">
                                <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#033E94]">All Photos</h3>
                                <button @click="toggleModal('photo', false)" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center transition text-xl sm:text-2xl font-bold">&times;</button>
                            </div>
                            <div class="p-4 sm:p-6 md:p-8 overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-6 bg-gray-50 grow">
                                <GalleryPhotoCard v-for="photo in allPhotos" :key="photo.id" :photo="photo" variant="modal" />
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