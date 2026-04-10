<script setup lang="ts">
import { computed } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';

// --- INTERFACES ---
interface Testimonial {
    image_path?: string;
    name?: string;
    role?: string;
    body?: string;
}

const props = defineProps<{
    testimonials?: Testimonial[];
}>();

const sampleTestimonials: Testimonial[] = [
    {
        name: "Marisol D.",
        role: "Teacher & FISMPC Member",
        body: "Joining FISMPC was the turning point for my invention. The mentorship and funding I received helped me refine my solar irrigation prototype and bring it to rural farmers. Now it’s making a real impact in our community!",
        image_path: "/assets/marisol.png"
    },
    {
        name: "Juan Carlos R.",
        role: "Renewable Energy Innovator",
        body: "Thanks to FISMPC’s training programs, I was able to file a patent for my low-cost water purifier and connect with manufacturing partners. Their support opened doors I never knew existed.",
        image_path: "/assets/juan.png"
    },
    {
        name: "Lani P.",
        role: "Tech Startup Co-founder",
        body: "FISMPC is like a family of inventors. We share ideas, resources, and celebrate each other’s successes. At Inventors Demo Day, our team even found an investor who believes in our project!",
        image_path: "/assets/lani.png"
    },
    {
        name: "Miguel S.",
        role: "Agritech Entrepreneur",
        body: "Our cooperative has truly empowered me. From workshops on business planning to access to cooperative loans, FISMPC has provided everything I needed to turn my prototype into a social enterprise.",
        image_path: "/assets/miguel.png"
    }
];

// --- COMPUTED DATA ---
const displayTestimonials = computed(() => {
    return (props.testimonials && props.testimonials.length > 0) 
        ? props.testimonials 
        : sampleTestimonials;
});

const getImageUrl = (path?: string) => {
    if (!path) return '/assets/marisol.png';
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};
</script>

<template>
    <div id="testimonials" class="overflow-x-hidden pb-8 md:pb-12">
        
        <div class="relative">
            <img src="/assets/gallery.jpg" alt="Gallery background" class="w-full h-48 md:h-64 lg:h-72 object-cover">
            <div class="absolute inset-0 flex items-start justify-center text-center pt-10 md:pt-16 bg-black/20">
                <h1 class="text-2xl md:text-4xl lg:text-5xl font-bold drop-shadow-md text-white tracking-widest">
                    TESTIMONIALS
                </h1>
            </div>
        </div>
  
        <div class="relative -mt-12 md:-mt-20 lg:-mt-24 w-full max-w-[1440px] mx-auto px-4 md:px-16 lg:px-24 z-10">
            
            <div v-if="displayTestimonials.length > 0" class="w-full min-w-0 py-4">
                
                <BaseCarousel 
                    :items="displayTestimonials" 
                    :autoplayDelay="4000"
                    slide-class="w-full md:w-[calc(50%-15px)] lg:w-[calc(50%-22.5px)] xl:w-[calc(33.333%-30px)]"
                >
                    
                    <template #prev-arrow>
                        <div class="bg-white rounded-full shadow-lg hover:bg-gray-100 cursor-pointer transition-all active:scale-95 flex items-center justify-center p-1 sm:p-2">
                            <img 
                                src="/assets/leftarrow.png" 
                                alt="Previous" 
                                class="w-6 h-6 sm:w-8 sm:h-8 lg:w-12 lg:h-12 hover:bg-blue-600 rounded-full transition-colors" 
                            />
                        </div>
                    </template>

                    <template #next-arrow>
                        <div class="bg-white rounded-full shadow-lg hover:bg-gray-100 cursor-pointer transition-all active:scale-95 flex items-center justify-center p-1 sm:p-2">
                            <img 
                                src="/assets/rightarrow.png" 
                                alt="Next" 
                                class="w-6 h-6 sm:w-8 sm:h-8 lg:w-12 lg:h-12 hover:bg-blue-600 rounded-full transition-colors" 
                            />
                        </div>
                    </template>

                    <template #slide="{ slide: item }">
                        <div class="flex flex-col sm:flex-row bg-white p-6 lg:p-8 rounded-3xl shadow-lg w-full h-full transition duration-300 hover:-translate-y-1 hover:shadow-xl border border-gray-100 box-border text-center sm:text-left items-center sm:items-start mx-auto my-2">
                            
                            <div class="flex flex-col justify-start items-center sm:w-1/3 shrink-0 mb-4 sm:mb-0 sm:mr-6 relative">
                                <img 
                                    class="object-cover w-20 h-20 md:w-24 md:h-24 aspect-square rounded-full shadow-md border-4 border-white z-10"
                                    :src="getImageUrl(item.image_path)" 
                                    :alt="item.name"
                                >
                                <div class="text-[5rem] md:text-[6rem] lg:text-[7rem] font-black text-[#D70328] mt-[-10px] sm:mt-[-25px] rotate-180 leading-[0.7] opacity-90">
                                    "
                                </div>
                            </div>

                            <div class="flex flex-col sm:w-2/3 leading-relaxed justify-center h-full">
                                <h5 class="text-lg md:text-xl lg:text-2xl font-bold text-[#033E94]">
                                    {{ item.name }}
                                </h5>
                                <i class="text-sm md:text-base text-blue-800 mt-1 font-semibold">
                                    {{ item.role }}
                                </i>
                                <p class="mt-3 md:mt-4 text-sm md:text-base text-gray-700 italic">
                                    "{{ item.body }}"
                                </p>
                            </div>

                        </div>
                    </template>

                </BaseCarousel>
            </div>

            <div v-else class="bg-white p-6 rounded-2xl shadow-md mt-12 mb-8 w-full text-center border border-gray-100">
                <p class="text-gray-600 font-medium">No testimonials found.</p>
            </div>

        </div>
    </div>
</template>