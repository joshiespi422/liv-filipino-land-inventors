<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
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
import type { FormField } from '@/types';

const props = defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  fields: FormField[];
  endpoint: string | { url: string; method: string };
  method?: 'post' | 'put' | 'patch';
  forceFormData?: boolean;
  showDefault?: boolean;
  columns?: 1 | 2;
}>();

const emit = defineEmits(['update:open', 'success']);

// build initial form dynamically
const initialData = Object.fromEntries(props.fields.map((f) => [f.name, '']));

// inertia form
const form = useForm(initialData);

// reset when opened, doesn't need for now
// watch(
//   () => props.open,
//   (val) => {
//     if (val) {
//       form.reset();
//       form.clearErrors();
//     }
//   },
// );

// validation
const isValid = computed(() =>
  props.fields.every((f) => {
    if (!f.required) return true;
    return !!form[f.name];
  }),
);

// submit
const handleSubmit = () => {
  // Resolve URL and Method first (Wayfinder objects contain the method)
  const isWayfinder = typeof props.endpoint !== 'string';
  const url = isWayfinder ? props.endpoint.url : props.endpoint;
  const method = isWayfinder ? props.endpoint.method : props.method || 'post';

  // Logic flags based on the RESOLVED method, not just props
  const hasFiles = props.fields.some((f) => f.type === 'file');
  const isUpdating =
    method.toLowerCase() === 'put' || method.toLowerCase() === 'patch';
  const requiresFormData = props.forceFormData || hasFiles;

  const options = {
    forceFormData: requiresFormData,
    onSuccess: () => {
      emit('update:open', false);
      emit('success');
      form.reset();
    },
  };

  // Logic for PUT/PATCH with files (Method Spoofing)
  if (isUpdating && hasFiles) {
    form
      .transform((data) => ({
        ...data,
        _method: method.toUpperCase(),
      }))
      .post(url, options);
  } else {
    // Logic for standard POST or PUT/PATCH without files
    form[method as 'post' | 'put' | 'patch'](url, options);
  }
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
      <div
        v-if="showDefault !== false"
        :class="[
          'mt-4 gap-4',
          columns === 2 ? 'grid grid-cols-2' : 'grid grid-cols-1',
        ]"
      >
        <div
          v-for="field in fields"
          :key="field.name"
          :class="['space-y-2', field.col === 2 ? 'col-span-2' : '']"
        >
          <Label>{{ field.label }}</Label>

          <!-- TEXT -->
          <Input
            v-if="field.type === 'text'"
            v-model="form[field.name]"
            :placeholder="field.placeholder"
          />

          <!-- FILE -->
          <Input
            v-else-if="field.type === 'file'"
            type="file"
            @change="(e: any) => (form[field.name] = e.target.files?.[0])"
          />

          <!-- TEXTAREA -->
          <Textarea
            v-else-if="field.type === 'textarea'"
            v-model="form[field.name]"
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
              <SelectValue :placeholder="field.placeholder" />
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

          <!-- ERROR -->
          <p v-if="form.errors[field.name]" class="text-sm text-destructive">
            {{ form.errors[field.name] }}
          </p>
        </div>
      </div>

      <!-- CUSTOM BODY -->
      <slot />

      <!-- ACTIONS -->
      <div class="mt-6 flex justify-end gap-2">
        <Button variant="outline" @click="emit('update:open', false)">
          Cancel
        </Button>

        <Button :disabled="form.processing || !isValid" @click="handleSubmit">
          {{ form.processing ? 'Submitting...' : 'Submit' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
