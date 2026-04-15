<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

type Variant = 'default' | 'destructive' | 'success' | 'warning';

defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  confirmText?: string;
  cancelText?: string;
  variant?: Variant;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'confirm', 'cancel']);
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>
          <slot name="title">
            {{ title || 'Are you sure?' }}
          </slot>
        </DialogTitle>

        <DialogDescription>
          <slot name="description">
            {{ description || 'This action cannot be undone.' }}
          </slot>
        </DialogDescription>
      </DialogHeader>

      <div class="mt-4 flex justify-end gap-2">
        <!-- Cancel -->
        <Button
          variant="outline"
          @click="
            emit('cancel');
            emit('update:open', false);
          "
        >
          {{ cancelText || 'Cancel' }}
        </Button>

        <!-- Confirm -->
        <Button
          :variant="variant === 'destructive' ? 'destructive' : 'default'"
          :disabled="loading"
          @click="emit('confirm')"
        >
          {{ confirmText || 'Confirm' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
