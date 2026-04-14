<script setup lang="ts">
import { computed } from 'vue';

// Automatically computes the current year for the copyright text
const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <footer class="w-full bg-[#033E94]">
        <div class="grid grid-cols-1 lg:grid-cols-2 max-w-7xl mx-auto p-4 md:p-8 gap-8 lg:gap-4">

            <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left space-y-4 md:space-y-0 md:space-x-6">
                <img src="/assets/footerlogo.png" class="h-20 md:h-24 object-contain" alt="Footer Logo" />

                <div>
                    <span class="block text-heading text-xl md:text-2xl lg:text-3xl font-semibold text-white leading-tight">
                        FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE
                    </span>
                    <i class="block text-sm md:text-lg lg:text-xl text-white/90 mt-2">
                        "Investing the Future, Empowering the Nation."
                    </i>
                </div>
            </div>

            <div class="text-white flex flex-col items-center lg:items-end">
                <div class="w-full max-w-md lg:max-w-none lg:w-4/5 text-center sm:text-left">
                    <h5 class="text-lg md:text-xl font-semibold mb-4">Quick Links:</h5>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <ul class="space-y-2 text-sm md:text-base font-medium">
                            <li><a href="#home" class="hover:underline transition-all">Home</a></li>
                            <li><a href="#about" class="hover:underline transition-all">About Us</a></li>
                            <li><a href="#programs" class="hover:underline transition-all">Programs & Services</a></li>
                            <li><a href="#strategic-plans" class="hover:underline transition-all">Strategic Plan 2026-2028</a></li>
                        </ul>
                        <ul class="space-y-2 text-sm md:text-base font-medium">
                            <li><a href="#testimonials" class="hover:underline transition-all">Testimonials</a></li>
                            <li><a href="#news-updates" class="hover:underline transition-all">News & Updates</a></li>
                            <li><a href="#gallery" class="hover:underline transition-all">Gallery</a></li>
                            <li><a href="#contact" class="hover:underline transition-all">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="w-full text-[#033E94] dark:text-white bg-white dark:bg-[#192842] text-xs md:text-sm py-4 border-t border-gray-300 flex flex-col md:flex-row justify-between items-center gap-3 text-center px-6 md:px-12">
            <div class="leading-relaxed">
                © {{ currentYear }} 
                <a href="/" class="hover:underline font-semibold">FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE</a>. 
                <br class="md:hidden" />
                All Rights Reserved.
            </div>
            <div>
                Designed & Developed by 
                <a 
                    class="hover:underline font-semibold" 
                    href="https://bb88advertising.com/" 
                    target="_blank" 
                    rel="noopener noreferrer"
                >
                    BB 88 Advertising and Digital Solutions Inc.
                </a>
            </div>
        </div>
    </footer>
</template>