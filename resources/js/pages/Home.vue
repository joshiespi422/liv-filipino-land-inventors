<script setup lang="ts">
// 1. Tell Inertia NOT to use the default dashboard layout
// defineOptions({ layout: null as any });
// import { Head, Link } from '@inertiajs/vue3';
// import { dashboard, login, register } from '@/routes';
import AboutSection from '@/components/landing/AboutSection.vue';
import ContactSection from '@/components/landing/ContactSection.vue';
import Footer from '@/components/landing/Footer.vue';
import GallerySection from '@/components/landing/GallerySection.vue';
import HomeSection from '@/components/landing/HomeSection.vue';
import NavBar from '@/components/landing/NavBar.vue';
import NewsUpdates from '@/components/landing/NewsUpdates.vue';
import ProgramServices from '@/components/landing/ProgramServices.vue';
import StrategicPlans from '@/components/landing/StrategicPlans.vue';
import TestimonialsSection from '@/components/landing/TestimonialsSection.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <NavBar :can-register="canRegister" />
    <main>
    <HomeSection :data="$page.props.home" />
    <AboutSection :data="$page.props.about" />
    <ProgramServices />
    <StrategicPlans />
    <TestimonialsSection />
    <NewsUpdates />
    <GallerySection />
    <ContactSection />
    <Footer />
    </main>
</template>