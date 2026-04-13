<script setup lang="ts">
import { ref, computed } from 'vue';

// --- INTERFACES ---
interface NewsItem {
    id: number | string;
    title: string;
    excerpt: string;
    image?: string;
    published_at?: string;
    is_highlight?: boolean;
}

const props = defineProps<{
    news?: NewsItem[];
}>();

// --- SAMPLE DATA (Fallback if backend is empty) ---
const sampleNews: NewsItem[] = [
    {
        id: 'sample-1',
        title: 'New Partnerships',
        excerpt: ' FISMPC continues to collaborate with government and academic partners. We work closely with DOST, DTI, IPOPHL, and local governments to expand opportunities for inventors. Stay tuned to this section for monthly newsletters, grant announcements, and upcoming event schedules.',
        image: '/assets/n1.jpg',
        published_at: 'Ongoing',
        is_highlight: true
    },
    {
        id: 'sample-2',
        title: 'Green Tech Initiative',
        excerpt: 'Under the Green Innovation and Renewable Systems program, FISMPC installed solar microgrid systems and water purifiers in 10 rural barangays, bringing clean energy and water solutions to these communities.',
        image: '/assets/n2.jpg',
        published_at: '2026-01-01'
    },
    {
        id: 'sample-3',
        title: 'National Inventors Week 2025',
        excerpt: 'Our annual innovation festival attracted over 5,000 participants. Inventors nationwide showcased their inventions and attended seminars on patent filing and business development. Youth inventor awards were given to outstanding student projects.',
        image: '/assets/n3.jpg',
        published_at: '2026-03-20 14:30:00'
    },
    {
        id: 'sample-4',
        title: 'Digital Member Portal Goes Live',
        excerpt: 'We launched our new online portal and blockchain-based IP timestamping system, making it easier for members to register, pay dues, and access learning resources from anywhere.',
        image: '/assets/n4.jpg',
        published_at: '2026-07-15 10:00:00',
    },
    // Added 5th item purely to trigger the scrollbar
    {
        id: 'sample-5',
        title: 'Future Innovations Grant',
        excerpt: 'We are opening applications for the Q3 funding round. Members with working prototypes can apply for up to PHP 500,000 in prototyping grants and get connected with dedicated manufacturing partners.',
        image: '/assets/n1.jpg',
        published_at: '2026-09-01 08:00:00',
    }
];

// --- COMPUTED DATA ---
const displayNews = computed(() => {
    return (props.news && props.news.length > 0) ? props.news : sampleNews;
});

const featured = computed(() => {
    return displayNews.value.find(n => n.is_highlight) || displayNews.value[0] || null;
});

const newsList = computed(() => {
    if (!featured.value) return [];
    return displayNews.value.filter(n => n.id !== featured.value!.id);
});

// --- HELPER METHODS ---
const formatDate = (val?: string) => {
    if (!val) return '';
    const dateOnly = val.trim().split(' ')[0];
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    
    if (regex.test(dateOnly)) {
        
        const date = new Date(dateOnly);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    }
    return val;
};

const getImageUrl = (path?: string) => {
    if (!path) return '/assets/n1.jpg'; 
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};

const expandedItems = ref<Set<string | number>>(new Set());

const toggleDetails = (id: string | number) => {
    if (expandedItems.value.has(id)) {
        expandedItems.value.delete(id); 
    } else {
        expandedItems.value.add(id);
    }
};

const isExpanded = (id: string | number) => expandedItems.value.has(id);
</script>

<template>
    <div id="news-updates" class="overflow-x-hidden">
        
        <div class="relative bg-[#829FCA] w-[90%] sm:w-fit min-w-[16rem] sm:min-w-100 px-4 sm:px-6 py-3 rounded-br-[40px] mb-8
                   before:content-[''] before:absolute before:top-0 before:left-0 
                   before:bg-[#033E94] before:rounded-br-[40px] before:h-full before:w-[95%] before:z-0
                   after:content-[''] after:absolute after:top-0 after:left-0 
                   after:bg-[#D70328] after:rounded-br-[40px] after:h-full after:w-[88%] after:z-0">
            <span class="relative z-10 text-white font-bold text-lg sm:text-xl md:text-2xl uppercase">
                News & Updates
            </span>
        </div>

        <div class="flex justify-center p-4 sm:p-8 bg-surface">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 w-full max-w-7xl items-start">

                <div class="lg:sticky lg:top-10 w-full">
                    <div v-if="featured" class="bg-outline bg-white rounded-3xl shadow-sm overflow-hidden border border-gray-100">
                        <div class="relative block">
                            <img class="w-full h-56 sm:h-80 lg:h-112.5 object-cover" 
                                 :src="getImageUrl(featured.image)" 
                                 :alt="featured.title" />
                            
                            <div class="absolute left-0 top-4 sm:top-5 bg-linear-to-tr from-yellow-600 via-yellow-400 to-yellow-600 text-white pl-4 sm:pl-8 pr-10 sm:pr-16 py-1 sm:py-2 rounded-r-3xl text-lg sm:text-2xl md:text-3xl font-bold shadow-lg">
                                HIGHLIGHTS
                            </div>
                        </div>
                        
                        <div class="p-5 sm:p-6 text-start">
                            <h2 class="text-base sm:text-lg md:text-xl font-bold text-[#D70328]">
                                {{ formatDate(featured.published_at) }}
                            </h2>
                            <h2 class="mt-2  text-[#033E94] font-bold text-xl sm:text-2xl leading-tight">
                                {{ featured.title }}
                            </h2>
                            
                            <p class="text-sm md:text-base text-sub-text text-gray-600 transition-all duration-300 mt-3"
                               :class="isExpanded('highlight') ? '' : 'h-20 overflow-hidden'">
                                {{ featured.excerpt }}
                            </p>
                            
                            <div class="flex justify-center mt-6">
                                <button @click="toggleDetails('highlight')" 
                                        class="text-white font-bold rounded-xl bg-[#033E94] hover:bg-blue-700 shadow-md text-lg sm:text-xl w-full max-w-xs py-2 transition-transform active:scale-95">
                                    {{ isExpanded('highlight') ? 'Hide' : 'View Details' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-5 w-full max-h-162.5 overflow-y-auto pr-2">
                    <template v-if="newsList.length > 0">
                        <div v-for="item in newsList" :key="item.id" class="bg-outline bg-white rounded-3xl shadow-sm flex flex-col sm:flex-row w-full border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 shrink-0">
                            
                            <img class="object-cover w-full sm:w-40 md:w-48 lg:w-40 xl:w-48 h-48 sm:h-auto shrink-0" 
                                 :src="getImageUrl(item.image)"
                                 :alt="item.title">
                                 
                            <div class="p-4 sm:p-5 text-start flex flex-col justify-between w-full">
                                <div>
                                    <h2 class="text-sm sm:text-lg font-bold text-[#D70328]">
                                        {{ formatDate(item.published_at) }}
                                    </h2>
                                    <h2 class="mt-1 text-[#033E94] font-bold leading-snug text-base sm:text-lg">
                                        {{ item.title }}
                                    </h2>
                                    
                                    <p class="pr-2 text-sub-text text-gray-600 transition-all duration-300 text-sm mt-2"
                                       :class="isExpanded(item.id) ? '' : 'h-13 overflow-hidden'">
                                        {{ item.excerpt }}
                                    </p>
                                </div>
                                
                                <button @click="toggleDetails(item.id)" 
                                        class="text-white font-bold rounded-lg bg-[#033E94] hover:bg-blue-700 shadow-sm text-sm w-full sm:w-32 py-2 mt-4 transition-colors">
                                    {{ isExpanded(item.id) ? 'Hide' : 'View' }}
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <div v-else class="text-center py-20 text-gray-400 italic bg-gray-50 rounded-3xl border-2 border-dashed">
                        No other updates found.
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>