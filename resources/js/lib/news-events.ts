import type { NewsArticleStatus } from '@/types';

export const statusClasses: Record<NewsArticleStatus, string> = {
  draft:
    'border-transparent bg-cyan-100 text-cyan-700 dark:bg-cyan-800 dark:text-cyan-300',
  scheduled:
    'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
  published:
    'border-transparent bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
  archived:
    'border-transparent bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
};

export const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

export const formatDateTime = (iso: string) =>
  new Date(iso).toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
