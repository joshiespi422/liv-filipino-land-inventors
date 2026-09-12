<script setup lang="ts">
import type { GalleryItem } from '@/types/landing/index';

defineProps<{
    photo: GalleryItem;
    variant?: 'highlight' | 'grid' | 'modal';
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
    <div v-if="variant === 'highlight'" class="relative w-full aspect-4/3 md:aspect-video lg:aspect-auto lg:h-full group overflow-hidden rounded-2xl shadow-lg cursor-pointer" @click="$emit('open', photo)">
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