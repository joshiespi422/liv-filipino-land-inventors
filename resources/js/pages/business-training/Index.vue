<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { businessTrainingTypeFields } from '@/features/business-training/fields';
import FormDialog from '@/components/FormDialog.vue';
import businessTraining from '@/routes/business-training';
import { toast } from 'vue-sonner';

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
  types: Array<{ id: number; name: string; slug: string; icon: string | null }>;
  can_mutate: boolean;
}>();

// state
const isFormOpen = ref(false);

const handleCreateType = (data: any) => {
  router.post(businessTraining.store(), data, {
    onSuccess: () => {
      isFormOpen.value = false;
      toast.success('Training type created successfully!');
    },
  });
};

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
        <CardHeader class="pb-2">
          <CardTitle class="text-lg">{{ type.name }}</CardTitle>
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
      :fields="businessTrainingTypeFields"
      show-default
      @submit="handleCreateType"
    />
  </div>
</template>
