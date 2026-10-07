<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  ArrowLeftIcon,
  CalendarDaysIcon,
  ExternalLinkIcon,
  ImageOffIcon,
  NewspaperIcon,
  PencilIcon,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import ChangeStatusDialog from '@/components/news-events/ChangeStatusDialog.vue';
import DeleteArticleDialog from '@/components/news-events/DeleteArticleDialog.vue';
import newsEvents from '@/routes/news-events';
import { formatDate, statusClasses } from '@/lib/news-events';
import { getNewsArticleDetails } from '@/features/news-events/details';
import type { NewsArticleDetail, NewsArticleAbilities } from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  article: NewsArticleDetail;
  can_mutate: boolean;
  abilities: NewsArticleAbilities;
}>();

const truncateTitle = (text: string, max = 30) =>
  text.length <= max ? text : text.slice(0, max - 3) + '...';

// computed so breadcrumbs update when navigating between articles
const breadcrumbs = computed(() => [
  { title: 'News & Events', href: newsEvents.index() },
  {
    title: truncateTitle(props.article.title),
    href: newsEvents.show(props.article.slug),
  },
]);

// draft / scheduled -> created_at, published / archived -> published_at
const showsPublishedDate = computed(
  () =>
    ['published', 'archived'].includes(props.article.status) &&
    !!props.article.published_at,
);
const headerDateLabel = computed(() =>
  showsPublishedDate.value ? 'Published on' : 'Created on',
);
const headerDate = computed(() =>
  showsPublishedDate.value
    ? props.article.published_at!
    : props.article.created_at,
);

const details = computed(() => getNewsArticleDetails(props.article));
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="article.title" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-6">
      <!-- Toolbar -->
      <div class="flex items-center justify-between gap-3">
        <Button variant="ghost" size="sm" as-child class="-ml-2">
          <Link :href="newsEvents.index()">
            <ArrowLeftIcon class="size-4" /> Back to News & Events
          </Link>
        </Button>

        <div
          v-if="can_mutate"
          class="flex flex-wrap items-center justify-end gap-2"
        >
          <ChangeStatusDialog
            v-if="abilities.transitions.length"
            :article="article"
            :transitions="abilities.transitions"
          />
          <Button v-if="abilities.edit" variant="outline" size="sm" as-child>
            <Link :href="newsEvents.edit(article.slug)">
              <PencilIcon class="size-4" /> Edit
            </Link>
          </Button>
          <DeleteArticleDialog
            :article="article"
            :can-delete="abilities.delete"
          />
        </div>
      </div>

      <!-- Article -->
      <article class="flex flex-col gap-6">
        <header class="flex flex-col gap-4">
          <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <Badge :class="statusClasses[article.status]">
              {{ article.status_label }}
            </Badge>
            <Badge v-if="article.category" variant="outline" class="gap-1">
              <NewspaperIcon class="size-3" />
              {{ article.category }}
            </Badge>
            <span
              class="flex items-center gap-1.5 text-sm text-muted-foreground"
            >
              <CalendarDaysIcon class="size-4" />
              {{ headerDateLabel }}
              <time :datetime="headerDate">{{ formatDate(headerDate) }}</time>
            </span>
          </div>

          <h1
            class="text-3xl leading-tight font-bold tracking-tight text-balance lg:text-4xl"
          >
            {{ article.title }}
          </h1>
        </header>

        <div
          class="aspect-video w-full overflow-hidden rounded-xl border bg-muted"
        >
          <img
            v-if="article.image"
            :src="article.image"
            :alt="article.title"
            class="size-full object-cover"
          />
          <div
            v-else
            class="flex size-full items-center justify-center text-muted-foreground"
          >
            <ImageOffIcon class="size-10" />
          </div>
        </div>

        <!-- Rendered as plain text; line breaks are preserved -->
        <div class="text-base leading-relaxed whitespace-pre-line">
          {{ article.content }}
        </div>
      </article>

      <Separator />

      <!-- Article details -->
      <Card>
        <CardHeader>
          <CardTitle class="text-base">Article details</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <div
              v-for="item in details"
              :key="item.key"
              class="flex items-start gap-3"
            >
              <div
                class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
              >
                <component :is="item.icon" class="size-4" />
              </div>
              <div class="min-w-0">
                <dt class="text-xs text-muted-foreground">{{ item.label }}</dt>
                <dd class="text-sm font-medium wrap-break-word">
                  <a
                    v-if="item.href"
                    :href="item.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 underline-offset-4 hover:text-primary hover:underline"
                  >
                    {{ item.value }}
                    <ExternalLinkIcon class="size-3.5 shrink-0" />
                  </a>
                  <template v-else>{{ item.value }}</template>
                </dd>
              </div>
            </div>
          </dl>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
