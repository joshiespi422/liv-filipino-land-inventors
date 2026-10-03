import type { NewsArticleDetail } from '@/types';
import {
  CalendarDaysIcon,
  ClockIcon,
  EyeIcon,
  Link2Icon,
  UserIcon,
} from 'lucide-vue-next';
import { formatDateTime } from '@/lib/news-events';

export function getNewsArticleDetails(article: NewsArticleDetail) {
  const safeSourceUrl =
    article.source_url && /^https?:\/\//i.test(article.source_url)
      ? article.source_url
      : null;

  const sourceLabel = (() => {
    if (article.source_name) return article.source_name;
    if (!safeSourceUrl) return null;

    try {
      return new URL(safeSourceUrl).hostname.replace(/^www\./, '');
    } catch {
      return safeSourceUrl;
    }
  })();

  return [
    {
      key: 'author',
      label: 'Author',
      icon: UserIcon,
      value: article.user,
    },
    {
      key: 'source',
      label: 'Source',
      icon: Link2Icon,
      value: sourceLabel,
      href: safeSourceUrl,
    },
    {
      key: 'views',
      label: 'Views',
      icon: EyeIcon,
      value: article.views_count.toLocaleString(),
    },
    {
      key: 'created',
      label: 'Created',
      icon: CalendarDaysIcon,
      value: formatDateTime(article.created_at),
    },
    {
      key: 'published',
      label: article.status === 'scheduled' ? 'Scheduled for' : 'Published',
      icon: ClockIcon,
      value: article.published_at ? formatDateTime(article.published_at) : null,
    },
  ].filter((item) => !!item.value);
}
