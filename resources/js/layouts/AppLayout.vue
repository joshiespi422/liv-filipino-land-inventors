<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import 'vue-sonner/style.css';

const { breadcrumbs = [] } = defineProps<{
  breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
    <Toaster />
  </AppLayout>
</template>
