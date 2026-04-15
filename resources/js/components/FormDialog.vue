<script setup lang="ts">
import { reactive, watch } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput,
} from '@/components/ui/number-field';

type FieldType = 'text' | 'textarea' | 'number' | 'select' | 'file';

interface Field {
  type: FieldType;
  name: string;
  label: string;
  placeholder?: string;
  options?: { label: string; value: string | number }[];
}

const props = defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  fields: Field[];
  initialValues?: Record<string, any>;
  loading?: boolean;
  showDefault?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

// reactive form
const form = reactive<Record<string, any>>({});

// initialize / reset form when dialog opens
watch(
  () => props.open,
  (val) => {
    if (val) {
      Object.assign(form, props.initialValues || {});
    }
  },
);

const handleSubmit = () => {
  emit('submit', { ...form });
};
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ title || 'Form' }}</DialogTitle>
        <DialogDescription>
          {{ description || '' }}
        </DialogDescription>
      </DialogHeader>

      <!-- CUSTOM TOP -->
      <slot name="top" />

      <!-- DEFAULT FORM -->
      <div v-if="showDefault !== false" class="mt-4 space-y-4">
        <div v-for="field in fields" :key="field.name" class="space-y-2">
          <Label>{{ field.label }}</Label>

          <!-- TEXT / FILE -->
          <Input
            v-if="field.type === 'text' || field.type === 'file'"
            v-model="form[field.name]"
            :type="field.type"
            :placeholder="field.placeholder"
          />

          <!-- for file if v-model is not working although need an adjustment "any" types? -->
          <!-- <Input
            v-if="field.type === 'file'"
            type="file"
            @change="(e: any) => form[field.name] = e.target.files?.[0]"
            /> -->

          <!-- TEXTAREA -->
          <Textarea
            v-else-if="field.type === 'textarea'"
            v-model="form[field.name]"
            :placeholder="field.placeholder"
          />

          <!-- NUMBER -->
          <NumberField v-else-if="field.type === 'number'">
            <NumberFieldContent>
              <NumberFieldDecrement />
              <NumberFieldInput v-model="form[field.name]" />
              <NumberFieldIncrement />
            </NumberFieldContent>
          </NumberField>

          <!-- SELECT -->
          <Select
            v-else-if="field.type === 'select'"
            v-model="form[field.name]"
          >
            <SelectTrigger>
              <SelectValue
                :placeholder="field.placeholder || 'Select option'"
              />
            </SelectTrigger>

            <SelectContent>
              <SelectItem
                v-for="opt in field.options"
                :key="opt.value"
                :value="opt.value"
              >
                {{ opt.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <!-- CUSTOM BODY -->
      <slot />

      <!-- ACTIONS -->
      <div class="mt-6 flex justify-end gap-2">
        <Button variant="outline" @click="emit('update:open', false)">
          Cancel
        </Button>

        <Button :disabled="loading" @click="handleSubmit"> Submit </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
