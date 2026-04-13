<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import loanAssistance from '@/routes/loan-assistance';
import loanSchedule from '@/routes/loan-assistance/schedule';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
import { scheduleColumns } from '@/features/loan-assistance/columns';
import type { LoanSchedule, LoanAssistance } from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  schedules: {
    data: LoanSchedule[];
  };
  loan: {
    data: LoanAssistance;
  };
  summary: {
    total_interest: number;
    total_payment: number;
  };
}>();

const breadcrumbs = [
  {
    title: 'Loan Assistance',
    href: loanAssistance.index.url(),
  },
  {
    title: 'Schedule',
    href: loanSchedule.index(props.loan.data.id),
  },
];

const formatCurrency = (val: number) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(val);

const loanDetails = [
  { label: 'Borrower', value: props.loan.data.user_name },
  { label: 'Loan Amount', value: formatCurrency(props.loan.data.amount) },
  { label: 'Interest Rate', value: props.loan.data.interest_rate_display },
  { label: 'Term', value: `${props.loan.data.term_months} months` },
  { label: 'Start Date', value: props.loan.data.start_date_display },
  { label: 'End Date', value: props.loan.data.end_date_display },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Loan Schedule" />

    <div class="flex flex-1 flex-col gap-3 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Loan Schedule</h1>
          <p class="text-muted-foreground">View loan breakdown and schedule.</p>
        </div>
        <Button
          variant="default"
          @click="router.visit(loanAssistance.index())"
          ]
        >
          <ArrowLeft /> Back to Loan
        </Button>
      </div>

      <div class="mt-4 grid grid-cols-[2fr_1fr] gap-4">
        <div>
          <DataTable
            :columns="scheduleColumns"
            :data="schedules.data"
            search-placeholder="Search payment or status..."
          />
        </div>

        <div class="flex flex-col gap-2">
          <div class="grid grid-cols-2 gap-2">
            <Card>
              <CardHeader>
                <CardTitle>Total Interest</CardTitle>
              </CardHeader>
              <CardContent>
                <p class="font-semibold">
                  {{ formatCurrency(summary.total_interest) }}
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Total Payment</CardTitle>
              </CardHeader>
              <CardContent>
                <p class="font-semibold">
                  {{ formatCurrency(summary.total_payment) }}
                </p>
              </CardContent>
            </Card>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Loan Details</CardTitle>
            </CardHeader>

            <CardContent class="grid grid-cols-2 gap-4 text-sm">
              <div
                v-for="(item, index) in loanDetails"
                :key="index"
                class="space-y-1"
              >
                <p class="text-xs text-muted-foreground">
                  {{ item.label }}
                </p>
                <p class="truncate font-semibold">
                  {{ item.value }}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
