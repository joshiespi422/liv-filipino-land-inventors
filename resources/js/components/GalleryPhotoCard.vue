<script setup lang="ts">
import type { GalleryItem } from '@/types/landing/index';

defineProps<{
    photo: GalleryItem;
    variant?: 'highlight' | 'grid' | 'modal' | 'list'; // Added 'list'
}>();

defineEmits(['open']);

const getImageUrl = (path?: string) => {
    if (!path) {
        return '/assets/placeholder.jpg';
}

    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = '/assets/placeholder.jpg';
};
</script>

<template>
    <!-- LIST VIEW (Horizontal Row) -->
    <div v-if="variant === 'list'" 
         @click="$emit('open', photo)"
         class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-200 flex flex-row p-3 gap-4 items-center group cursor-pointer w-full">
        
        <div class="relative w-20 h-20 sm:w-28 sm:h-28 shrink-0 rounded-lg overflow-hidden bg-gray-100 border border-gray-100">
            <img :src="getImageUrl(photo.media_path)" @error="handleImageError" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" :alt="photo.title || 'Photo'">
        </div>
        
        <div class="flex-1 min-w-0 flex flex-col justify-center py-1">
            <h4 class="font-bold text-[#033E94] text-base sm:text-lg truncate">{{ photo.title || 'Gallery Photo' }}</h4>
            <span v-if="photo.subtitle" class="text-xs sm:text-sm font-medium text-gray-500 mt-1">{{ photo.subtitle }}</span>
            <p v-if="photo.description" class="text-xs sm:text-sm text-gray-600 mt-1 line-clamp-1 sm:line-clamp-2">{{ photo.description }}</p>
        </div>
    </div>

    <!-- Highlight Variant -->
    <div v-else-if="variant === 'highlight'" class="relative w-full aspect-4/3 md:aspect-video lg:aspect-auto lg:h-full group overflow-hidden rounded-2xl shadow-lg cursor-pointer" @click="$emit('open', photo)">
        <img :src="getImageUrl(photo.media_path)" @error="handleImageError" alt="Highlight" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
        <div class="absolute top-4 left-0 bg-linear-to-r from-yellow-500 to-yellow-600 text-white px-4 sm:px-6 py-1.5 sm:py-2 rounded-r-full text-base sm:text-lg md:text-xl font-bold uppercase shadow-md">
            HIGHLIGHTS
        </div>
    </div>

    <!-- Modal Variant -->
    <div v-else-if="variant === 'modal'" class="relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl shadow-sm hover:shadow-lg transition bg-gray-200 group cursor-pointer" @click="$emit('open', photo)">
        <img :src="getImageUrl(photo.media_path)" @error="handleImageError" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-500">
    </div>

    <!-- Grid Variant (Default) -->
    <div v-else class="relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl shadow-md group cursor-pointer" @click="$emit('open', photo)">
        <img :src="getImageUrl(photo.media_path)" @error="handleImageError" alt="Gallery Photo" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
    </div>
</template>