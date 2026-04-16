<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { businessTrainingTypeFields } from '@/features/business-training/fields';
import FormDialog from '@/components/FormDialog.vue';
import businessTraining from '@/routes/business-training';
import { toast } from 'vue-sonner';
import type { BusinessTrainingType } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Business Training',
        href: businessTraining.index(),
      },
    ],
  },
});

defineProps<{
  types: BusinessTrainingType[];
  can_mutate: boolean;
}>();

// state
const isFormOpen = ref(false);

const navigateToType = (slug: string) => {
  router.visit(businessTraining.type.show(slug));
};
</script>

<template>
  <Head title="Business Training Types" />

  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Business Training</h1>
      <p class="text-muted-foreground">
        Select a training type to view available categories.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
      <Card
        v-for="type in types"
        :key="type.id"
        class="cursor-pointer transition-all hover:border-primary hover:shadow-md"
        @click="navigateToType(type.slug)"
      >
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0"
        >
          <CardTitle class="text-lg font-bold">{{ type.name }}</CardTitle>

          <div
            v-if="type.icon"
            class="h-10 w-10 shrink-0 overflow-hidden rounded-md border bg-muted"
          >
            <img
              :src="`/storage/${type.icon}`"
              :alt="type.name"
              class="h-full w-full object-cover"
            />
          </div>
          <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-muted-foreground"
          >
            <span class="text-xs">N/A</span>
          </div>
        </CardHeader>
        <CardContent>
          <div class="flex items-center text-sm text-muted-foreground">
            Explore categories &rarr;
          </div>
        </CardContent>
      </Card>
      <Card
        v-if="can_mutate"
        class="flex cursor-pointer items-center justify-center border-dashed hover:border-primary hover:shadow-md"
        @click="isFormOpen = true"
      >
        <CardContent class="flex flex-col items-center justify-center">
          <div class="text-3xl font-bold">+</div>
          <p class="mt-2 text-sm text-muted-foreground">Add Type</p>
        </CardContent>
      </Card>
    </div>

    <FormDialog
      v-model:open="isFormOpen"
      title="Create Training Type"
      description="Add a new training type."
      show-default
      :fields="businessTrainingTypeFields"
      :endpoint="businessTraining.store.url()"
      @success="toast.success('Training type created successfully!')"
    />
  </div>
</template>
