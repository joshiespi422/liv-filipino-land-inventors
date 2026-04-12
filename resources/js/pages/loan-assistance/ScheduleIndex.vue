<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import loanAssistance from '@/routes/loan-assistance';
import loanSchedule from '@/routes/loan-assistance/schedule';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { scheduleColumns } from '@/features/loan-assistance/columns';
import type { LoanSchedule } from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  schedules: {
    data: LoanSchedule[];
  };
  loanId: number;
}>();

const breadcrumbs = [
  {
    title: 'Loan Assistance',
    href: loanAssistance.index.url(),
  },
  {
    title: 'Schedule',
    href: loanSchedule.index(props.loanId),
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Loan Schedule" />

    <div class="flex h-full flex-1 flex-col gap-3 p-6">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Loan Schedule</h1>
        <p class="text-muted-foreground">View loan breakdown and schedule.</p>
      </div>

      <DataTable
        :columns="scheduleColumns"
        :data="schedules.data"
        search-placeholder="Search payment or status..."
      />
    </div>
  </AppLayout>
</template>
