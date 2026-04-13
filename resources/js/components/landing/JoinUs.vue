<script setup lang="ts">
// Define props so your navLogo check works
interface Props {
    navLogo?: { icon_path: string };
}
const props = defineProps<Props>();

// Replace these with your actual app store links when ready
const playStoreUrl = "https://play.google.com/store/apps";
const appStoreUrl = "https://www.apple.com/app-store/";
</script>

<template>
    <section id="join-us" class="relative py-10 lg:py-20 overflow-hidden bg-gray-50 dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 size-[500px] bg-blue-100/50 dark:bg-blue-900/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/4 size-[400px] bg-yellow-100/50 dark:bg-yellow-900/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="container mx-auto px-6 relative z-10 max-w-7xl">
            <div class="flex flex-col-reverse lg:flex-row items-center gap-12 lg:gap-20">
                
                <div class="lg:w-1/2 text-center lg:text-left mt-8 lg:mt-0">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-[#033e94] dark:text-white mb-6 leading-tight">
                        Become a Part of
                        <span class="text-[#FFCC00]">Filipino Innovation</span>
                    </h2>
                    
                    <p class="text-gray-600 dark:text-gray-300 text-base md:text-lg mb-8 leading-relaxed">
                        Download the official FISMPC app to join a dynamic community of visionary inventors and entrepreneurs. Get exclusive access to cooperative benefits, member updates, and a platform to commercialize your creative ideas right from your phone.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a :href="playStoreUrl" target="_blank" rel="noopener noreferrer" class="transition-transform duration-300 hover:scale-105 active:scale-95 hover:shadow-lg rounded-xl">
                            <img 
                                src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                                alt="Get it on Google Play" 
                                class="h-14 w-auto"
                            />
                        </a>

                        <a :href="appStoreUrl" target="_blank" rel="noopener noreferrer" class="transition-transform duration-300 hover:scale-105 active:scale-95 hover:shadow-lg rounded-xl">
                            <img 
                                src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" 
                                alt="Download on the App Store" 
                                class="h-14 w-auto"
                            />
                        </a>
                    </div>
                </div>

                <div class="lg:w-1/2 relative flex justify-center w-full">
                    <div class="relative z-20 w-[280px] h-[580px] bg-slate-800 rounded-[3rem] border-[10px] border-slate-900 shadow-2xl overflow-hidden flex flex-col">
                        <div class="absolute top-0 inset-x-0 h-6 bg-slate-900 rounded-b-2xl w-40 mx-auto z-30"></div>
                        
                        <div class="flex-1 w-full bg-white dark:bg-slate-800 flex flex-col relative">
                            <div class="bg-[#033e94] pt-12 pb-8 px-6 text-center rounded-b-3xl shadow-md relative z-10">
                                
                                <div class="size-16 bg-white rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg p-2">
                                    <img 
                                        :src="props.navLogo?.icon_path ?? '/assets/navlogo.png'"
                                        class="w-auto h-full object-contain transition-all duration-500" 
                                        alt="Logo"
                                    >
                                </div>

                                <h3 class="text-white font-bold text-lg">Join Us</h3>
                                <p class="text-blue-200 text-sm mt-1">Register today</p>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <div class="h-12 w-full bg-gray-100 dark:bg-slate-700 rounded-xl"></div>
                                <div class="h-12 w-full bg-gray-100 dark:bg-slate-700 rounded-xl"></div>
                                <div class="h-12 w-full bg-[#FFCC00] rounded-xl shadow-md mt-6"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-72 bg-[#FFCC00] rounded-full filter blur-[80px] opacity-30 dark:opacity-20 z-0"></div>
                </div>

            </div>
        </div>
    </section>
</template>