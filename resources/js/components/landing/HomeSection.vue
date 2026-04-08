<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    sectionData: {
        type: Object,
        default: () => null
    }
});

const bgImage = computed(() => {
    return props.sectionData?.image_path 
        ? `/storage/${props.sectionData.image_path}` 
        : '/assets/bg1.jpg';
});

// Store default fallback texts
const defaultTitle = 'FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE';
const defaultShortTitle = 'FISMPC';
const defaultContent = `Welcome to the Filipino Inventors Society Multi-Purpose Cooperative (FISMPC)! We are a dynamic community of visionary inventors, innovators, scientists, and entrepreneurs working together to transform Filipino ingenuity into engines of inclusive national development. Since our founding in 2011, FISMPC has become a recognized platform for innovation commercialization, bridging the gap between creative ideas and real-world solutions. 

Our members have developed technologies in fields like agriculture, renewable energy, education, and digital transformation. Through a decade of service and advocacy, FISMPC stands as a symbol of Filipino resilience and creativity, proving that innovation, when nurtured through cooperation, can make the Philippines great again.`;
</script>

<template>
    <div id="home" class="relative w-full overflow-hidden rounded-b-[10%] lg:rounded-b-[15%] flex items-center justify-center min-h-[85vh] lg:min-h-screen pt-32 md:pt-40 pb-16 md:pb-24">
        
        <div class="absolute inset-0 z-0">
            <img 
                :src="bgImage" 
                alt="Hero Background" 
                class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 bg-black/40 dark:bg-black/60"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center px-4 sm:px-8 md:px-12 text-center w-full max-w-7xl mx-auto">
            
            <h1 class="text-white text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold drop-shadow-2xl mb-6 leading-tight max-w-5xl">
                {{ sectionData?.title || defaultTitle }}
                <span class="text-[#FFCC00] block mt-2 sm:mt-4 text-2xl sm:text-3xl lg:text-4xl">
                    ({{ sectionData?.short_title || defaultShortTitle }})
                </span>
            </h1>

            <p class="text-white text-sm sm:text-base md:text-lg lg:text-xl drop-shadow-lg font-medium opacity-95 leading-relaxed whitespace-pre-wrap max-w-5xl mx-auto">
                {{ sectionData?.content || defaultContent }}
            </p>
            
        </div>
    </div>
</template>