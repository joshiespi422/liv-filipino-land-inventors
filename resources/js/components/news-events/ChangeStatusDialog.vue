<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowLeftRightIcon, Loader2Icon } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { statusClasses } from '@/lib/news-events';
import newsEvents from '@/routes/news-events';
import type { NewsArticleDetail, NewsStatusOption } from '@/types';

const props = defineProps<{
  article: NewsArticleDetail;
  transitions: NewsStatusOption[];
}>();

const open = ref(false);

const defaults = () => ({
  status: (props.transitions[0]?.value ?? '') as string,
  // prefill the current date when rescheduling (ISO keeps the app timezone offset)
  published_at:
    props.article.status === 'scheduled' && props.article.published_at
      ? props.article.published_at.slice(0, 10)
      : '',
});

const form = useForm(defaults());

const isScheduled = computed(() => form.status === 'scheduled');

const minDate = (() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toLocaleDateString('en-CA'); // YYYY-MM-DD
})();

const hint = computed(() => {
  switch (form.status) {
    case 'draft':
      return 'Removes the publish date. The article stays unpublished until you schedule or publish it.';
    case 'scheduled':
      return props.article.status === 'scheduled'
        ? 'Pick a new publish date.'
        : 'Goes live automatically on the date you pick.';
    case 'published':
      return props.article.status === 'archived'
        ? 'Restores the article and keeps its original publish date.'
        : 'Goes live immediately, dated today.';
    case 'archived':
      return 'Takes the article out of circulation. You can restore it later.';
    default:
      return '';
  }
});

watch(open, (isOpen) => {
  if (!isOpen) return;
  form.defaults(defaults());
  form.reset();
  form.clearErrors();
});

watch(
  () => form.status,
  () => form.clearErrors('published_at'),
);

function submit() {
  form
    .transform((data) => ({
      ...data,
      published_at: data.status === 'scheduled' ? data.published_at : null,
    }))
    .submit(newsEvents.update(props.article.slug), {
      preserveScroll: true,
      onSuccess: () => {
        open.value = false;
        toast.success('Status successfully changed.');
      },
    });
}
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button variant="outline" size="sm" class="cursor-pointer">
        <ArrowLeftRightIcon class="size-4" /> Change status
      </Button>
    </DialogTrigger>

    <DialogContent class="sm:max-w-md">
      <form class="grid gap-5" @submit.prevent="submit">
        <DialogHeader>
          <DialogTitle>Change status</DialogTitle>
          <DialogDescription class="flex items-center gap-2">
            Currently
            <Badge :class="statusClasses[article.status]">
              {{ article.status_label }}
            </Badge>
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-2">
          <Label>New status</Label>
          <Select v-model="form.status">
            <SelectTrigger class="w-full cursor-pointer">
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="t in transitions"
                :key="t.value"
                :value="t.value"
              >
                {{ t.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p class="text-xs text-muted-foreground">{{ hint }}</p>
          <p v-if="form.errors.status" class="text-sm text-destructive">
            {{ form.errors.status }}
          </p>
        </div>

        <div v-if="isScheduled" class="grid gap-2">
          <Label for="status_published_at">Publish date</Label>
          <Input
            id="status_published_at"
            v-model="form.published_at"
            type="date"
            :min="minDate"
          />
          <p v-if="form.errors.published_at" class="text-sm text-destructive">
            {{ form.errors.published_at }}
          </p>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="open = false">
            Cancel
          </Button>
          <Button
            type="submit"
            class="cursor-pointer"
            :disabled="form.processing"
          >
            <Loader2Icon v-if="form.processing" class="size-4 animate-spin" />
            Save
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
