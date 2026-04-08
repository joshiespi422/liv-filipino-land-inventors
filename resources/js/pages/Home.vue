<script setup lang="ts">
// 1. Tell Inertia NOT to use the default dashboard layout
defineOptions({ layout: null as any });
// import { Head, Link } from '@inertiajs/vue3';
// import { dashboard, login, register } from '@/routes';
import HomeSection from '@/components/landing/HomeSection.vue';
import AboutSection from '@/components/landing/AboutSection.vue';
import ProgramServices from '@/components/landing/ProgramServices.vue';
import StrategicPlans from '@/components/landing/StrategicPlans.vue';
import TestimonialsSection from '@/components/landing/TestimonialsSection.vue';
import NewsUpdates from '@/components/landing/NewsUpdates.vue';
import GallerySection from '@/components/landing/GallerySection.vue';
import ContactSection from '@/components/landing/ContactSection.vue';
import Footer from '@/components/landing/Footer.vue';

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
    <HomeSection :data="$page.props.home" />
    <AboutSection :data="$page.props.about" />
    <ProgramServices />
    <StrategicPlans />
    <TestimonialsSection />
    <NewsUpdates />
    <GallerySection />
    <ContactSection />
    <Footer />
</template>