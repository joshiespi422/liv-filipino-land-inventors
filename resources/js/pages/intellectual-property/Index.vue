<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getIPColumns } from '@/features/intellectual-property/columns';
import { getIPDetails } from '@/features/intellectual-property/details';
import intellectualPropertyAssistance from '@/routes/intellectual-property-assistance';
import type {
  IntellectualProperty,
  IntellectualPropertyStatus,
  IntellectualPropertyCreationType,
  IntellectualPropertyFormType,
  IntellectualPropertyDetail,
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

// state for select filters
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

// state for details
const selectedIP = ref<IntellectualPropertyDetail | null>(null);
const isDetailsOpen = ref(false);
// inertia http
const http = useHttp();

// fetch intellectual property
const showIPDetails = async (id: number) => {
  isDetailsOpen.value = true;
  selectedIP.value = null;

  try {
    const response = (await http.get(
      intellectualPropertyAssistance.show.url(id),
    )) as ApiResponse<IntellectualPropertyDetail>;

    selectedIP.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

const approveIP = (ipId: number) => {};
const declineIP = (ipId: number) => {};

const columns = getIPColumns({
  showIPDetails,
  approveIP,
  declineIP,
});

const ipDetails = computed(() =>
  selectedIP.value ? getIPDetails(selectedIP.value) : [],
);
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

  <DetailsDialog
    v-model:open="isDetailsOpen"
    title="Intellectual Property Details"
    :loading="http.processing || !selectedIP"
    :items="ipDetails"
    show-default
  >
    <template #bottom>
      <div v-if="selectedIP" class="mt-4 space-y-6">
        <!-- CLAIMS -->
        <div>
          <h3 class="mb-1 text-xs text-muted-foreground">Claims</h3>

          <ul v-if="selectedIP.claims.length" class="ml-5 list-disc space-y-1">
            <li
              v-for="claim in selectedIP.claims"
              :key="claim.id"
              class="text-sm wrap-break-word"
            >
              {{ claim.description }}
            </li>
          </ul>

          <p v-else class="text-sm text-muted-foreground italic">
            No claims available
          </p>
        </div>

        <!-- DOCUMENTS -->
        <div>
          <h3 class="mb-1 text-xs text-muted-foreground">Documents</h3>

          <ul
            v-if="selectedIP.documents.length"
            class="ml-5 list-disc space-y-1"
          >
            <li
              v-for="doc in selectedIP.documents"
              :key="doc.id"
              class="text-sm wrap-break-word"
            >
              <a
                :href="doc.attachment"
                target="_blank"
                class="text-xs wrap-break-word text-blue-500 underline"
              >
                {{ doc.attachment.split('/').pop() }}
              </a>
            </li>
          </ul>

          <p v-else class="text-sm text-muted-foreground italic">
            No documents uploaded
          </p>
        </div>
      </div>
    </template>
  </DetailsDialog>
</template>
