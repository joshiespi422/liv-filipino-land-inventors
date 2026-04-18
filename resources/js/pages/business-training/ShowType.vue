<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { InfoIcon, ArrowLeft, PlusIcon } from 'lucide-vue-next';
import FormDialog from '@/components/FormDialog.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import ModuleBuilder from '@/components/ModuleBuilder.vue';
import ModuleViewer from '@/components/ModuleViewer.vue';
import { businessTrainingCategoryFields } from '@/features/business-training/fields';
import { Button } from '@/components/ui/button';
import businessTraining from '@/routes/business-training';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from 'vue-sonner';
import type { BusinessTrainingTypeDetail } from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  type: BusinessTrainingTypeDetail;
  can_mutate: boolean;
}>();

const breadcrumbs = [
  {
    title: 'Business Training',
    href: businessTraining.index.url(),
  },
  {
    title: props.type.name,
    href: businessTraining.types.show.url({ slug: props.type.slug }),
  },
];

const http = useHttp();

// state for form
const isFormOpen = ref(false);
// state for details
const isDetailsOpen = ref(false);
const isLoading = ref(false);
const activeCategory = ref<any>(null);
const activeModules = ref<any[]>([]);

const openCategoryModal = async (categorySlug: string) => {
  isLoading.value = true;
  isDetailsOpen.value = true;

  http
    .get(businessTraining.modules.show.url({ slug: categorySlug }))
    .then((response: any) => {
      const data = response.data || response;

      activeCategory.value = data.category;
      activeModules.value = data.modules;
      isLoading.value = false;
    })
    .catch((error) => {
      console.error('Failed to load modules:', error);
      isLoading.value = false;
    });
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="type.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">
            {{ type.name }} Categories
          </h1>
          <p class="text-muted-foreground">
            Select a category to view its training modules.
          </p>
        </div>
        <Button
          variant="default"
          @click="router.visit(businessTraining.index())"
          ]
        >
          <ArrowLeft /> Back to Types
        </Button>
      </div>

      <Alert v-if="type.categories.length === 0" class="bg-muted/50">
        <InfoIcon class="h-4 w-4" />
        <AlertTitle>No Categories Found</AlertTitle>
        <AlertDescription>
          There are currently no categories listed for this training type.
          {{ can_mutate ? 'Click the button below to add one.' : '' }}
        </AlertDescription>
      </Alert>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="category in type.categories"
          :key="category.id"
          class="flex cursor-pointer flex-col transition-all hover:border-primary hover:shadow-md"
          @click="openCategoryModal(category.slug)"
        >
          <CardHeader>
            <CardTitle>{{ category.name }}</CardTitle>
            <CardDescription class="line-clamp-2">
              {{ category.description }}
            </CardDescription>
          </CardHeader>
          <CardContent class="mt-auto pt-4">
            <Button variant="secondary" class="w-full"> View Modules </Button>
          </CardContent>
        </Card>

        <Card
          v-if="can_mutate"
          @click="isFormOpen = true"
          class="flex cursor-pointer flex-col items-center justify-center border-dashed transition-colors hover:border-primary hover:shadow-md"
        >
          <CardContent class="flex flex-col items-center justify-center">
            <div class="rounded-full bg-primary/10 p-1.5">
              <PlusIcon class="h-8 w-8" />
            </div>
            <p class="mt-2 text-sm text-muted-foreground">Add Category</p>
          </CardContent>
        </Card>
      </div>

      <DetailsDialog
        v-model:open="isDetailsOpen"
        :title="activeCategory?.name"
        :description="activeCategory?.description"
        :loading="isLoading"
        :show-default="false"
      >
        <!-- BOTTOM -->
        <template #bottom>
          <ModuleViewer :modules="activeModules" />
        </template>
      </DetailsDialog>

      <FormDialog
        v-model:open="isFormOpen"
        title="Create Training Category & Modules"
        :description="`Add a new training category under ${type.name} type and its modules.`"
        show-default
        :fields="businessTrainingCategoryFields"
        :endpoint="businessTraining.categories.store({ type: type.slug })"
        :extraData="{
          modules: [
            {
              intro_title: '',
              intro_description: '',
              advantages: [],
              challenges: [],
              required_mindset: [],
            },
            { items: [] },
            { budget: [], min_cost: null, max_cost: null },
            { items: [] },
            { items: [] },
            { items: [] },
          ],
        }"
        @success="
          toast.success('Training category & modules created successfully!')
        "
      >
        <template #default="{ form }">
          <ModuleBuilder v-model="form.modules" :errors="form.errors" />
        </template>
      </FormDialog>
    </div>
  </AppLayout>
</template>
