<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';
import conversations from '@/routes/conversations';
import supportChat from '@/routes/support-chat';
import type { Auth } from '@/types';

interface TicketUser {
  id: number;
  name: string;
  email: string;
}
interface Ticket {
  id: number;
  conversation_id: number;
  status: string;
  subject: string | null;
  user: TicketUser | null;
  last_message: string | null;
  last_message_at: string | null;
  unread_count: number;
}
interface Attachment {
  id: number;
  path: string;
  original_name: string;
  mime_type: string | null;
  size: number;
}
interface Sender {
  id: number;
  name: string;
}
interface Message {
  id: number;
  body: string | null;
  created_at: string;
  sender: Sender;
  attachments: Attachment[];
}
interface ConversationData {
  id: number;
  status: string;
  messages: Message[];
}
type StatusFilter = 'open' | 'closed';

const props = defineProps<{
  tickets: Ticket[];
  closedTickets?: Ticket[];
  selectedConversation: ConversationData | null;
}>();

// Open when the page loads; switches to Closed only if the linked ticket is closed
const statusFilter = ref<StatusFilter>(
  props.selectedConversation?.status === 'closed' ? 'closed' : 'open',
);

const partialProps = () => [
  'selectedConversation',
  'tickets',
  'auth',
  ...(statusFilter.value === 'closed' ? ['closedTickets'] : []),
];

const visibleTickets = computed(() =>
  statusFilter.value === 'open' ? props.tickets : props.closedTickets,
);

defineOptions({ layout: { breadcrumbs: [{ title: 'Support Chat' }] } });

const page = usePage<{ auth: Auth }>();
const currentUserId = computed(() => page.props.auth.user?.id);

const messages = ref<Message[]>([]);
const body = ref('');
const files = ref<File[]>([]);
const scrollContainer = ref<HTMLElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Fetch closed tickets every time the filter switches to Closed
watch(statusFilter, (filter) => {
  if (
    filter === 'closed' &&
    props.closedTickets === undefined &&
    !props.selectedConversation
  ) {
    router.reload({ only: ['closedTickets'] });
  }
});
onMounted(() => {
  if (statusFilter.value === 'closed' && props.closedTickets === undefined) {
    router.reload({ only: ['closedTickets'] });
  }
});

function setStatus(status: StatusFilter) {
  if (!props.selectedConversation) {
    return;
  }

  router.patch(
    supportChat.status(props.selectedConversation.id).url,
    { status },
    {
      only: partialProps(),
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        toast.success('Conversation status successfully updated.');
      },
    },
  );
}
function changeFilter(filter: StatusFilter) {
  if (filter === statusFilter.value) {
    return;
  }

  statusFilter.value = filter;

  if (props.selectedConversation) {
    router.visit(supportChat.index(), {
      only: partialProps(),
      preserveState: true,
      preserveScroll: true,
    });
  }
}

// Status badge colors: add one entry per future status
const STATUS_STYLES: Record<string, string> = {
  open: 'border-transparent bg-emerald-500/15 text-emerald-700 dark:text-emerald-400',
  closed: 'border-transparent bg-rose-500/15 text-rose-600 dark:text-rose-400',
};
const statusClass = (status: string) =>
  STATUS_STYLES[status] ?? STATUS_STYLES.closed;

function scrollToBottom() {
  nextTick(() => {
    if (scrollContainer.value) {
      scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
  });
}

function selectTicket(conversationId: number | null) {
  if (!conversationId || conversationId === props.selectedConversation?.id) {
    return;
  }

  router.visit(supportChat.index({ query: { c: conversationId } }), {
    only: partialProps(),
    preserveState: true,
    preserveScroll: true,
  });
}

// Server messages -> local list (the server already marked the chat as read)
watch(
  () => props.selectedConversation,
  (conversation) => {
    messages.value = conversation ? [...conversation.messages] : [];
    scrollToBottom();
  },
  { immediate: true },
);

function markAsRead(conversationId: number) {
  router.post(
    supportChat.read(conversationId).url,
    {},
    {
      only: ['tickets', 'auth'],
      preserveScroll: true,
      preserveState: true,
    },
  );
}

// Real-time: one channel per open conversation, cleaned up on switch/unmount
watch(
  () => props.selectedConversation?.id,
  (id, _prev, onCleanup) => {
    body.value = ''; // don't carry a draft into another ticket
    files.value = [];

    if (fileInput.value) {
      fileInput.value.value = '';
    }

    if (!id) {
      return;
    }

    const channelName = `conversation.${id}`;

    echo()
      .private(channelName)
      .listen('.message.sent', (e: any) => {
        if (
          e.sender_id === currentUserId.value ||
          messages.value.some((m) => m.id === e.id)
        ) {
          return;
        }

        messages.value.push({
          id: e.id,
          body: e.body,
          created_at: e.created_at,
          sender: e.sender,
          attachments: e.attachments || [],
        });
        scrollToBottom();
        markAsRead(id); // viewing it live = read; also clears the notification
      });

    onCleanup(() => echo().leave(channelName));
  },
  { immediate: true },
);

onMounted(scrollToBottom);

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  files.value = input.files ? Array.from(input.files) : [];
}

function sendMessage() {
  if (!props.selectedConversation) {
    return;
  }

  if (!body.value.trim() && files.value.length === 0) {
    return;
  }

  if (props.selectedConversation.status !== 'open') return;

  const formData = new FormData();
  formData.append('body', body.value);
  files.value.forEach((file) => formData.append('attachments[]', file));

  router.post(
    conversations.messages.store(props.selectedConversation.id).url,
    formData,
    {
      only: ['selectedConversation', 'tickets'],
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        body.value = '';
        files.value = [];

        if (fileInput.value) {
          fileInput.value.value = '';
        }

        scrollToBottom();
      },
    },
  );
}

function isOwnMessage(message: Message): boolean {
  return message.sender.id === currentUserId.value;
}

function formatTime(isoString: string | null) {
  return isoString
    ? new Date(isoString).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
      })
    : '';
}

// Attachments
const attachmentUrl = (path: string) =>
  /^(https?:)?\//.test(path) ? path : `/storage/${path}`;
const isImage = (a: Attachment) => !!a.mime_type?.startsWith('image/');
const isVideo = (a: Attachment) => !!a.mime_type?.startsWith('video/');
</script>

<template>
  <Head title="Support Chat" />

  <div class="flex h-[calc(100vh-8rem)] gap-4 p-4">
    <!-- Left Column: Tickets List -->
    <div class="flex w-1/3 flex-col overflow-hidden rounded-lg border bg-card">
      <div class="flex items-center justify-between gap-2 border-b p-3">
        <span class="font-semibold text-card-foreground">Support Tickets</span>

        <Select
          :model-value="statusFilter"
          @update:model-value="changeFilter($event as StatusFilter)"
        >
          <SelectTrigger class="h-8 w-28 text-xs">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="open">Open</SelectItem>
            <SelectItem value="closed">Closed</SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="flex-1 divide-y overflow-y-auto">
        <div
          v-for="ticket in visibleTickets"
          :key="ticket.id"
          class="flex cursor-pointer flex-col gap-1 p-3 transition-colors hover:bg-muted/50"
          :class="{
            'bg-muted': selectedConversation?.id === ticket.conversation_id,
          }"
          @click="selectTicket(ticket.conversation_id)"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2">
              <span class="truncate text-sm font-medium">{{
                ticket.user?.name ?? 'Unknown'
              }}</span>
              <Badge
                variant="outline"
                class="px-1.5 py-0 text-[10px] uppercase"
                :class="statusClass(ticket.status)"
              >
                {{ ticket.status }}
              </Badge>
            </div>

            <span
              v-if="ticket.unread_count > 0"
              class="inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-semibold text-primary-foreground"
            >
              {{ ticket.unread_count > 9 ? '9+' : ticket.unread_count }}
            </span>
          </div>

          <div class="truncate text-xs text-muted-foreground">
            {{ ticket.subject ?? ticket.last_message ?? 'No messages yet' }}
          </div>

          <div
            class="mt-1 flex justify-between text-[10px] text-muted-foreground"
          >
            <span>{{ ticket.user?.email }}</span>
            <span>{{ formatTime(ticket.last_message_at) }}</span>
          </div>
        </div>

        <div
          v-if="visibleTickets === undefined"
          class="p-4 text-center text-sm text-muted-foreground"
        >
          Loading…
        </div>
        <div
          v-else-if="visibleTickets.length === 0"
          class="p-4 text-center text-sm text-muted-foreground"
        >
          No {{ statusFilter }} tickets.
        </div>
      </div>
    </div>

    <!-- Right Column: Selected Chat -->
    <div class="flex flex-1 flex-col overflow-hidden rounded-lg border bg-card">
      <template v-if="selectedConversation">
        <!-- Chat Header -->
        <div
          class="flex items-center justify-between gap-3 border-b bg-muted/30 p-3"
        >
          <div class="flex items-center gap-2 font-semibold">
            <span>Conversation #{{ selectedConversation.id }}</span>
            <Badge
              variant="outline"
              class="px-1.5 py-0 text-[10px] uppercase"
              :class="statusClass(selectedConversation.status)"
            >
              {{ selectedConversation.status }}
            </Badge>
          </div>

          <Button
            v-if="selectedConversation.status === 'open'"
            variant="outline"
            size="sm"
            @click="setStatus('closed')"
          >
            Close ticket
          </Button>
          <Button v-else variant="outline" size="sm" @click="setStatus('open')">
            Reopen
          </Button>
        </div>

        <!-- Chat Messages Window -->
        <div
          ref="scrollContainer"
          class="flex flex-1 flex-col gap-3 overflow-y-auto p-4"
        >
          <div
            v-for="message in messages"
            :key="message.id"
            class="flex flex-col gap-1"
            :class="isOwnMessage(message) ? 'items-end' : 'items-start'"
          >
            <span class="text-xs text-muted-foreground">{{
              message.sender.name
            }}</span>

            <div
              class="max-w-md rounded-lg px-3 py-2 text-sm"
              :class="
                isOwnMessage(message)
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted'
              "
            >
              <p v-if="message.body" class="whitespace-pre-wrap">
                {{ message.body }}
              </p>

              <div
                v-if="message.attachments?.length"
                class="mt-1 flex flex-col gap-2"
              >
                <template
                  v-for="attachment in message.attachments"
                  :key="attachment.id"
                >
                  <a
                    v-if="isImage(attachment)"
                    :href="attachmentUrl(attachment.path)"
                    target="_blank"
                    rel="noopener"
                  >
                    <img
                      :src="attachmentUrl(attachment.path)"
                      :alt="attachment.original_name"
                      loading="lazy"
                      class="max-h-64 max-w-full rounded-md object-cover"
                      @load="scrollToBottom"
                    />
                  </a>
                  <video
                    v-else-if="isVideo(attachment)"
                    :src="attachmentUrl(attachment.path)"
                    controls
                    preload="metadata"
                    class="max-h-64 max-w-full rounded-md"
                  />
                  <a
                    v-else
                    :href="attachmentUrl(attachment.path)"
                    target="_blank"
                    rel="noopener"
                    class="text-xs underline"
                  >
                    {{ attachment.original_name }}
                  </a>
                </template>
              </div>
            </div>

            <span class="text-xs text-muted-foreground">
              {{ formatTime(message.created_at) }}
            </span>
          </div>

          <p
            v-if="messages.length === 0"
            class="text-center text-sm text-muted-foreground"
          >
            No messages in this chat yet.
          </p>
        </div>

        <!-- Input Area -->
        <form
          v-if="selectedConversation.status === 'open'"
          class="flex flex-col gap-2 border-t p-3"
          @submit.prevent="sendMessage"
        >
          <Textarea
            v-model="body"
            placeholder="Type a reply..."
            rows="2"
            @keydown.enter.exact.prevent="sendMessage"
          />

          <div class="flex items-center justify-between gap-2">
            <input
              type="file"
              multiple
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              class="text-xs"
              @change="handleFileChange"
            />
            <Button type="submit" size="sm">Send Reply</Button>
          </div>

          <p v-if="files.length > 0" class="text-xs text-muted-foreground">
            {{ files.length }} file(s) selected
          </p>
        </form>
        <div
          v-else
          class="border-t p-3 text-center text-xs text-muted-foreground"
        >
          This ticket is closed. Reopen it to reply.
        </div>
      </template>

      <!-- Empty State -->
      <div
        v-else
        class="flex flex-1 items-center justify-center text-muted-foreground"
      >
        Select a conversation to start chatting.
      </div>
    </div>
  </div>
</template>
