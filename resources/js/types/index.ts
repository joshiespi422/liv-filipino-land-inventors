export * from './auth';
export * from './navigation';
export * from './ui';
export * from './loan';
export * from './dashboard';

export type ApiResponse<T> = {
  data: T;
};

export interface DetailItem {
  label: string;
  value: any;
  type?: 'text' | 'image';
  class?: string;
  full?: boolean;
}
