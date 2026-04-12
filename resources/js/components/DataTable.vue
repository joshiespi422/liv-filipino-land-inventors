<script setup lang="ts" generic="TData, TValue">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/components/ui/table/utils';
import {
  type ColumnDef,
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  useVueTable,
} from '@tanstack/vue-table';
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronsLeftIcon,
  ChevronsRightIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  searchPlaceholder?: string;
}>();

// --- Table State ---
const globalFilter = ref('');
const pagination = ref({
  pageIndex: 0,
  pageSize: 10,
});

// --- Table Instance ---
const table = useVueTable({
  data: computed(() => props.data),
  columns: props.columns,
  state: {
    get globalFilter() {
      return globalFilter.value;
    },
    get pagination() {
      return pagination.value;
    },
  },
  // --- Cleaned up Updaters using your utility ---
  onGlobalFilterChange: (updater) => valueUpdater(updater, globalFilter),
  onPaginationChange: (updater) => valueUpdater(updater, pagination),

  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  // --- Config ---
  manualPagination: false,
  manualFiltering: false,
});

// --- Computed Values for Display ---
const pageCount = computed(() => table.getPageCount());
const totalResults = computed(() => table.getFilteredRowModel().rows.length);

const fromResult = computed(() => {
  if (totalResults.value === 0) return 0;
  return pagination.value.pageIndex * pagination.value.pageSize + 1;
});

const toResult = computed(() => {
  const end = (pagination.value.pageIndex + 1) * pagination.value.pageSize;
  return end > totalResults.value ? totalResults.value : end;
});
</script>

<template>
  <div class="flex flex-col gap-4 rounded-2xl border-2 bg-card p-5 pb-8">
    <!-- Search Filter -->
    <div
      class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
    >
      <Input
        v-model="globalFilter"
        type="text"
        :placeholder="searchPlaceholder || 'Search all columns...'"
        class="max-w-xl lg:max-w-sm"
      />
      <div class="flex items-center gap-2">
        <slot name="custom-actions" />
      </div>
    </div>

    <!-- Data Table -->
    <div class="w-full overflow-x-auto rounded-md border">
      <Table>
        <TableHeader class="bg-accent">
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"
              class="text-center"
            >
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
              class="text-center"
            >
              <TableCell
                v-for="cell in row.getVisibleCells()"
                :key="cell.id"
                class="py-4"
              >
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>
          <TableRow v-else>
            <TableCell
              :colspan="columns.length"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No results found.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination Controls -->
    <div
      class="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row"
    >
      <div class="text-sm text-muted-foreground">
        Showing <strong>{{ fromResult }}</strong> to
        <strong>{{ toResult }}</strong> of
        <strong>{{ totalResults }}</strong> results
      </div>

      <div class="flex flex-col items-center gap-4 sm:flex-row sm:gap-6">
        <div class="flex items-center space-x-2">
          <p class="text-sm font-medium">Rows per page</p>
          <Select
            :model-value="`${pagination.pageSize}`"
            @update:model-value="(val) => table.setPageSize(Number(val))"
          >
            <SelectTrigger class="h-8 w-18">
              <SelectValue />
            </SelectTrigger>
            <SelectContent side="top">
              <SelectItem
                v-for="pageSize in [10, 20, 30, 40, 50]"
                :key="pageSize"
                :value="`${pageSize}`"
              >
                {{ pageSize }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="flex w-25 items-center justify-center text-sm font-medium">
          Page {{ pagination.pageIndex + 1 }} of {{ pageCount }}
        </div>

        <div class="flex items-center space-x-2">
          <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="!table.getCanPreviousPage()"
            @click="table.setPageIndex(0)"
          >
            <ChevronsLeftIcon class="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            class="h-8 w-8 p-0"
            :disabled="!table.getCanPreviousPage()"
            @click="table.previousPage()"
          >
            <ChevronLeftIcon class="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            class="h-8 w-8 p-0"
            :disabled="!table.getCanNextPage()"
            @click="table.nextPage()"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="!table.getCanNextPage()"
            @click="table.setPageIndex(pageCount - 1)"
          >
            <ChevronsRightIcon class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
