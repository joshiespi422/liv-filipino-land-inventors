<script setup lang="ts">
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import dashboard from '@/routes/dashboard';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { getPendingUserColumns } from '@/features/dashboard/columns';
import type { PendingUser, PendingUserDetail, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard.index(),
      },
    ],
  },
});

const props = defineProps<{
  pendingUsers: {
    data: PendingUser[];
  };
}>();

// state for details
const selectedUser = ref<PendingUserDetail | null>(null);
const isDetailsOpen = ref(false);
// inertia http
const http = useHttp();

// fetch user
const showUserDetails = async (id: number) => {
  isDetailsOpen.value = true;
  selectedUser.value = null;

  try {
    const response = (await http.get(
      dashboard.users.show.url(id),
    )) as ApiResponse<PendingUserDetail>;

    selectedUser.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

// state for manage
const isConfirmOpen = ref(false);
const selectedUserId = ref<number | null>(null);
const actionType = ref<'approve' | 'decline' | null>(null);

const form = useForm({
  action: '' as 'approve' | 'decline',
});

const openConfirm = (id: number, action: 'approve' | 'decline') => {
  selectedUserId.value = id;
  actionType.value = action;
  isConfirmOpen.value = true;
};

const approveUser = (id: number) => openConfirm(id, 'approve');
const declineUser = (id: number) => openConfirm(id, 'decline');

const handleUserAction = () => {
  if (!selectedUserId.value || !actionType.value) return;

  form.action = actionType.value;

  form.patch(dashboard.users.updateStatus.url(selectedUserId.value), {
    preserveScroll: true,

    onSuccess: () => {
      isConfirmOpen.value = false;
      form.reset();
      toast.success(`User has been ${actionType.value}d successfully!`);
    },
  });
};

const columns = getPendingUserColumns({
  showUserDetails,
  approveUser,
  declineUser,
});

const userDetails = computed(() => {
  if (!selectedUser.value) return [];

  const u = selectedUser.value;

  return [
    { label: 'Name', value: u.name },
    { label: 'Email', value: u.email },
    { label: 'Phone', value: u.phone },
    { label: 'Gender', value: u.gender },
    { label: 'User Type', value: u.user_type_name, class: 'capitalize' },
    { label: 'Status', value: u.status_name.replaceAll('_', ' ') },
    { label: 'Valid ID Type', value: u.valid_id_type },
    { label: 'Valid ID Number', value: u.valid_id_number },
    { label: 'Front ID', value: u.front_id_url, type: 'image' },
    { label: 'Back ID', value: u.back_id_url, type: 'image' },
    { label: 'Address', value: u.address, full: true },
    { label: 'Registered', value: u.created_at },
  ];
});
</script>

<template>
  <Head title="Dashboard" />

  <div class="flex flex-col gap-4 p-6">
    <div>
      <h1 class="text-2xl font-bold">Pending Users</h1>
      <p class="text-muted-foreground">Users waiting for approval.</p>
    </div>

    <DataTable
      :columns="columns"
      :data="pendingUsers.data"
      search-placeholder="Search name, email..."
    />
  </div>

  <Dialog v-model:open="isDetailsOpen">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>User Details</DialogTitle>
      </DialogHeader>

      <DialogDescription>
        <div
          v-if="http.processing || !selectedUser"
          class="mt-4 grid grid-cols-2 gap-4"
        >
          <div v-for="i in 10" :key="i" class="space-y-2">
            <Skeleton class="h-3 w-24" />
            <Skeleton class="h-5 w-full" />
          </div>
        </div>

        <div v-else class="mt-2">
          <!-- avatar -->
          <div class="mb-4 flex justify-center">
            <div class="relative h-20 w-20 overflow-hidden rounded-full">
              <a
                :href="selectedUser.avatar"
                target="_blank"
                rel="noopener noreferrer"
                class="block"
              >
                <img
                  :src="selectedUser.avatar"
                  class="h-full w-full cursor-pointer object-cover transition-transform hover:scale-105"
                  alt="Avatar"
                />
              </a>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div
              v-for="(item, index) in userDetails"
              :key="index"
              :class="item.full ? 'col-span-2' : ''"
              class="space-y-1"
            >
              <p class="text-xs text-muted-foreground">
                {{ item.label }}
              </p>
              <template v-if="item.type === 'image'">
                <div
                  v-if="item.value"
                  class="mt-1 overflow-hidden rounded-md border"
                >
                  <a
                    :href="item.value"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block"
                  >
                    <img
                      :src="item.value"
                      class="h-32 w-full cursor-pointer object-cover transition-transform hover:scale-105"
                      alt="ID Picture"
                    />
                  </a>
                </div>
                <p v-else class="text-sm text-muted-foreground italic">
                  No image uploaded
                </p>
              </template>
              <p v-else :class="['font-semibold wrap-break-word', item.class]">
                {{ item.value }}
              </p>
            </div>
          </div>
        </div>
      </DialogDescription>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="isConfirmOpen">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>
          {{ actionType === 'approve' ? 'Approve User' : 'Decline User' }}
        </DialogTitle>
        <DialogDescription>
          Are you sure you want to
          <span class="font-semibold">
            {{ actionType }}
          </span>
          this user?
        </DialogDescription>
      </DialogHeader>

      <div class="mt-4 flex justify-end gap-2">
        <Button variant="outline" @click="isConfirmOpen = false">
          Cancel
        </Button>

        <Button
          :variant="actionType === 'approve' ? 'default' : 'destructive'"
          @click="handleUserAction"
        >
          Confirm
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
