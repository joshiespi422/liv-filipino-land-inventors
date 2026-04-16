<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useHttp } from '@inertiajs/vue3';
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
import { businessTrainingCategoryFields } from '@/features/business-training/fields';
import { Button } from '@/components/ui/button';
import businessTraining from '@/routes/business-training';
import AppLayout from '@/layouts/AppLayout.vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
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
    href: businessTraining.type.show.url({ slug: props.type.slug }),
  },
];

const http = useHttp();

// Modal State
const isModalOpen = ref(false);
const isLoading = ref(false);
const activeCategory = ref<any>(null);
const activeModules = ref<any[]>([]);

const openCategoryModal = async (categorySlug: string) => {
  isLoading.value = true;
  isModalOpen.value = true;

  http
    .get(businessTraining.modules.url({ slug: categorySlug }))
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

// state
const isFormOpen = ref(false);
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

      <Dialog v-model:open="isModalOpen">
        <DialogContent
          class="flex max-h-[85vh] flex-col p-3 pb-10 sm:max-w-5xl"
        >
          <DialogHeader class="p-3">
            <DialogTitle>{{
              activeCategory?.name || 'Loading...'
            }}</DialogTitle>
            <DialogDescription>
              {{ activeCategory?.description || 'Fetching module content...' }}
            </DialogDescription>
          </DialogHeader>

          <div class="flex-1 overflow-y-auto px-6 py-0">
            <div v-if="isLoading" class="flex flex-col gap-6">
              <div
                v-for="i in 3"
                :key="i"
                class="animate-pulse rounded-lg border p-4"
              >
                <div class="mb-4 h-10 w-48 rounded bg-muted/60"></div>

                <div class="flex flex-col gap-4">
                  <div v-for="j in 2" :key="j" class="space-y-2">
                    <Skeleton class="h-5 w-1/3" />
                    <Skeleton class="h-4 w-full" />
                    <Skeleton class="h-4 w-5/6" />
                  </div>
                </div>
              </div>
            </div>

            <div
              v-else-if="activeModules.length > 0"
              class="flex flex-col gap-6"
            >
              <div
                v-for="module in activeModules"
                :key="module.id"
                class="rounded-lg border p-4"
              >
                <h3 class="mb-4 rounded bg-muted p-2 text-lg font-semibold">
                  Module {{ module.module }}
                </h3>
                <div class="flex flex-col gap-4">
                  <div
                    v-for="(contentBlock, index) in module.content"
                    :key="index"
                  >
                    <h4 class="font-medium">{{ contentBlock.title }}</h4>
                    <p class="mt-1 text-sm text-muted-foreground">
                      {{ contentBlock.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="py-8 text-center text-muted-foreground">
              No modules found for this category.
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <FormDialog
        v-model:open="isFormOpen"
        title="Create Training Category & Modules"
        description="Add a new training category under {{ type.name }} type and its modules."
        show-default
        :fields="businessTrainingCategoryFields"
        :endpoint="businessTraining.type.store.url()"
        @success="
          toast.success('Training category & modules created successfully!')
        "
      />
    </div>
  </AppLayout>
</template>
