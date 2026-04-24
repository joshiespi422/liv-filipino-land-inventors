<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getIPColumns } from '@/features/intellectual-property/columns';
import intellectualPropertyAssistance from '@/routes/intellectual-property-assistance';
import type {
  IntellectualProperty,
  IntellectualPropertyStatus,
  IntellectualPropertyCreationType,
  IntellectualPropertyFormType,
  ApiResponse,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Intellectual Property',
        href: intellectualPropertyAssistance.index(),
      },
    ],
  },
});

const props = defineProps<{
  intellectual_properties: {
    data: IntellectualProperty[];
  };
  can_mutate: boolean;
  filters: {
    status: IntellectualPropertyStatus;
    creation: IntellectualPropertyCreationType;
    form: IntellectualPropertyFormType;
  };
}>();

const selectedStatus = ref(props.filters.status || 'pending');
const selectedCreation = ref(props.filters.creation || null);
const selectedForm = ref(props.filters.form || null);

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    intellectualPropertyAssistance.index(),
    {
      status: selectedStatus.value,
      creation: selectedCreation.value || undefined,
      form: selectedForm.value || undefined,
    },
    {
      preserveScroll: true,
      replace: true,
    },
  );
};

// Watch for select filter changes (debounced)
const debouncedUpdate = useDebounceFn(updateFilters, 300);
watch([selectedStatus, selectedCreation, selectedForm], debouncedUpdate);

const approveIP = (ipId: number) => {};
const declineIP = (ipId: number) => {};

const viewIPDetails = (ipId: number) => {};

const columns = getIPColumns({
  viewIPDetails,
  approveIP,
  declineIP,
});
</script>

<template>
  <Head title="Intellectual Property" />

  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Intellectual Property</h1>
        <p class="text-muted-foreground">
          Manage and monitor intellectual property.
        </p>
      </div>
      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedCreation">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="business_idea"> Business idea</SelectItem>
            <SelectItem value="invention"> Invention</SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="selectedForm">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="grant">Grant</SelectItem>
            <SelectItem value="payment">Payment</SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="waiting_for_payment"
              >Waiting for payment</SelectItem
            >
            <SelectItem value="expired">Expired</SelectItem>
            <SelectItem value="rejected">Rejected</SelectItem>
            <SelectItem value="registered">Registered</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="intellectual_properties.data"
      search-placeholder="Search intellectual property..."
    />
  </div>
</template>
