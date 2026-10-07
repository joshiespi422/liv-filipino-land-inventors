<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import { ArrowLeftIcon, ImagePlusIcon, Loader2Icon } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
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
import { toast } from 'vue-sonner';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { statusClasses } from '@/lib/news-events';
import newsEvents from '@/routes/news-events';
import type { NewsArticleDetail, NewsCategoryOption } from '@/types';

defineOptions({ layout: [] });

const props = defineProps<{
  article: NewsArticleDetail;
  news_categories: NewsCategoryOption[];
}>();

const truncateTitle = (text: string, max = 30) =>
  text.length <= max ? text : text.slice(0, max - 3) + '...';

const breadcrumbs = computed(() => [
  { title: 'News & Events', href: newsEvents.index() },
  {
    title: truncateTitle(props.article.title),
    href: newsEvents.show(props.article.slug),
  },
  { title: 'Edit', href: newsEvents.edit(props.article.slug) },
]);

const form = useForm({
  news_category_id: String(props.article.news_category_id),
  title: props.article.title,
  content: props.article.content,
  image: null as File | null, // null = keep the current image
  source_name: props.article.source_name ?? '',
  source_url: props.article.source_url ?? '',
});

// Image preview: current image until a new file is picked
const blobUrl = ref<string | null>(null);
const preview = computed(() => blobUrl.value ?? props.article.image);

function onImageChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  form.image = file;
  form.clearErrors('image');
  if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
  blobUrl.value = file ? URL.createObjectURL(file) : null;
}

onBeforeUnmount(() => {
  if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
});

function submit() {
  form
    .transform(({ image, ...rest }) => ({
      ...rest,
      ...(image ? { image } : {}),
      // PHP doesn't parse multipart PUT bodies, so spoof the method over POST
      _method: 'put',
    }))
    .post(newsEvents.update(props.article.slug).url, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Article successfully updated.');
      },
    });
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="`Edit: ${truncateTitle(article.title)}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight">Edit Article</h1>
            <Badge :class="statusClasses[article.status]">
              {{ article.status_label }}
            </Badge>
          </div>
          <p class="text-muted-foreground">
            <template v-if="article.status === 'published'">
              This article is live, so changes are visible right away.
            </template>
            <template v-else>
              Status and publish date are changed from the article page.
            </template>
          </p>
        </div>

        <Button variant="ghost" size="sm" as-child class="-ml-2">
          <Link :href="newsEvents.show(article.slug)">
            <ArrowLeftIcon class="size-4" /> Back to article
          </Link>
        </Button>
      </div>

      <form
        class="grid items-start gap-6 lg:grid-cols-3"
        @submit.prevent="submit"
      >
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle>Content</CardTitle>
            <CardDescription>The title, cover image and body.</CardDescription>
          </CardHeader>
          <CardContent class="grid gap-5">
            <div class="grid gap-2">
              <Label for="title">Title</Label>
              <Input id="title" v-model="form.title" maxlength="255" />
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
                <ImagePlusIcon v-else class="size-8" />
              </div>
              <Input
                id="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="onImageChange"
              />
              <p class="text-xs text-muted-foreground">
                Leave empty to keep the current image. JPG, PNG or WebP, up to 2
                MB.
              </p>
              <p v-if="form.errors.image" class="text-sm text-destructive">
                {{ form.errors.image }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="content">Content</Label>
              <Textarea id="content" v-model="form.content" rows="14" />
              <p v-if="form.errors.content" class="text-sm text-destructive">
                {{ form.errors.content }}
              </p>
            </div>
          </CardContent>
        </Card>

        <div class="flex flex-col gap-6">
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
                <Label for="source_name">
                  Source name
                  <span class="font-normal text-muted-foreground"
                    >(optional)</span
                  >
                </Label>
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
                <Label for="source_url">
                  Source URL
                  <span class="font-normal text-muted-foreground"
                    >(optional)</span
                  >
                </Label>
                <Input
                  id="source_url"
                  v-model="form.source_url"
                  type="url"
                  placeholder="https://"
                />
                <p
                  v-if="form.errors.source_url"
                  class="text-sm text-destructive"
                >
                  {{ form.errors.source_url }}
                </p>
              </div>
            </CardContent>
          </Card>

          <div class="flex gap-2">
            <Button
              type="submit"
              class="flex-1 cursor-pointer"
              :disabled="form.processing || !form.isDirty"
            >
              <Loader2Icon v-if="form.processing" class="size-4 animate-spin" />
              Save changes
            </Button>
            <Button type="button" variant="outline" as-child>
              <Link :href="newsEvents.show(article.slug)">Cancel</Link>
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
