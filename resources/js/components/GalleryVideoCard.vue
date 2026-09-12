<script setup lang="ts">
import type { GalleryItem } from '@/types/landing/index';

defineProps<{
    video: GalleryItem;
    isCompact?: boolean;
}>();

defineEmits(['play']);

const getImageUrl = (path?: string) => {
    if (!path) return '/assets/placeholder.jpg';
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = '/assets/placeholder.jpg';
};
</script>

<template>
    <div :class="isCompact ? 
        'bg-white rounded-xl sm:rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100 flex flex-col group h-full' : 
        'bg-white rounded-2xl md:rounded-3xl shadow-md hover:shadow-xl transform transition duration-300 hover:-translate-y-1 p-4 sm:p-5 h-full border border-gray-100 flex flex-col w-full'">
        
        <a href="#" @click.prevent="$emit('play', video)" 
           :class="isCompact ? 
           'relative aspect-video shrink-0 bg-gray-200 overflow-hidden cursor-pointer' : 
           'relative block aspect-video overflow-hidden rounded-xl md:rounded-2xl shadow-sm shrink-0 bg-gray-100 group'">
            <img 
                :class="isCompact ? 'absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500' : 'absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105'" 
                :src="getImageUrl(video.media_path)" 
                @error="handleImageError" 
                :alt="video.title" 
            />
            <div :class="isCompact ? 'absolute inset-0 bg-black/20 group-hover:bg-black/10 transition' : 'absolute inset-0 bg-black/10 group-hover:bg-black/0 transition duration-300'"></div>
            <img 
                :class="isCompact ? 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-10 h-10 sm:w-14 sm:h-14 object-contain opacity-90 drop-shadow-lg group-hover:scale-110 transition duration-300' : 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 md:w-14 md:h-14 object-contain drop-shadow-lg transition-transform duration-300 group-hover:scale-110'" 
                src="/assets/video.png" 
                alt="Play" 
            />
        </a>

        <div :class="isCompact ? 'p-4 sm:p-5 flex-1 flex flex-col' : 'mt-4 sm:mt-5 flex flex-col flex-1'">
            <div class="flex justify-between items-start gap-3 mb-2">
                <h4 v-if="isCompact" class="font-bold text-[#033E94] text-base sm:text-lg line-clamp-2 leading-tight">{{ video.title }}</h4>
                <h5 v-else class="text-[#033E94] text-base sm:text-lg lg:text-xl font-bold line-clamp-2 leading-tight">{{ video.title }}</h5>
                <span :class="isCompact ? 'text-xs font-medium text-gray-500 shrink-0 mt-1' : 'text-xs sm:text-sm font-medium text-gray-500 shrink-0 mt-1'">
                    {{ video.subtitle }}
                </span>
            </div>
            <p :class="isCompact ? 'text-xs sm:text-sm text-gray-600 line-clamp-3' : 'text-gray-600 line-clamp-3 text-xs sm:text-sm leading-relaxed'">
                {{ video.description }}
            </p>
        </div>
    </div>
</template>