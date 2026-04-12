<script setup lang="ts">
import { computed } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';

interface Program {
    title: string;
    subtitle: string;
    image: string;
}

interface SectionData {
    title?: string;
    short_title?: string;
    content?: string;
}

const props = defineProps<{
    programs?: Program[];
    sectionData?: SectionData;
}>();

const samplePrograms: Program[] = [
    {
        title: "Invention Development & Commercialization",
        subtitle: "We help inventors refine their prototypes and provide assistance with intellectual property registration and patent facilitation.",
        image: "/assets/p1.jpg"
    },
    {
        title: "Cooperative Enterprise Development",
        subtitle: "We provide access to cooperative credit and livelihood programs so that innovators can turn their ideas into sustainable businesses.",
        image: "/assets/p2.jpg"
    },
    {
        title: "Research and Innovation Hubs",
        subtitle: "We establish shared laboratories and fabrication centers where members can collaborate, experiment, and build their technologies together.",
        image: "/assets/p3.jpg"
    },
    {
        title: "National Innovation Advocacy",
        subtitle: "We engage with policymakers and run awareness campaigns to promote the importance of invention in national development.",
        image: "/assets/p4.jpg"
    },
    {
        title: "Trade Fairs and Exhibitions",
        subtitle: "We host events like the National Inventors Week to showcase Filipino-made technologies, connect inventors with industry partners, and celebrate our community’s achievements.",
        image: "/assets/p5.jpg"
    }
];

// --- COMPUTED DATA ---
const displayPrograms = computed(() => {
    return (props.programs && props.programs.length > 0)
        ? props.programs
        : samplePrograms;
});

const getImageUrl = (path?: string) => {
    if (!path) return '/assets/placeholder.jpg';
    
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};
</script>

<template>
    <div id="program-services" class="overflow-x-hidden">
        
        <div class="relative bg-[linear-gradient(to_bottom,var(--color-surface)_0_35%,var(--color-primary)_35%_100%)]">
            <img
                src="/assets/productsandservices.jpg"
                alt="Programs and Services"
                class="h-32 sm:h-40 w-full object-cover md:h-56 lg:h-72"
            />
            <div class="absolute inset-0 flex flex-col items-center justify-center px-4 dark:bg-black/30 text-center">
                <h1 class="text-xl font-bold tracking-wider text-white uppercase drop-shadow-xl sm:text-3xl md:text-5xl">
                    {{ sectionData?.title ?? 'PROGRAMS & SERVICES' }}
                </h1>
                <p class="mt-2 max-w-4xl text-xs sm:text-sm md:text-base lg:text-xl font-medium text-white/95 drop-shadow-md">
                    {{
                        sectionData?.short_title ??
                        'FISMPC offers a wide range of programs and services to support our members at every stage of innovation.'
                    }}
                </p>
            </div>
        </div>

        <div class="relative transition-colors duration-500 bg-[linear-gradient(to_bottom,#FFFFFF_0_35%,#033E94_35%_100%)] dark:bg-[#033e94]">
            
            <div class="relative z-30 pt-4 sm:pt-6 pb-6 flex items-center justify-center max-w-7xl mx-auto px-2 md:px-8 gap-2 md:gap-4 lg:gap-8">
                
                <BaseCarousel :items="displayPrograms" :autoplayDelay="5000" class="w-full min-w-0">
                    
                    <template #prev-arrow>
                        <div class="rounded-full border border-gray-300 bg-white transition-all hover:border-gray-400 active:scale-95 cursor-pointer flex items-center justify-center">
                            <img
                                src="/assets/leftarrow.png"
                                class="h-10 w-10 md:h-12 md:w-12 rounded-full transition-colors"
                                alt="Prev"
                            />
                        </div>
                    </template>

                    <template #next-arrow>
                        <div class="rounded-full border border-gray-300 bg-white transition-all hover:border-gray-400 active:scale-95 cursor-pointer flex items-center justify-center">
                            <img
                                src="/assets/rightarrow.png"
                                class="h-10 w-10 md:h-12 md:w-12 rounded-full transition-colors"
                                alt="Next"
                            />
                        </div>
                    </template>

                    <template #slide="{ slide: program }">
                        <div class="flex h-[360px] sm:h-[400px] md:h-[420px] cursor-pointer flex-col overflow-hidden rounded-2xl md:rounded-3xl border border-gray-100 bg-white transition-all duration-500 ease-out transform scale-95 shadow-md hover:scale-100 md:hover:scale-105 hover:z-20 hover:ring-4 hover:ring-[#033E94]/30 hover:shadow-2xl mx-1 my-2">
                            
                            <div class="h-40 sm:h-48 md:h-52 w-full overflow-hidden shrink-0">
                                <img
                                    class="h-full w-full object-cover transition-transform duration-700 hover:scale-110"
                                    :src="getImageUrl(program.image)"
                                    :alt="program.title"
                                />
                            </div>

                            <div class="flex flex-1 flex-col justify-start p-4 md:p-6">
                                <h5 class="mb-2 line-clamp-2 text-base sm:text-lg lg:text-xl leading-snug font-extrabold text-[#033E94] uppercase">
                                    {{ program.title }}
                                </h5>
                                <p class="line-clamp-4 md:line-clamp-5 text-xs sm:text-sm md:text-base leading-relaxed text-gray-600">
                                    {{ program.subtitle }}
                                </p>
                            </div>

                        </div>
                    </template>

                </BaseCarousel>
            </div>

            <div class="relative z-30 px-4 sm:px-6 pb-8 md:pb-12 max-w-6xl mx-auto">
                <p class="text-center text-xs sm:text-sm md:text-lg leading-relaxed font-light text-white/95 italic">
                    "{{
                        sectionData?.content ??
                        'Through these programs, FISMPC ensures that the resources needed for invention – from training and mentorship to funding and market access – are available to our members. We also work closely with partners such as the Department of Science and Technology, DTI, the Intellectual Property Office, and various universities to deliver these services. Our cooperative model ensures that the benefits of innovation are shared equitably among all members and communities.'
                    }}"
                </p>
            </div>

        </div>
    </div>
</template>