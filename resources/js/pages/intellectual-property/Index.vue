<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import DataTable from '@/components/DataTable.vue';
import intellectualPropertyAssistance from '@/routes/intellectual-property-assistance';
import { getIPColumns } from '@/features/intellectual-property/columns';
import { toast } from 'vue-sonner';
import type {
  IntellectualProperty,
  IntellectualPropertyStatus,
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
  };
}>();

const selectedStatus = ref(props.filters.status || 'pending');

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
