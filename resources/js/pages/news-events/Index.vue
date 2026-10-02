<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  CalendarDaysIcon,
  ImageOffIcon,
  NewspaperIcon,
  XIcon,
} from 'lucide-vue-next';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import Pagination from '@/components/Pagination.vue';
import newsEvents from '@/routes/news-events';
import type {
  NewsArticleStatus,
  NewsCategoryOption,
  NewsFilters,
  NewsStatusOption,
  PaginatedNewsArticles,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [{ title: 'News & Events', href: newsEvents.index() }],
  },
});

const props = defineProps<{
  news_articles: PaginatedNewsArticles;
  news_categories: NewsCategoryOption[];
  statuses: NewsStatusOption[];
  filters: NewsFilters;
  can_mutate: boolean;
}>();

const ALL = 'all';

const status = computed(() => props.filters.status ?? ALL);
const category = computed(() => props.filters.category ?? ALL);
const hasFilters = computed(
  () => !!props.filters.status || !!props.filters.category,
);

function applyFilters(next: Partial<NewsFilters>) {
  const merged = { ...props.filters, ...next };
  const query: Record<string, string> = {};

  if (merged.status) query.status = merged.status;
  if (merged.category) query.category = merged.category;

  // page is omitted on purpose, so changing a filter goes back to page 1
  router.get(
    newsEvents.index({ query }),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['news_articles', 'filters'],
    },
  );
}

const onStatusChange = (value: unknown) =>
  applyFilters({
    status: value === ALL ? null : (value as NewsArticleStatus),
  });

const onCategoryChange = (value: unknown) =>
  applyFilters({ category: value === ALL ? null : (value as string) });

const resetFilters = () => applyFilters({ status: null, category: null });

const statusClasses: Record<NewsArticleStatus, string> = {
  draft:
    'border-transparent bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
  scheduled:
    'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
  published:
    'border-transparent bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
  archived:
    'border-transparent bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
};

const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
</script>

<template>
  <Head title="News & Events" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header + filters -->
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold tracking-tight">News & Events</h1>
        <p class="text-muted-foreground">View and manage news and events.</p>
      </div>

      <div
        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center"
      >
        <Select :model-value="status" @update:model-value="onStatusChange">
          <SelectTrigger class="w-full cursor-pointer sm:w-40">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="ALL">All statuses</SelectItem>
            <SelectItem v-for="s in statuses" :key="s.value" :value="s.value">
              {{ s.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select :model-value="category" @update:model-value="onCategoryChange">
          <SelectTrigger class="w-full cursor-pointer sm:w-48">
            <SelectValue placeholder="Category" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="ALL">All categories</SelectItem>
            <SelectItem
              v-for="c in news_categories"
              :key="c.id"
              :value="c.slug"
            >
              {{ c.name }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Button
          v-if="hasFilters"
          variant="destructive"
          size="sm"
          class="cursor-pointer"
          @click="resetFilters"
        >
          <XIcon class="size-4" /> Reset
        </Button>
      </div>
    </div>

    <!-- Cards -->
    <div
      v-if="news_articles.data.length"
      class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      <Card
        v-for="article in news_articles.data"
        :key="article.id"
        class="gap-0 overflow-hidden pt-0 transition-shadow hover:shadow-md"
      >
        <div class="relative aspect-video w-full overflow-hidden bg-muted">
          <img
            v-if="article.image"
            :src="article.image"
            :alt="article.title"
            loading="lazy"
            class="size-full object-cover"
          />
          <div
            v-else
            class="flex size-full items-center justify-center text-muted-foreground"
          >
            <ImageOffIcon class="size-8" />
          </div>

          <Badge
            :class="statusClasses[article.status]"
            class="absolute top-3 left-3 shadow-sm"
          >
            {{ article.status_label }}
          </Badge>
        </div>

        <CardHeader class="gap-2 pt-4">
          <div class="flex">
            <Badge v-if="article.category" variant="outline" class="gap-1">
              <NewspaperIcon class="size-3" />
              {{ article.category }}
            </Badge>
          </div>
          <CardTitle class="line-clamp-2 text-base leading-snug">
            {{ article.title }}
          </CardTitle>
        </CardHeader>

        <CardContent class="flex-1" />

        <CardFooter
          class="flex-col items-start gap-1 pt-4 text-xs text-muted-foreground"
        >
          <span class="flex items-center gap-1.5">
            <CalendarDaysIcon class="size-3.5" />
            <template v-if="article.published_at">
              {{
                article.status === 'scheduled' ? 'Scheduled for' : 'Published'
              }}
              {{ formatDate(article.published_at) }}
            </template>
            <template v-else>Not published</template>
          </span>
          <span class="pl-5">Created {{ formatDate(article.created_at) }}</span>
        </CardFooter>
      </Card>
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="flex flex-1 flex-col items-center justify-center gap-2 py-16 text-muted-foreground"
    >
      <NewspaperIcon class="size-10" />
      <p class="font-medium">No articles found</p>
      <p v-if="hasFilters" class="text-sm">
        Try changing or resetting the filters.
      </p>
    </div>

    <Pagination :links="news_articles.meta.links" />
  </div>
</template>
