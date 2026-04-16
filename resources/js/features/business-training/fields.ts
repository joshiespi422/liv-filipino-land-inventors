import type { FormField } from '@/types';

export const businessTrainingTypeFields: FormField[] = [
  {
    type: 'text',
    name: 'name',
    label: 'Type Name',
    placeholder: 'Enter Business training type',
    required: true,
  },
  {
    type: 'file',
    name: 'icon',
    label: 'Icon',
    placeholder: 'Upload icon',
    required: true,
  },
];
