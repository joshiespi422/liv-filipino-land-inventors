export * from './auth';
export * from './navigation';
export * from './ui';
export * from './loan';
export * from './dashboard';
export * from './business-training';
export * from './intellectual-property';

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

export type FormFieldType = 'text' | 'textarea' | 'number' | 'select' | 'file';

export interface FormField {
  type: FormFieldType;
  name: string;
  label: string;
  placeholder?: string;
  options?: { label: string; value: string | number }[];
  required?: boolean;
  col?: number;
  accept?: string;
}
