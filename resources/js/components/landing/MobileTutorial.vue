<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import type { TutorialItem } from '@/types/landing/index';

const props = defineProps<{
    items?: TutorialItem[];
}>();

const searchQuery = ref('');
const isVideoModalOpen = ref(false);
const activeVideoUrl = ref<string | null>(null);

// Updated to use the local files from public/assets/Mobile-Tutorial/
const sampleItems: any[] = [
    {
        id: 'v1', type: 'video',
        title: 'How to Download the App',
        subtitle: 'Step 1',
        description: 'Learn where and how to securely download the official mobile app.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/1 How to Download the App.mp4'
    },
    {
        id: 'v2', type: 'video',
        title: 'How to create your fismpc account',
        subtitle: 'Step 2',
        description: 'A step-by-step guide to registering and setting up your new account.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/2 How to create your fismpc account.mp4'
    },
    {
        id: 'v3', type: 'video',
        title: 'How to Log In to Your Account',
        subtitle: 'Step 3',
        description: 'Securely access your account using your credentials.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/3 How to Log In to Your Account.mp4'
    },
    {
        id: 'v4', type: 'video',
        title: 'How to Recover Your Account',
        subtitle: 'Step 4',
        description: 'Forgot your password? Learn how to safely recover your access.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/4 How to Recover Your Account If You Forget Your Password.mp4'
    },
    {
        id: 'v5', type: 'video',
        title: 'How to Complete Your Profile',
        subtitle: 'Step 5',
        description: 'Fill in your necessary details to complete your membership profile.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/5 How to Complete Your Profile.mp4'
    },
    {
        id: 'v6', type: 'video',
        title: 'How to Change Your Profile Picture',
        subtitle: 'Step 6',
        description: 'Personalize your account by updating your profile picture.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/6 How to Change Your Profile Picture.mp4'
    },
    {
        id: 'v7', type: 'video',
        title: 'How to Activate Biometric Authentication',
        subtitle: 'Step 7',
        description: 'Enable Face ID or Fingerprint login for faster, secure access.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/7 How to Activate Biometric Authentication.mp4'
    },
    {
        id: 'v8', type: 'video',
        title: 'How to Become a Cooperative Member',
        subtitle: 'Step 8',
        description: 'Follow the process to officially become a registered cooperative member.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/8 How to Become a Cooperative Member.mp4'
    },
    {
        id: 'v9', type: 'video',
        title: 'Access the Cooperative Membership Module',
        subtitle: 'Step 9',
        description: 'Navigate the membership module to view your cooperative status.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/9 How to access the Cooperative Membership Module.mp4'
    },
    {
        id: 'v10', type: 'video',
        title: 'How to access the Business training',
        subtitle: 'Step 10',
        description: 'Find and enroll in available business training courses.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/10 How to access the Business training.mp4'
    },
    {
        id: 'v11', type: 'video',
        title: 'How to Access News and Events',
        subtitle: 'Step 11',
        description: 'Stay updated with the latest cooperative news and upcoming events.',
        media_path: '/assets/placeholder.jpg',
        video_url: '/assets/Mobile-Tutorial/11 How to Access News and Events.mp4'
    }
];

// --- COMPUTED DATA & FILTERING ---
const processedItems = computed(() => {
    let items = (props.items && props.items.length > 0) ? props.items : sampleItems;

    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();
        items = items.filter(item => {
            const titleMatch = item.title?.toLowerCase().includes(query) ?? false;
            const descMatch = item.description?.toLowerCase().includes(query) ?? false;

            return titleMatch || descMatch;
        });
    }

    return items;
});

const videos = computed(() => processedItems.value.filter(item => item.type === 'video'));

// --- HELPER METHODS ---
const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = '/assets/placeholder.jpg';
};

const openVideoModal = (url?: string) => {
    if (!url) {
        return;
    }
    
    // We no longer need YouTube parameters, just the direct file path
    activeVideoUrl.value = url;
    isVideoModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeVideoModal = () => {
    activeVideoUrl.value = null;
    isVideoModalOpen.value = false;
    document.body.style.overflow = '';
};

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <div id="mobile-tutorial" class="overflow-x-hidden pb-16 bg-white dark:bg-[#0a192f]">
        
        <div class="relative h-64 md:h-80 w-full bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
            <div class="absolute inset-0 flex flex-col items-center justify-center px-4 sm:px-6 text-center">
                <span class="text-[#033E94] dark:text-blue-400 font-semibold tracking-wider text-sm uppercase mb-3">
                    Support & Guides
                </span>
                <h1 class="text-gray-900 dark:text-white text-3xl md:text-5xl font-bold tracking-tight mb-4">
                    Mobile App Tutorials
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm md:text-base max-w-2xl leading-relaxed">
                    Follow our step-by-step tutorials to learn how to use the mobile app, manage your account, access your membership, and get the most out of your experience.
                </p>
            </div>
        </div>

        <!-- Search Bar (Minimal) -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 mt-8 md:mt-12">
            <div class="relative flex items-center bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm focus-within:ring-2 focus-within:ring-[#033E94]/20 transition-all">
                <div class="pl-5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    v-model="searchQuery"
                    class="w-full bg-transparent px-4 py-3.5 text-sm text-gray-700 dark:text-white focus:outline-none border-none placeholder-gray-400" 
                    placeholder="Search tutorials..." 
                />
            </div>
        </div>

        <!-- Looping Video Carousel -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-12 lg:px-20 mt-12">
            <div class="relative w-full">
                <div v-if="videos.length > 0">
                    <!-- autoplayDelay ensures it loops automatically -->
                    <BaseCarousel 
                        :items="videos" 
                        :autoplayDelay="3500" 
                        slide-class="w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1.33rem)]"
                    >                        
                        <template #slide="{ slide: video }">
                            <div class="p-2 h-full w-full">
                                <!-- Notice we swapped video.youtube_url to video.video_url below -->
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-3 h-full flex flex-col group cursor-pointer" @click="openVideoModal(video.video_url || video.youtube_url)">
                                    
                                    <!-- Clean Thumbnail -->
                                    <div class="relative block aspect-video overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-900 w-full mb-4">
                                        <video 
                                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 pointer-events-none" 
                                        :src="`${video.video_url || video.youtube_url}#t=0.1`" 
                                        preload="metadata"
                                        muted
                                        playsinline
                                    ></video>
                                        <!-- Minimal Play Button Overlay -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/30 transition-colors duration-300">
                                            <div class="w-12 h-12 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                                                <svg class="w-5 h-5 text-[#033E94] ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Minimal Text Content -->
                                    <div class="flex flex-col flex-1 px-1">
                                        <span class="text-[11px] font-bold tracking-wider text-gray-400 uppercase mb-1">{{ video.subtitle }}</span>
                                        <h5 class="text-gray-900 dark:text-white text-base font-semibold leading-tight mb-2 group-hover:text-[#033E94] dark:group-hover:text-blue-400 transition-colors">
                                            {{ video.title }}
                                        </h5>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 leading-relaxed">
                                            {{ video.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </BaseCarousel>
                </div>
                
                <div v-else class="text-center py-16 text-gray-400">
                    <p class="text-base">No tutorials found.</p>
                </div>
            </div>
        </div>

        <!-- Clean Pop-up Modal Video Player -->
        <teleport to="body">
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="isVideoModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 md:p-12">
                    
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm cursor-pointer" @click="closeVideoModal"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative w-full max-w-4xl bg-black rounded-xl overflow-hidden shadow-2xl z-10 ring-1 ring-white/10">
                        
                        <!-- Minimal Close Button -->
                        <button @click="closeVideoModal" class="absolute -top-10 right-0 text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium z-20">
                            Close
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>

                        <!-- Local Video Player replacing the iframe -->
                        <div class="aspect-video w-full bg-black flex items-center justify-center">
                            <video 
                                v-if="activeVideoUrl"
                                class="w-full h-full object-contain"
                                controls 
                                autoplay 
                                controlsList="nodownload"
                            >
                                <source :src="activeVideoUrl" type="video/mp4" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>

    </div>
</template>