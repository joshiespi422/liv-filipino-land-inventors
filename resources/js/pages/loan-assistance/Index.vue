<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import loanAssistance from '@/routes/loan-assistance';
import { CreditCard, Calendar, Percent } from 'lucide-vue-next';
import { computed } from 'vue';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Loan Assistance',
        href: loanAssistance.index(),
      },
    ],
  },
});

interface globalSetting {
  id: number;
  default_amount: string;
  default_interest_rate: string;
  default_term_months: number;
}

const props = defineProps<{
  global_settings: globalSetting | null;
  can_mutate: boolean;
}>();

const displayStats = computed(() => {
  if (!props.global_settings) return [];

  return [
    {
      title: 'Maximum Amount',
      value: `₱${parseFloat(props.global_settings.default_amount).toLocaleString()}`,
      description: 'Default loan principal',
      icon: CreditCard,
    },
    {
      title: 'Current Interest Rate',
      value: `${(parseFloat(props.global_settings.default_interest_rate) * 100).toFixed(2)}%`,
      description: 'Annual percentage rate',
      icon: Percent,
    },
    {
      title: 'Maximum Term',
      value: `${props.global_settings.default_term_months} Months`,
      description: 'Standard repayment period',
      icon: Calendar,
    },
  ];
});
</script>

<template>
  <Head title="Loan Assistance" />

  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Global Default Value</h1>
      <p class="text-muted-foreground">
        Manage the default loan parameters used across the system.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card
        v-for="stat in displayStats"
        :key="stat.title"
        class="flex flex-col justify-between transition-all hover:shadow-md"
      >
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
          <component :is="stat.icon" class="h-5 w-5 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stat.value }}
          </div>
          <p class="text-xs text-muted-foreground">
            {{ stat.description }}
          </p>
        </CardContent>
      </Card>

      <div
        v-if="!global_settings"
        class="col-span-3 py-10 text-center text-muted-foreground"
      >
        No global settings found.
      </div>
    </div>
  </div>
</template>
