<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
  CalendarDaysIcon,
  ImageOffIcon,
  NewspaperIcon,
  XIcon,
  SearchIcon,
  PlusIcon,
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
import { Input } from '@/components/ui/input';
import { Card, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import Pagination from '@/components/Pagination.vue';
import newsEvents from '@/routes/news-events';
import { formatDate, statusClasses } from '@/lib/news-events';
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
  () =>
    !!props.filters.status ||
    !!props.filters.category ||
    !!props.filters.search,
);

// Search keeps local state (so typing is instant) and is debounced to the server
const search = ref(props.filters.search ?? '');
let lastSent = props.filters.search ?? '';
let timer: ReturnType<typeof setTimeout> | undefined;

function applyFilters(next: Partial<NewsFilters> = {}) {
  clearTimeout(timer);

  const merged: NewsFilters = {
    status: props.filters.status,
    category: props.filters.category,
    search: search.value.trim() || null,
    ...next,
  };

  const query: Record<string, string> = {};
  if (merged.status) query.status = merged.status;
  if (merged.category) query.category = merged.category;
  if (merged.search) query.search = merged.search;
  lastSent = merged.search ?? '';

  // page is omitted on purpose: any filter change goes back to page 1
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

watch(search, (value) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    if (value.trim() !== (props.filters.search ?? '')) applyFilters();
  }, 350);
});

// Sync the input when the URL changes from outside (back/forward navigation),
// but ignore the echo of what we just sent so typing is never overwritten.
watch(
  () => props.filters.search,
  (value) => {
    if ((value ?? '') !== lastSent) {
      lastSent = value ?? '';
      search.value = value ?? '';
    }
  },
);

onBeforeUnmount(() => clearTimeout(timer));

const onStatusChange = (value: unknown) =>
  applyFilters({
    status: value === ALL ? null : (value as NewsArticleStatus),
  });

const onCategoryChange = (value: unknown) =>
  applyFilters({ category: value === ALL ? null : (value as string) });

function resetFilters() {
  search.value = '';
  applyFilters({ status: null, category: null, search: null });
}
</script>

<template>
  <Head title="News & Events" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">News & Events</h1>
        <p class="text-muted-foreground">View and manage news and events.</p>
      </div>

      <div>
        <Button v-if="can_mutate" variant="default" as-child class="-ml-2">
          <Link :href="newsEvents.create()">
            <PlusIcon class="size-4" /> Create Article
          </Link>
        </Button>
      </div>
    </div>

    <!-- Toolbar -->
    <div
      class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="relative w-full lg:max-w-sm">
        <SearchIcon
          class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
          v-model="search"
          type="search"
          placeholder="Search by title..."
          class="pl-9"
        />
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
      <Link
        v-for="article in news_articles.data"
        :key="article.id"
        :href="newsEvents.show(article.slug)"
        class="group block rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        <Card
          class="h-full gap-0 overflow-hidden pt-0 transition-shadow group-hover:shadow-md"
        >
          <div class="relative aspect-video w-full overflow-hidden bg-muted">
            <img
              v-if="article.image"
              :src="article.image"
              :alt="article.title"
              loading="lazy"
              class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
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

          <CardHeader class="flex-1 gap-2 pt-4">
            <div class="flex">
              <Badge v-if="article.category" variant="outline" class="gap-1">
                <Newspaper class="size-3" />
                {{ article.category }}
              </Badge>
            </div>
            <CardTitle
              class="line-clamp-2 text-base leading-snug group-hover:text-primary"
            >
              {{ article.title }}
            </CardTitle>
          </CardHeader>

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
            <span class="pl-5"
              >Created {{ formatDate(article.created_at) }}</span
            >
          </CardFooter>
        </Card>
      </Link>
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
