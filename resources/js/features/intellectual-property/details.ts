import type { IntellectualPropertyDetail, DetailItem } from '@/types';

export function getIPDetails(ip: IntellectualPropertyDetail): DetailItem[] {
  return [
    { label: 'Member', value: ip.user_name },
    { label: 'Status', value: ip.status_name.replaceAll('_', ' ') },
    {
      label: 'Creation Type',
      value: ip.creation_type.replaceAll('_', ' '),
      class: 'capitalize',
    },
    { label: 'Form Type', value: ip.form_type },
    { label: 'Title', value: ip.title, full: true },
    { label: 'Description', value: ip.description, full: true },
    { label: 'Applicability', value: ip.applicability, full: true },
  ];
}
