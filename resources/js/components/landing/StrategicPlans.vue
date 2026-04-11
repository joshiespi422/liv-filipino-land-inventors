<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue'; 

interface Plan {
    title: string;
    acronym: string;
    goal: string;
    description: string;
    key_actions: string[];
    expected_outcome: string;
}

interface SectionData {
    title?: string;
    short_title?: string;
    content?: string;
    image_path?: string;
}

const props = defineProps<{
    plans?: Plan[];
    sectionData?: SectionData;
}>();

const samplePlans: Plan[] = [
    {
        title: "Philippine Inventors Enterprise Accelerator",
        acronym: "PIEA",
        goal: "A national incubation program that turns member-inventors’ prototypes into market-ready products.",
        description: "A comprehensive program designed to support inventors by providing shared fabrication facilities, technical mentorship, and prototype testing.",
        key_actions: [
            "Mentorship and technical training with DOST and academic partners",
            "Product testing, certification, and packaging design",
            "Equity-based cooperative funding for promising inventions",
            "Annual 'Inventors Demo Day' to connect inventors with investors"
        ],
        expected_outcome: "30 inventions commercialized per year; 100+ MSMEs supported in scaling operations."
    },
    {
        title: "National Innovation & Cooperative Commercial Hub",
        acronym: "NICCH",
        goal: "Establish a centralized marketplace and innovation hub for Filipino inventors.",
        description: "A hybrid physical-digital hub in Quezon City serving as a one-stop center for invention showcasing, IP support, and technology transfer.",
        key_actions: [
            "Showroom for Filipino-made technologies and products",
            "Co-working space and fabrication lab",
            "IP registration and legal assistance desk",
            "Online platform for e-commerce and investor engagement"
        ],
        expected_outcome: "Increased national visibility for Filipino inventions; enhanced B2B and cooperative-to-government transactions."
    },
    {
        title: "Green Innovation and Renewable Systems Program",
        acronym: "GIRS",
        goal: "Promote eco-sustainable inventions addressing climate change, agriculture, and rural development.",
        description: "FISMPC will prioritize inventions focused on renewable energy, waste recycling, smart agriculture, and green technologies.",
        key_actions: [
            "Partnerships with LGUs for local green tech adoption",
            "Solar microgrid and low-cost water purification systems",
            "Promotion of biodegradable and sustainable packaging solutions"
        ],
        expected_outcome: "50 barangay-level installations of green inventions; recognition as the Philippines’ leading green cooperative."
    },
    {
        title: "Cooperative Investment and Funding Program",
        acronym: "CIFP",
        goal: "Strengthen the cooperative’s financial sustainability and provide accessible capital to inventors.",
        description: "A revolving fund and investment program sourced from cooperative members, CSR partners, and institutional investors.",
        key_actions: [
            "Loan facilities for product development and patent filing",
            "Equity participation in scalable invention enterprises",
            "Partnership with cooperative banks and fintech platforms"
        ],
        expected_outcome: "₱100 million cooperative fund pool by 2027; 300+ inventors receiving seed or expansion support."
    },
    {
        title: "Digital Cooperative Transformation Initiative",
        acronym: "DCTI",
        goal: "Digitize all cooperative processes and empower inventors to thrive in the Web3 and AI era.",
        description: "Launch of FISMPC’s Digital Member Portal and integration of blockchain-based IP timestamping for inventor protection.",
        key_actions: [
            "Digital Member Portal for registration, payments, and e-learning",
            "Blockchain IP timestamping system",
            "AI-driven design, prototyping, and e-commerce partnerships."
        ],
        expected_outcome: "100% digital membership database by 2027; improved transparency and efficiency in cooperative operations."
    },
    {
        title: "Inventors Education and Training Program",
        acronym: "IETP",
        goal: "Equip inventors, students, and MSMEs with technical and entrepreneurial skills.",
        description: "Capacity-building programs and workshops nationwide with partner agencies and universities.",
        key_actions: [
            "InventPreneur Bootcamp on business planning and scaling",
            "IP 101, Patent Filing, and Industrial Design Workshops",
            "TESDA and DOST-linked technical courses"
        ],
        expected_outcome: "1,000 trained inventors nationwide; 50 new IP filings annually supported by FISMPC"
    },
    {
        title: "National Inventors Week and Innovation Awards",
        acronym: "NIWIA",
        goal: "Promote Filipino innovation culture and recognize excellence in invention.",
        description: "Annual celebration and exhibit of Filipino inventions with awards for outstanding innovators.",
        key_actions: [
            "National exhibit and showcase",
            "Youth inventor awards and cooperative innovation recognition",
            "Networking events and media exposure"
        ],
        expected_outcome: "10,000 participants yearly; national recognition for FISMPC members."
    },
    {
        title: "Regional Innovation Cooperative Network",
        acronym: "RICN",
        goal: "Decentralize invention support through regional cooperative chapters.",
        description: "Establish regional innovation clusters across Luzon, Visayas, and Mindanao.",
        key_actions: [
            "Regional partnerships with universities and LGUs",
            "Mentorship and incubation at local levels",
            "Access to cooperative credit and resources"
        ],
        expected_outcome: "15 regional cooperative partnerships; expanded grassroots innovation support."
    }
];

// --- COMPUTED DATA ---
const displayPlans = computed(() => {
    return (props.plans && props.plans.length > 0) ? props.plans : samplePlans;
});

// --- MODAL STATE & METHODS ---
const isModalOpen = ref(false);

const openStrategicModal = () => {
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
};

const closeStrategicModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = ''; // Restore background scrolling
};

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <div id="strategic-plans">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-center my-8 text-[#033E94] dark:text-white px-4">
            {{ sectionData?.title ?? 'STRATEGIC PLANS' }} 
            <span class="text-[#D70328] dark:text-white">{{ sectionData?.short_title ?? '2026-2028' }}</span>
        </h1>
        
        <p class="text-primary text-sm sm:text-base md:text-lg max-w-md sm:max-w-2xl md:max-w-3xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl mx-auto text-center leading-relaxed drop-shadow mb-6 px-6">
            <template v-if="sectionData?.content">
                {{ sectionData.content }}
            </template>
            <template v-else>
                Looking ahead to 2026–2028, FISMPC has set an ambitious strategic agenda to make Filipino inventions drivers of national productivity, global competitiveness, and cooperative prosperity. Our strategy is built on four pillars: 
                <span class="font-bold">Innovation Commercialization & Technology Transfer; Sustainable Cooperative Enterprise & Green Manufacturing;</span> 
                <span class="font-bold">Digital Transformation & Inclusive Market Access;</span> 
                and <span class="font-bold">Capacity Building & Policy Advocacy for Inventors.</span>
            </template>
        </p>

        <div 
            style="background-image: url('/assets/news.jpg')" 
            class="h-auto px-4 sm:px-8 md:px-[8%] xl:px-[14%] bg-cover bg-center relative shadow-lg"
        >
            <div class="relative w-full h-auto py-10">
                
                <BaseCarousel 
                    :items="displayPlans" 
                    :autoplayDelay="10000" 
                    class="w-full"
                    slide-class="w-full"
                >
                    
                    <template #prev-arrow>
                        <div class="hidden md:flex absolute inset-y-0 left-0 items-center z-20 pointer-events-none">
                            <button class="bg-white w-12 rounded-full shadow-md pointer-events-auto hover:bg-gray-100 transition-colors">
                                <img src="/assets/leftarrow.png" class="h-12 w-12 sm:h-10 sm:w-10 md:h-12 md:w-12" alt="Previous" />
                            </button>
                        </div>
                    </template>

                    <template #next-arrow>
                        <div class="hidden md:flex absolute inset-y-0 right-0 items-center z-20 pointer-events-none">
                            <button class="bg-white w-12 rounded-full shadow-md pointer-events-auto hover:bg-gray-100 transition-colors">
                                <img src="/assets/rightarrow.png" class="h-12 w-12 sm:h-10 sm:w-10 md:h-12 md:w-12" alt="Next" />
                            </button>
                        </div>
                    </template>

                    <template #slide="{ slide: plan, index }">
                        <div class="px-2 sm:px-5">
                            <div class="item relative w-full h-auto bg-white/85 dark:bg-slate-900/85 backdrop-blur-md rounded-2xl pt-24 pb-8 px-6 sm:px-10 flex flex-col gap-6 shadow-xl border border-white/30 min-h-125">
                                
                                <div class="absolute top-6 left-0 -ml-2 sm:-ml-4 z-10 w-[95%] sm:w-max">
                                    <div class="absolute inset-y-0 left-0 -right-10 sm:-right-14 bg-gray-300 rounded-br-[40px]"></div>
                                    <div class="absolute inset-y-0 left-0 -right-5 sm:-right-7 bg-[#829FCA] rounded-br-[40px]"></div>
                                    <div class="relative bg-[#033E94] text-white px-4 sm:px-6 py-2 lg:py-2.5 rounded-br-[40px] text-lg sm:text-xl font-bold w-full sm:w-fit whitespace-normal sm:whitespace-nowrap flex items-start sm:items-center">
                                        <span class="mr-2 mt-1 sm:mt-0">{{ index + 1 }}.</span>
                                        <span>{{ plan.title }} <span class="text-[#FFCC00]">({{ plan.acronym }})</span></span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="relative -ml-10 sm:-ml-14 mb-3 bg-[#D70328] px-4 sm:px-6 py-2 rounded-br-[40px] text-white text-lg sm:text-xl font-bold w-max shadow-md">
                                        <h3>Goal:</h3>
                                    </div>
                                    <p class="px-2 text-gray-800 dark:text-gray-200 font-medium text-sm sm:text-base">{{ plan.goal }}</p>
                                </div>

                                <div>
                                    <div class="relative -ml-10 sm:-ml-14 mb-3 bg-[#D70328] px-4 sm:px-6 py-2 rounded-br-[40px] text-white text-lg sm:text-xl font-bold w-max shadow-md">
                                        <h3>Description:</h3>
                                    </div>
                                    <p class="px-2 text-gray-700 dark:text-gray-300 text-sm sm:text-lg">{{ plan.description }}</p>
                                </div>

                                <div>
                                    <h4 class="px-2 mb-3 text-lg sm:text-xl font-semibold text-[#033E94] dark:text-blue-300">Key Activities:</h4>
                                    <div class="space-y-3 px-2 sm:px-8">
                                        <div v-for="(action, aIndex) in plan.key_actions" :key="aIndex" class="flex items-start gap-3">
                                            <svg class="h-5 w-5 sm:h-6 sm:w-6 shrink-0 mt-1" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" fill="#16a34a" />
                                                <path d="M8.5 12.5l2.5 2.5 4.5-5.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="text-sm sm:text-lg leading-relaxed text-gray-700 dark:text-gray-300">{{ action }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4">
                                    <div class="relative -ml-10 sm:-ml-14 mb-3 bg-[#D70328] px-4 sm:px-6 py-2 rounded-br-[40px] text-white text-lg sm:text-xl font-bold w-max shadow-md">
                                        <h3>Expected Outcome:</h3>
                                    </div>
                                    <p class="px-2 text-gray-800 dark:text-gray-200 italic font-medium text-sm sm:text-base">{{ plan.expected_outcome }}</p>
                                </div>

                            </div>
                        </div>
                    </template>
                </BaseCarousel>

                <div class="flex justify-center pb-5 mt-8 relative z-20">
                    <button @click="openStrategicModal" class="text-[#033E94] hover:text-white bg-white hover:bg-[#033E94] border-2 border-[#033E94] shadow-lg rounded-xl text-lg sm:text-xl font-medium px-8 py-3 cursor-pointer transition-colors">
                        View all
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-surface-alt shadow-md px-6 sm:px-10 p-6 sm:p-8">
            <p class="text-text-primary text-sm sm:text-base leading-relaxed text-center xl:text-xl w-full max-w-6xl mx-auto">
                {{ sectionData?.image_path ?? 'Together, these strategic programs ensure that FISMPC continues to empower Filipino inventors and drive sustainable, inclusive innovation across the country.' }}
            </p>
        </div>

        <teleport to="body">
            <transition 
                enter-active-class="transition ease-out duration-300" 
                enter-from-class="opacity-0" 
                enter-to-class="opacity-100" 
                leave-active-class="transition ease-in duration-200" 
                leave-from-class="opacity-100" 
                leave-to-class="opacity-0"
            >
                <div v-if="isModalOpen" id="strategicModal" class="fixed inset-0 z-100 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    <div class="fixed inset-0 dark:bg-black/60 backdrop-blur-sm transition-opacity" @click="closeStrategicModal"></div>

                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-2xl bg-white  text-left shadow-2xl transition-all sm:my-8 w-full max-w-6xl flex flex-col max-h-[90vh]">
                            
                            <div class="bg-[#033E94] px-6 py-4 flex justify-between items-center shrink-0">
                                <h3 class="text-xl sm:text-2xl font-bold text-white" id="modal-title">
                                    All Strategic Plans <span class="text-[#FFCC00] dark:text-white">(2026-2028)</span>
                                </h3>
                                <button @click="closeStrategicModal" class="text-white hover:text-[#FFCC00] transition-colors focus:outline-none">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-6 sm:p-8 overflow-y-auto bg-gray-50 dark:bg-slate-900/85 grow">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div v-for="(plan, index) in displayPlans" :key="index" class="bg-white dark:bg-slate-700/85 p-6 rounded-xl shadow-md border-t-4 border-[#FFCC00] hover:shadow-lg transition-shadow">
                                        
                                        <div class="flex items-start mb-5">
                                            <span class="bg-[#033E94] text-white  px-3 py-1 rounded-lg text-sm font-bold mr-3 mt-1">{{ index + 1 }}</span>
                                            <h4 class="text-xl font-bold text-[#033E94] dark:text-white leading-tight">{{ plan.title }} <span class="text-[#D70328] dark:text-white">({{ plan.acronym }})</span></h4>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <strong class="text-xs sm:text-sm uppercase tracking-wider text-[#D70328] dark:text-white block mb-1">Goal</strong>
                                                <p class="text-gray-800 dark:text-white text-sm md:text-base font-medium">{{ plan.goal }}</p>
                                            </div>
                                            
                                            <div>
                                                <strong class="text-xs sm:text-sm uppercase tracking-wider text-[#D70328] dark:text-white block mb-1">Description</strong>
                                                <p class="text-gray-700 dark:text-white text-sm md:text-base">{{ plan.description }}</p>
                                            </div>

                                            <div>
                                                <strong class="text-xs sm:text-sm uppercase tracking-wider text-[#D70328] dark:text-white block mb-2">Key Activities</strong>
                                                <ul class="space-y-2">
                                                    <li v-for="(action, aIndex) in plan.key_actions" :key="aIndex" class="flex items-start gap-2 text-sm md:text-base text-gray-700">
                                                        <svg class="h-5 w-5 shrink-0 text-green-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="dark:text-white">{{ action }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div class="pt-3 border-t border-gray-100">
                                                <strong class="text-xs sm:text-sm uppercase tracking-wider text-[#D70328] dark:text-white block mb-1">Expected Outcome</strong>
                                                <p class="text-gray-800 dark:text-white font-medium text-sm md:text-base italic">{{ plan.expected_outcome }}</p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-100 dark:bg-[#033E94] px-6 py-4 flex justify-end shrink-0 border-t border-gray-200">
                                <button @click="closeStrategicModal" type="button" class="inline-flex w-full sm:w-auto justify-center rounded-lg bg-white px-8 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>