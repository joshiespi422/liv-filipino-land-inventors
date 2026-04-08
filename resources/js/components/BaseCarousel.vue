<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    items: any[];
    autoplayDelay?: number; // E.g., 4000 for 4 seconds. Leave undefined for no autoplay.
}>();

// --- REFS ---
const sliderRef = ref<HTMLElement | null>(null);
let autoplayTimer: ReturnType<typeof setInterval> | null = null;

// --- DRAG TO SCROLL STATE ---
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);

// --- SCROLL LOGIC ---
const scrollNext = () => {
    if (sliderRef.value) {
        const maxScroll = sliderRef.value.scrollWidth - sliderRef.value.clientWidth;
        // Loop back to start if at the end, otherwise scroll right
        if (sliderRef.value.scrollLeft >= maxScroll - 10) {
            sliderRef.value.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            sliderRef.value.scrollBy({ left: sliderRef.value.clientWidth, behavior: 'smooth' });
        }
        resetAutoplay();
    }
};

const scrollPrev = () => {
    if (sliderRef.value) {
        // Jump to end if at the start, otherwise scroll left
        if (sliderRef.value.scrollLeft <= 10) {
            sliderRef.value.scrollTo({ left: sliderRef.value.scrollWidth, behavior: 'smooth' });
        } else {
            sliderRef.value.scrollBy({ left: -sliderRef.value.clientWidth, behavior: 'smooth' });
        }
        resetAutoplay();
    }
};

// --- AUTOPLAY LOGIC ---
const startAutoplay = () => {
    if (props.autoplayDelay && props.items.length > 1) {
        autoplayTimer = setInterval(() => {
            if (!isDragging.value) scrollNext();
        }, props.autoplayDelay);
    }
};

const stopAutoplay = () => {
    if (autoplayTimer) clearInterval(autoplayTimer);
};

const resetAutoplay = () => {
    stopAutoplay();
    startAutoplay();
};

// --- DRAG EVENT HANDLERS ---
const dragStart = (e: MouseEvent) => {
    if (!sliderRef.value) return;
    isDragging.value = true;
    startX.value = e.pageX - sliderRef.value.offsetLeft;
    scrollLeft.value = sliderRef.value.scrollLeft;
    stopAutoplay(); // Pause autoplay while interacting
};

const dragStop = () => {
    isDragging.value = false;
    startAutoplay(); // Resume autoplay when done
};

const dragMove = (e: MouseEvent) => {
    if (!isDragging.value || !sliderRef.value) return;
    e.preventDefault(); // Prevents text selection while dragging
    const x = e.pageX - sliderRef.value.offsetLeft;
    const walk = (x - startX.value) * 2; // Scroll-fast multiplier
    sliderRef.value.scrollLeft = scrollLeft.value - walk;
};

// --- LIFECYCLE ---
onMounted(() => startAutoplay());
onUnmounted(() => stopAutoplay());
</script>

<template>
    <div 
        class="relative w-full group/carousel"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        
        <button 
            @click="scrollPrev" 
            class="absolute -left-4 md:-left-6 lg:-left-12 top-1/2 -translate-y-1/2 z-20 shrink-0 transition-transform active:scale-95 hidden md:flex items-center justify-center focus:outline-none"
            aria-label="Previous Slide"
        >
            <slot name="prev-arrow">
                <div class="bg-white rounded-full shadow-lg p-3 hover:bg-gray-100 text-[#033E94]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>
            </slot>
        </button>

        <button 
            @click="scrollNext" 
            class="absolute -right-4 md:-right-6 lg:-right-12 top-1/2 -translate-y-1/2 z-20 shrink-0 transition-transform active:scale-95 hidden md:flex items-center justify-center focus:outline-none"
            aria-label="Next Slide"
        >
            <slot name="next-arrow">
                <div class="bg-white rounded-full shadow-lg p-3 hover:bg-gray-100 text-[#033E94]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </slot>
        </button>

        <div 
            ref="sliderRef"
            class="flex overflow-x-auto hide-scrollbar gap-4 md:gap-6 lg:gap-8 pb-8 pt-4 px-2"
            :class="[
                isDragging ? 'cursor-grabbing scroll-auto' : 'cursor-grab snap-x snap-mandatory scroll-smooth'
            ]"
            @mousedown="dragStart"
            @mouseleave="dragStop"
            @mouseup="dragStop"
            @mousemove="dragMove"
        >
            <div 
                v-for="(item, index) in items" 
                :key="index"
                class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] shrink-0"
                :class="!isDragging ? 'snap-center' : ''"
            >
                <div :class="{ 'pointer-events-none': isDragging }" class="h-full">
                    <slot name="slide" :slide="item" :index="index"></slot>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Hides default scrollbar across different browsers */
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.hide-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}
</style>