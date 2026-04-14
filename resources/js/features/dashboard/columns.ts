import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { PendingUser } from '@/types';
import { Badge } from '@/components/ui/badge';

const STATUS_STYLES: Record<string, string> = {
  pending_for_member: 'bg-amber-500 hover:bg-amber-600',
  active: 'bg-blue-500 hover:bg-blue-600',
  rejected: 'bg-rose-500 hover:bg-rose-600',
};

export const pendingUserColumns: ColumnDef<PendingUser>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
  },
  {
    accessorKey: 'email',
    header: 'Email',
  },
  {
    accessorKey: 'phone',
    header: 'Phone',
  },
  {
    accessorKey: 'address',
    header: () => h('div', { class: 'text-center' }, 'Address'),
    cell: ({ row }) =>
      h(
        'div',
        {
          class: 'max-w-80 truncate mx-auto',
        },
        row.getValue('address'),
      ),
  },
  {
    accessorKey: 'user_type_name',
    header: () => h('div', { class: 'text-center' }, 'User Type'),
    cell: ({ row }) =>
      h(
        'div',
        { class: 'text-center capitalize' },
        row.getValue('user_type_name'),
      ),
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center ' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;

      const badgeClass =
        STATUS_STYLES[status] ?? 'bg-gray-500 hover:bg-gray-600';
      const formattedStatus = status?.replaceAll('_', ' ');

      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: [badgeClass, 'text-white pb-1'] },
          () => formattedStatus || '-',
        ),
      ]);
    },
  },
];
