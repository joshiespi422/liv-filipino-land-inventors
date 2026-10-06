<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { ArrowLeftIcon, ImagePlus, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';
import newsEvents from '@/routes/news-events';
import type {
  NewsArticleStatus,
  NewsCategoryOption,
  NewsStatusOption,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'News & Events', href: newsEvents.index() },
      { title: 'Create Article', href: newsEvents.create() },
    ],
  },
});

defineProps<{
  news_categories: NewsCategoryOption[];
  statuses: NewsStatusOption[];
}>();

const form = useForm({
  news_category_id: '',
  title: '',
  content: '',
  image: null as File | null,
  status: 'draft' as NewsArticleStatus,
  published_at: '',
  source_name: '',
  source_url: '',
});

const isScheduled = computed(() => form.status === 'scheduled');

const statusHints: Partial<Record<NewsArticleStatus, string>> = {
  draft: 'Saved without a publish date. Only managers can see it.',
  scheduled: 'Goes live automatically on the date you pick.',
  published: 'Goes live immediately, dated today.',
};

// earliest selectable date = tomorrow (the server enforces this too)
const minDate = (() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toLocaleDateString('en-CA'); // YYYY-MM-DD
})();

watch(
  () => form.status,
  () => form.clearErrors('published_at'),
);

// Image preview
const preview = ref<string | null>(null);

function onImageChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  form.image = file;
  form.clearErrors('image');
  if (preview.value) URL.revokeObjectURL(preview.value);
  preview.value = file ? URL.createObjectURL(file) : null;
}

onBeforeUnmount(() => {
  if (preview.value) URL.revokeObjectURL(preview.value);
});

function submit() {
  form
    .transform((data) => ({
      ...data,
      // the date only means something when scheduled
      published_at: data.status === 'scheduled' ? data.published_at : null,
    }))
    .submit(newsEvents.store(), {
      onSuccess: () => {
        toast.success('Article successfully created.');
      },
    });
}
</script>

<template>
  <Head title="Create Article" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Create Article</h1>
        <p class="text-muted-foreground">Write a news article or event.</p>
      </div>

      <Button variant="ghost" size="sm" as-child class="-ml-2">
        <Link :href="newsEvents.index()">
          <ArrowLeftIcon class="size-4" /> Back to News & Events
        </Link>
      </Button>
    </div>

    <form
      class="grid items-start gap-6 lg:grid-cols-3"
      @submit.prevent="submit"
    >
      <!-- Main content -->
      <Card class="lg:col-span-2">
        <CardHeader>
          <CardTitle>Content</CardTitle>
          <CardDescription>The title, cover image and body.</CardDescription>
        </CardHeader>
        <CardContent class="grid gap-5">
          <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
              id="title"
              v-model="form.title"
              maxlength="255"
              placeholder="Article title"
            />
            <p v-if="form.errors.title" class="text-sm text-destructive">
              {{ form.errors.title }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label for="image">Cover image</Label>
            <div
              class="flex aspect-video w-full items-center justify-center overflow-hidden rounded-lg border border-dashed bg-muted text-muted-foreground"
            >
              <img
                v-if="preview"
                :src="preview"
                alt="Cover preview"
                class="size-full object-cover"
              />
              <ImagePlus v-else class="size-8" />
            </div>
            <Input
              id="image"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="onImageChange"
            />
            <p class="text-xs text-muted-foreground">
              JPG, PNG or WebP, up to 2 MB.
            </p>
            <p v-if="form.errors.image" class="text-sm text-destructive">
              {{ form.errors.image }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label for="content">Content</Label>
            <Textarea
              id="content"
              v-model="form.content"
              rows="14"
              placeholder="Write the article..."
            />
            <p v-if="form.errors.content" class="text-sm text-destructive">
              {{ form.errors.content }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Sidebar -->
      <div class="flex flex-col gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Publishing</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-5">
            <div class="grid gap-2">
              <Label>Status</Label>
              <Select v-model="form.status">
                <SelectTrigger class="w-full cursor-pointer">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="s in statuses"
                    :key="s.value"
                    :value="s.value"
                  >
                    {{ s.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p class="text-xs text-muted-foreground">
                {{ statusHints[form.status] }}
              </p>
              <p v-if="form.errors.status" class="text-sm text-destructive">
                {{ form.errors.status }}
              </p>
            </div>

            <div v-if="isScheduled" class="grid gap-2">
              <Label for="published_at">Publish date</Label>
              <Input
                id="published_at"
                v-model="form.published_at"
                type="date"
                :min="minDate"
              />
              <p
                v-if="form.errors.published_at"
                class="text-sm text-destructive"
              >
                {{ form.errors.published_at }}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-5">
            <div class="grid gap-2">
              <Label>Category</Label>
              <Select v-model="form.news_category_id">
                <SelectTrigger class="w-full cursor-pointer">
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="c in news_categories"
                    :key="c.id"
                    :value="String(c.id)"
                  >
                    {{ c.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p
                v-if="form.errors.news_category_id"
                class="text-sm text-destructive"
              >
                {{ form.errors.news_category_id }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="source_name"
                >Source name
                <span class="font-normal text-muted-foreground"
                  >(optional)</span
                ></Label
              >
              <Input
                id="source_name"
                v-model="form.source_name"
                maxlength="255"
              />
              <p
                v-if="form.errors.source_name"
                class="text-sm text-destructive"
              >
                {{ form.errors.source_name }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="source_url"
                >Source URL
                <span class="font-normal text-muted-foreground"
                  >(optional)</span
                ></Label
              >
              <Input
                id="source_url"
                v-model="form.source_url"
                type="url"
                placeholder="https://"
              />
              <p v-if="form.errors.source_url" class="text-sm text-destructive">
                {{ form.errors.source_url }}
              </p>
            </div>
          </CardContent>
        </Card>

        <div class="flex gap-2">
          <Button
            type="submit"
            class="flex-1 cursor-pointer"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
            Create Article
          </Button>
          <Button type="button" variant="outline" as-child>
            <Link :href="newsEvents.index()">Cancel</Link>
          </Button>
        </div>
      </div>
    </form>
  </div>
</template>
