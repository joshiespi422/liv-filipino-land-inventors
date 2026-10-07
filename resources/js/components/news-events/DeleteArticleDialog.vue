<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Loader2Icon, Trash2Icon } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import { Button, buttonVariants } from '@/components/ui/button';
import newsEvents from '@/routes/news-events';

const props = defineProps<{
  article: { slug: string; title: string };
  canDelete: boolean;
}>();

const processing = ref(false);

function confirmDelete() {
  router.delete(newsEvents.destroy(props.article.slug), {
    onStart: () => (processing.value = true),
    onFinish: () => (processing.value = false),
    onSuccess: () => {
      toast.success('Article successfully deleted.');
    },
  });
}
</script>

<template>
  <!-- Published articles: show why it's unavailable instead of hiding the button -->
  <Button
    v-if="!canDelete"
    variant="destructive"
    size="sm"
    disabled
    title="Archive this article before deleting it"
  >
    <Trash2Icon class="size-4" /> Delete
  </Button>

  <AlertDialog v-else>
    <AlertDialogTrigger as-child>
      <Button variant="destructive" size="sm" class="cursor-pointer">
        <Trash2Icon class="size-4" /> Delete
      </Button>
    </AlertDialogTrigger>

    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Delete this article permanently?</AlertDialogTitle>
        <AlertDialogDescription>
          “{{ article.title }}” and its cover image will be removed for good.
          This can't be undone.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction
          :class="buttonVariants({ variant: 'destructive' })"
          :disabled="processing"
          @click="confirmDelete"
        >
          <Loader2Icon v-if="processing" class="size-4 animate-spin" />
          Delete permanently
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
