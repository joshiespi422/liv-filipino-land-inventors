import type { LaravelPaginationItem } from './seller/product';

export type NewsArticleStatus =
  | 'draft'
  | 'scheduled'
  | 'published'
  | 'archived';

export interface NewsArticleIndex {
  id: number;
  slug: string;
  title: string;
  image: string | null;
  category: string | null;
  status: NewsArticleStatus;
  status_label: string;
  created_at: string;
  published_at: string | null;
}

export interface NewsCategoryOption {
  id: number;
  name: string;
  slug: string;
}
export interface NewsStatusOption {
  value: NewsArticleStatus;
  label: string;
}
export interface NewsFilters {
  status: NewsArticleStatus | null;
  category: string | null;
}

export interface PaginatedNewsArticles {
  data: NewsArticleIndex[];

  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };

  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: LaravelPaginationItem[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}
