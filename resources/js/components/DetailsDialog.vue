<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import type { DetailItem } from '@/types';

defineProps<{
  open: boolean;
  title?: string;
  loading?: boolean;
  items?: DetailItem[];
  showDefault?: boolean;
}>();

const emit = defineEmits(['update:open']);
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>{{ title || 'Details' }}</DialogTitle>
      </DialogHeader>

      <DialogDescription>
        <!-- LOADING -->
        <div v-if="loading" class="mt-4 grid grid-cols-2 gap-4">
          <div v-for="i in 10" :key="i" class="space-y-2">
            <Skeleton class="h-3 w-24" />
            <Skeleton class="h-5 w-full" />
          </div>
        </div>

        <div v-else class="mt-2">
          <!-- CUSTOM TOP SLOT -->
          <slot name="top" />

          <!-- DEFAULT LOOP -->
          <div
            v-if="showDefault !== false"
            class="grid grid-cols-2 gap-4 text-sm"
          >
            <div
              v-for="(item, index) in items"
              :key="index"
              :class="item.full ? 'col-span-2' : ''"
              class="space-y-1"
            >
              <p class="text-xs text-muted-foreground">
                {{ item.label }}
              </p>

              <!-- IMAGE -->
              <template v-if="item.type === 'image'">
                <div
                  v-if="item.value"
                  class="mt-1 overflow-hidden rounded-md border"
                >
                  <a :href="item.value" target="_blank" class="block">
                    <img
                      :src="item.value"
                      class="h-32 w-full object-cover transition hover:scale-105"
                    />
                  </a>
                </div>
                <p v-else class="text-muted-foreground italic">
                  No image uploaded
                </p>
              </template>

              <!-- TEXT -->
              <p v-else :class="['font-semibold', item.class]">
                {{ item.value }}
              </p>
            </div>
          </div>

          <!-- CUSTOM BOTTOM SLOT -->
          <slot name="bottom" />
        </div>
      </DialogDescription>
    </DialogContent>
  </Dialog>
</template>
