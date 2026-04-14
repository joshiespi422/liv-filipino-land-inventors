<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import dashboard from '@/routes/dashboard';
import DataTable from '@/components/DataTable.vue';
import { pendingUserColumns } from '@/features/dashboard/columns';
import type { PendingUser } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard.index(),
      },
    ],
  },
});

const props = defineProps<{
  pendingUsers: {
    data: PendingUser[];
  };
}>();
</script>

<template>
  <Head title="Dashboard" />

  <div class="flex flex-col gap-4 p-6">
    <div>
      <h1 class="text-2xl font-bold">Pending Users</h1>
      <p class="text-muted-foreground">Users waiting for approval.</p>
    </div>

    <DataTable
      :columns="pendingUserColumns"
      :data="pendingUsers.data"
      search-placeholder="Search name, email..."
    />
  </div>
</template>
