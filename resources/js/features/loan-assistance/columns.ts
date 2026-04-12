import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { LoanAssistance } from '@/types';
import { Badge } from '@/components/ui/badge';

export const columns: ColumnDef<LoanAssistance>[] = [
  {
    accessorKey: 'user_name',
    header: 'Member',
  },
  {
    accessorKey: 'amount',
    header: 'Amount',
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue('amount'));
      const formatted = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      }).format(amount);

      return h('div', { class: 'font-medium' }, formatted);
    },
  },
  {
    accessorKey: 'term_months',
    header: 'Duration',
    cell: ({ row }) => {
      const months = row.getValue('term_months') as number;
      return `${months} month${months > 1 ? 's' : ''}`;
    },
  },
  {
    accessorKey: 'interest_rate_display',
    header: 'Interest',
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;
      const badgeClass = {
        'bg-blue-500 hover:bg-blue-600': status === 'active',
        'bg-green-500 hover:bg-green-600': status === 'finished',
        'bg-amber-500 hover:bg-amber-600': status === 'pending',
        'bg-rose-500 hover:bg-rose-600':
          status === 'cancelled' ||
          status === 'overdue' ||
          status === 'rejected',
      };
      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: [badgeClass, 'text-white pb-1'] },
          () => status || '-',
        ),
      ]);
    },
  },
  {
    accessorKey: 'start_date_display',
    header: 'Start Date',
  },
  {
    accessorKey: 'end_date_display',
    header: 'End Date',
  },
];
