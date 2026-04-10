<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

const props = defineProps<{
    items: any[];
    autoplayDelay?: number; 
}>();

// --- INFINITE LOOP CLONES ---
// Create 5 identical sets of the items. 
// Set 0 | Set 1 | Set 2 (Center) | Set 3 | Set 4
const infiniteItems = computed(() => {
    if (!props.items || props.items.length === 0) return [];
    return [
        ...props.items,
        ...props.items,
        ...props.items, // We start the user here
        ...props.items,
        ...props.items,
    ];
});

// --- REFS ---
const sliderRef = ref<HTMLElement | null>(null);
let autoplayTimer: ReturnType<typeof setInterval> | null = null;
let scrollTimeout: ReturnType<typeof setTimeout> | null = null;

// --- STATE ---
const isDragging = ref(false);
const isJumping = ref(false); // Prevents transitions during the infinite teleport
const startX = ref(0);
const scrollLeft = ref(0);

// Calculates the exact pixel width of one complete set of original items
const getSingleSetWidth = () => {
    if (!sliderRef.value || props.items.length === 0) return 0;
    const children = sliderRef.value.children;
    if (children.length < props.items.length) return 0;
    
    // Distance between index 0 and the first item of the second set
    const firstItem = children[0] as HTMLElement;
    const secondSetFirstItem = children[props.items.length] as HTMLElement;
    return secondSetFirstItem.offsetLeft - firstItem.offsetLeft;
};

// Teleports the scrollbar instantly without triggering the CSS smooth transition
const jumpTo = async (position: number) => {
    if (!sliderRef.value) return;
    
    isJumping.value = true;
    await nextTick(); // Wait for Vue to strip the `scroll-smooth` classes
    
    const container = sliderRef.value;
    container.style.scrollBehavior = 'auto';
    container.scrollLeft = position;
    
    // Double frame request ensures the DOM paints the jump before restoring transitions
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            isJumping.value = false;
            if (container) container.style.scrollBehavior = '';
        });
    });
};

// The core infinite logic: checks if we've drifted out of the center sets
const checkInfiniteBounds = () => {
    if (!sliderRef.value || isDragging.value) return;
    const container = sliderRef.value;
    const setWidth = getSingleSetWidth();
    
    if (setWidth === 0) return;

    // If drifted too far left (into Set 0 or 1) -> Teleport Right
    if (container.scrollLeft < setWidth * 1.5) {
        jumpTo(container.scrollLeft + setWidth);
    } 
    // If drifted too far right (into Set 3 or 4) -> Teleport Left
    else if (container.scrollLeft >= setWidth * 3.5) {
        jumpTo(container.scrollLeft - setWidth);
    }
};

const handleScroll = () => {
    if (isDragging.value) return;
    if (scrollTimeout) clearTimeout(scrollTimeout);
    
    // Wait for native scroll momentum or snap to settle
    scrollTimeout = setTimeout(() => {
        checkInfiniteBounds();
    }, 150);
};

const scrollNext = () => {
    if (sliderRef.value) {
        sliderRef.value.scrollBy({ left: sliderRef.value.clientWidth, behavior: 'smooth' });
        resetAutoplay();
    }
};

const scrollPrev = () => {
    if (sliderRef.value) {
        sliderRef.value.scrollBy({ left: -sliderRef.value.clientWidth, behavior: 'smooth' });
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

// --- DRAG LOGIC ---
const dragStart = (e: MouseEvent) => {
    if (!sliderRef.value) return;
    isDragging.value = true;
    startX.value = e.pageX - sliderRef.value.offsetLeft;
    scrollLeft.value = sliderRef.value.scrollLeft;
    stopAutoplay(); 
};

const dragStop = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    startAutoplay(); 
    // Verify boundaries in case they dragged violently past the center sets
    setTimeout(checkInfiniteBounds, 150);
};

const dragMove = (e: MouseEvent) => {
    if (!isDragging.value || !sliderRef.value) return;
    e.preventDefault();
    const x = e.pageX - sliderRef.value.offsetLeft;
    const walk = (x - startX.value) * 1.5; // Drag speed multiplier
    sliderRef.value.scrollLeft = scrollLeft.value - walk;
};

// --- INITIALIZATION ---
const initCarousel = async () => {
    await nextTick();
    if (sliderRef.value && props.items.length > 0) {
        setTimeout(() => {
            const setWidth = getSingleSetWidth();
            // Jump exactly to the start of Set 2 (the center block)
            jumpTo(setWidth * 2); 
        }, 100);
    }
};

const handleResize = () => {
    if (scrollTimeout) clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
        const setWidth = getSingleSetWidth();
        if (setWidth > 0) jumpTo(setWidth * 2);
    }, 200);
};

onMounted(() => {
    initCarousel();
    startAutoplay();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    stopAutoplay();
    if (scrollTimeout) clearTimeout(scrollTimeout);
    window.removeEventListener('resize', handleResize);
});
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
                isDragging ? 'cursor-grabbing' : 'cursor-grab',
                (!isDragging && !isJumping) ? 'snap-x snap-mandatory scroll-smooth' : ''
            ]"
            @scroll="handleScroll"
            @mousedown="dragStart"
            @mouseleave="dragStop"
            @mouseup="dragStop"
            @mousemove="dragMove"
        >
            <div 
                v-for="(item, index) in infiniteItems" 
                :key="`slide-${index}`"
                class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] shrink-0"
                :class="(!isDragging && !isJumping) ? 'snap-center' : ''"
            >
                <div :class="{ 'pointer-events-none': isDragging }" class="h-full">
                    <slot name="slide" :slide="item" :index="index % props.items.length"></slot>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.hide-scrollbar {
    -ms-overflow-style: none; 
    scrollbar-width: none;  
}
.hide-scrollbar::-webkit-scrollbar {
    display: none; 
}
</style>