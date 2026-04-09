<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import businessTraining from '@/routes/business-training';

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
    </div>
  </div>
</template>
