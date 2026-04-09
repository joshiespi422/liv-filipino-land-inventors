<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { home, logout, register, login } from '@/routes';
import type { NavItem, NavProps } from '@/types/landing/nav-bar';

const props = defineProps<NavProps>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const mobileMenuIsOpen = ref(false);
const scrolled = ref(false);
const activeSection = ref('home');
const isDarkMode = ref(false);

const navItems: NavItem[] = [
    { id: 'home', label: 'Home' },
    { id: 'about', label: 'About Us' },
    { id: 'program-services', label: 'Program & Services' },
    { id: 'strategic-plans', label: 'Strategic Plans 2026-2027' },
    { id: 'testimonials', label: 'Testimonials' },
    { id: 'news-updates', label: 'News & Updates' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'contact', label: 'Contact' }
];

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
    document.documentElement.classList.toggle('dark', isDarkMode.value);
};

const scrollTo = (id: string) => {
    mobileMenuIsOpen.value = false;
    const element = document.getElementById(id);

    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
    
    navItems.forEach(item => {
        const el = document.getElementById(item.id);
        
        if (el && window.scrollY >= (el.offsetTop - 150)) {
            activeSection.value = item.id;
        }
    });
};

onMounted(() => {
    // Initialize Theme
    isDarkMode.value = localStorage.getItem('theme') === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDarkMode.value);

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div 
        class="fixed inset-x-0 z-100 px-2 md:px-4 transition-all duration-700 ease-in-out"
        :class="scrolled ? 'top-1 md:top-3' : 'top-3 md:top-10'"
    >
        <nav 
            class="mx-auto flex items-center bg-white dark:bg-[#033e94] border border-gray-200 dark:border-white/10 rounded-2xl px-3 py-2 md:px-6 shadow-lg transition-all duration-500"
            :class="scrolled ? 'py-1.5 md:py-2 shadow-xl max-w-full' : 'py-2 md:py-4 shadow-sm max-w-[95%]'"
        >
            <div class="flex flex-none items-center">
                <a href="#" @click.prevent="scrollTo('home')" class="transition-transform hover:scale-105">
                    <img 
                        :src="props.navLogo?.icon_path ?? '/assets/navlogo.png'"
                        class="w-auto transition-all duration-500" 
                        :class="scrolled ? 'h-7 md:h-9' : 'h-8 md:h-11'"
                        alt="Logo"
                    >
                </a>
            </div>

            <ul class="hidden flex-1 items-center justify-center gap-x-1 px-4 xl:flex">
                <li v-for="item in navItems" :key="item.id" class="relative group">
                    <a 
                        :href="'#' + item.id" 
                        @click.prevent="scrollTo(item.id)"
                        class="px-3 py-2 text-sm font-medium transition-colors duration-300 dark:text-white"
                        :class="activeSection === item.id ? 'text-[#033e94]' : 'text-black hover:text-blue-light dark:hover:text-blue-200'"
                    >
                        <span>{{ item.label }}</span>
                    </a>
                    <span
                        class="absolute bottom-0 left-1/2 h-0.5 bg-blue-light transition-all duration-300 -translate-x-1/2"
                        :class="activeSection === item.id ? 'w-4' : 'w-0 group-hover:w-4'"
                    ></span>
                </li>
            </ul>

            <div class="flex flex-none items-center gap-1 md:gap-2 ml-auto">
                <button 
                    type="button" 
                    @click="toggleTheme"
                    class="hidden sm:block rounded-full p-1.5 md:p-2 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors focus:outline-none active:rotate-12"
                >
                    <div class="relative size-4 md:size-5">
                        <svg v-show="isDarkMode" class="absolute inset-0 size-4 md:size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <svg v-show="!isDarkMode" class="absolute inset-0 size-4 md:size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </div>
                </button>

                <div class="hidden sm:flex items-center gap-2">
                    <template v-if="user">
                        <Link 
                            :href="home()"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all"
                        >
                            Dashboard
                        </Link>
                        
                        <Link 
                            :href="logout()" 
                            method="post" 
                            as="button"
                            class="relative ml-3 nav-link text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 px-4 py-2 rounded-xl text-sm font-medium transition-all"
                        >
                            Log Out
                        </Link>
                    </template>

                    <template v-else>
                        <Link 
                            :href="register()"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-[#033e94] dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all"
                        >
                            Register
                        </Link>
                        <Link 
                            :href="login()"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-[#033e94] dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all"
                        >
                            Log in
                        </Link>
                    </template>

                    <Link 
                        :href="register()" 
                        class="rounded-xl bg-[#033e94] dark:bg-white px-5 py-2 text-sm font-medium text-white dark:text-[#033e94] hover:opacity-90 transition-all shadow-md active:scale-95"
                    >
                        Join us
                    </Link>
                </div>

                <button 
                    @click="mobileMenuIsOpen = !mobileMenuIsOpen" 
                    class="p-1.5 md:p-2 text-gray-700 dark:text-white xl:hidden focus:outline-none"
                >
                    <div class="relative size-5 md:size-6">
                        <svg v-show="!mobileMenuIsOpen" class="absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg v-show="mobileMenuIsOpen" class="absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </div>
                </button>
            </div>

            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 scale-95 -translate-y-5"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-5"
            >
                <div 
                    v-show="mobileMenuIsOpen" 
                    class="absolute inset-x-0 top-full mt-2 md:mt-3 z-50 flex flex-col bg-white dark:bg-[#033e94] border border-gray-200 dark:border-white/10 rounded-2xl md:rounded-3xl px-4 py-5 md:px-6 md:py-8 shadow-2xl xl:hidden max-h-[75vh] overflow-y-auto no-scrollbar"
                >
                    <ul class="flex flex-col gap-y-1 md:gap-y-2">
                        <li v-for="item in navItems" :key="item.id">
                            <a 
                                @click.prevent="scrollTo(item.id)" 
                                :href="'#' + item.id"
                                class="block p-2.5 md:p-3 rounded-xl text-base md:text-lg font-medium transition-colors dark:text-white"
                                :class="activeSection === item.id ? 'text-blue-600 bg-gray-50 dark:bg-white/10' : 'text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10'"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 flex justify-between items-center sm:hidden">
                        <span class="text-sm font-medium text-gray-700 dark:text-white">Dark Mode</span>
                        <button 
                            type="button" 
                            @click="toggleTheme"
                            class="rounded-full p-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white focus:outline-none"
                        >
                            <svg v-show="isDarkMode" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            <svg v-show="!isDarkMode" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 md:gap-3 sm:hidden">
                        <Link 
                            :href="login()"
                            class="w-full rounded-xl md:rounded-2xl border border-gray-300 dark:border-white/20 px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-gray-700 dark:text-white active:scale-[0.98] transition-transform"
                        >
                            Log in
                        </Link>
                        <Link 
                            :href="register()" 
                            class="w-full rounded-xl md:rounded-2xl bg-blue-600 dark:bg-white px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-white dark:text-[#033e94] shadow-lg active:scale-[0.98] transition-transform"
                        >
                            Join us
                        </Link>
                    </div>
                </div>
            </transition>
        </nav>
    </div>
</template>