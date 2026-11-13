<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import type { TutorialItem } from '@/types/landing/index';

const props = defineProps<{
  items?: TutorialItem[];
}>();

const searchQuery = ref('');
const isVideoModalOpen = ref(false);
const activeVideoUrl = ref<string | null>(null);
const videoPlayerRef = ref<HTMLVideoElement | null>(null);

const sampleItems: any[] = [
  {
    id: 'v1',
    type: 'video',
    title: 'How to Download the App',
    subtitle: 'Step 1',
    description:
      'Learn where and how to securely download the official mobile app.',
    media_path: '/assets/placeholder.jpg',
    video_url: '/assets/Mobile-Tutorial/1 How to Download the App.mp4',
  },
  {
    id: 'v2',
    type: 'video',
    title: 'How to Create your FISMPC Account',
    subtitle: 'Step 2',
    description:
      'A step-by-step guide to registering and setting up your new account.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/2 How to create your fismpc account.mp4',
  },
  {
    id: 'v3',
    type: 'video',
    title: 'How to Login your Account',
    subtitle: 'Step 3',
    description: 'Securely access your account using your credentials.',
    media_path: '/assets/placeholder.jpg',
    video_url: '/assets/Mobile-Tutorial/3 How to Log In to Your Account.mp4',
  },
  {
    id: 'v4',
    type: 'video',
    title: 'How to Recover Your Account If You Forget Your Password',
    subtitle: 'Step 4',
    description:
      'Forgot your password? Learn how to safely recover your access.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/4 How to Recover Your Account If You Forget Your Password.mp4',
  },
  {
    id: 'v5',
    type: 'video',
    title: 'How to Complete Your Profile',
    subtitle: 'Step 5',
    description:
      'Fill in your necessary details to complete your membership profile.',
    media_path: '/assets/placeholder.jpg',
    video_url: '/assets/Mobile-Tutorial/5 How to Complete Your Profile.mp4',
  },
  {
    id: 'v6',
    type: 'video',
    title: 'How to Change Your Profile Picture',
    subtitle: 'Step 6',
    description: 'Personalize your account by updating your profile picture.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/6 How to Change Your Profile Picture.mp4',
  },
  {
    id: 'v7',
    type: 'video',
    title: 'How to Activate Biometric Authentication',
    subtitle: 'Step 7',
    description:
      'Enable Face ID or Fingerprint login for faster, secure access.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/7 How to Activate Biometric Authentication.mp4',
  },
  {
    id: 'v8',
    type: 'video',
    title: 'How to Become a Cooperative Member',
    subtitle: 'Step 8',
    description:
      'Follow the process to officially become a registered cooperative member.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/8 How to Become a Cooperative Member.mp4',
  },
  {
    id: 'v9',
    type: 'video',
    title: 'How to Access the Cooperative Membership Module',
    subtitle: 'Step 9',
    description:
      'Navigate the membership module to view your cooperative status.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/9 How to access the Cooperative Membership Module.mp4',
  },
  {
    id: 'v10',
    type: 'video',
    title: 'How to Access Business Training and What You Can Gain from It',
    subtitle: 'Step 10',
    description: 'Find and enroll in available business training courses.',
    media_path: '/assets/placeholder.jpg',
    video_url:
      '/assets/Mobile-Tutorial/10 How to access the Business training.mp4',
  },
  {
    id: 'v11',
    type: 'video',
    title: 'How to Access News and Events',
    subtitle: 'Step 11',
    description:
      'Stay updated with the latest cooperative news and upcoming events.',
    media_path: '/assets/placeholder.jpg',
    video_url: '/assets/Mobile-Tutorial/11 How to Access News and Events.mp4',
  },
];

const processedItems = computed(() => {
  let items = props.items && props.items.length > 0 ? props.items : sampleItems;

  if (searchQuery.value.trim() !== '') {
    const query = searchQuery.value.toLowerCase();
    items = items.filter((item) => {
      const titleMatch = item.title?.toLowerCase().includes(query) ?? false;
      const descMatch =
        item.description?.toLowerCase().includes(query) ?? false;

      return titleMatch || descMatch;
    });
  }

  return items;
});

const videos = computed(() =>
  processedItems.value.filter((item) => item.type === 'video'),
);

const openVideoModal = (url?: string) => {
  if (!url) {
    return;
  }

  activeVideoUrl.value = url;
  isVideoModalOpen.value = true;
  document.body.style.overflow = 'hidden';
};

const closeVideoModal = () => {
  if (videoPlayerRef.value) {
    videoPlayerRef.value.pause();
    videoPlayerRef.value.removeAttribute('src');
    videoPlayerRef.value.load();
  }

  isVideoModalOpen.value = false;
  document.body.style.overflow = '';

  setTimeout(() => {
    activeVideoUrl.value = null;
  }, 300);
};

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<template>
  <div
    id="mobile-tutorial"
    class="overflow-x-hidden bg-white pb-16 dark:bg-[#0a192f]"
  >
    <div
      class="relative flex h-64 w-full flex-col items-center justify-center border-b border-gray-100 bg-gray-50 px-4 text-center sm:px-6 md:h-80 lg:h-96 dark:border-gray-800 dark:bg-gray-900"
    >
      <span
        class="mb-3 text-xs font-bold tracking-widest text-[#033E94] uppercase sm:mb-4 sm:text-sm dark:text-blue-400"
      >
        Support & Guides
      </span>
      <h1
        class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl md:text-5xl dark:text-white"
      >
        Mobile App Tutorials
      </h1>
      <p
        class="max-w-2xl text-sm leading-relaxed text-gray-500 sm:text-base md:text-lg dark:text-gray-400"
      >
        Follow our step-by-step tutorials to learn how to use the mobile app,
        manage your account, access your membership, and get the most out of
        your experience.
      </p>
    </div>

    <div class="mx-auto mt-8 max-w-3xl px-4 sm:px-6 md:mt-12">
      <div
        class="relative flex items-center rounded-2xl border border-gray-200 bg-gray-50 shadow-sm transition-all focus-within:ring-2 focus-within:ring-[#033E94]/20 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="pl-5 text-gray-400">
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
            />
          </svg>
        </div>
        <input
          type="search"
          v-model="searchQuery"
          class="w-full border-none bg-transparent px-4 py-3.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none sm:text-base dark:text-white"
          placeholder="Search tutorials (e.g., 'Biometric' or 'Password')..."
        />
      </div>
    </div>

    <div class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 md:mt-14 md:px-8 lg:px-12">
      <div class="relative w-full">
        <div v-if="videos.length > 0">
          <BaseCarousel
            :items="videos"
            :autoplayDelay="3500"
            slide-class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1.33rem)]"
          >
            <template #slide="{ slide: video }">
              <div class="h-full w-full p-2 sm:p-3">
                <div
                  class="group flex h-full cursor-pointer flex-col rounded-2xl border border-gray-100 bg-white p-3 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-4 dark:border-gray-700 dark:bg-gray-800"
                  @click="openVideoModal(video.video_url)"
                >
                  <div
                    class="relative mb-4 block aspect-video w-full overflow-hidden rounded-xl bg-gray-200 sm:mb-5 dark:bg-gray-900"
                  >
                    <video
                      class="pointer-events-none absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                      :src="encodeURI(video.video_url)"
                      :poster="video.media_path"
                      preload="none"
                      muted
                      playsinline
                    ></video>

                    <div
                      class="absolute inset-0 flex items-center justify-center bg-black/10 transition-colors duration-300 group-hover:bg-black/20"
                    >
                      <div
                        class="flex h-10 w-10 transform items-center justify-center rounded-full bg-white/95 shadow-lg backdrop-blur-md transition-transform duration-300 group-hover:scale-110 sm:h-12 sm:w-12"
                      >
                        <svg
                          class="ml-1 h-4 w-4 text-[#033E94] sm:h-5 sm:w-5"
                          fill="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path d="M8 5v14l11-7z" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <div class="flex flex-1 flex-col px-1 sm:px-2">
                    <span
                      class="mb-1 text-[10px] font-bold tracking-wider text-[#033E94] uppercase sm:mb-1.5 sm:text-[11px] dark:text-blue-400"
                    >
                      {{ video.subtitle }}
                    </span>
                    <h5
                      class="mb-1.5 line-clamp-2 text-sm leading-snug font-bold text-gray-900 transition-colors group-hover:text-[#033E94] sm:mb-2 sm:text-base dark:text-white dark:group-hover:text-blue-400"
                    >
                      {{ video.title }}
                    </h5>
                    <p
                      class="line-clamp-2 text-xs leading-relaxed text-gray-500 sm:text-sm dark:text-gray-400"
                    >
                      {{ video.description }}
                    </p>
                  </div>
                </div>
              </div>
            </template>
          </BaseCarousel>
        </div>

        <div
          v-else
          class="mx-2 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-16 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800"
        >
          <p class="text-base font-medium sm:text-lg">No tutorials found.</p>
          <p class="mt-1 text-sm">Try adjusting your search terms.</p>
        </div>
      </div>
    </div>

    <teleport to="body">
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="isVideoModalOpen"
          class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 md:p-12"
        >
          <div
            class="absolute inset-0 cursor-pointer bg-black/95 backdrop-blur-sm transition-opacity"
            @click="closeVideoModal"
          ></div>

          <div
            class="relative z-10 w-full max-w-5xl overflow-hidden rounded-xl bg-black shadow-2xl ring-1 ring-white/20 sm:rounded-2xl"
          >
            <button
              @click="closeVideoModal"
              class="absolute -top-10 right-0 z-20 flex items-center gap-1.5 text-sm font-medium text-gray-300 transition-colors hover:text-white sm:-top-12 sm:gap-2 sm:text-base"
            >
              Close
              <svg
                class="h-5 w-5 sm:h-6 sm:w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>

            <div
              class="flex aspect-video w-full items-center justify-center bg-black"
            >
              <!-- Fixed Modal video tag with the videoPlayerRef attached -->
              <video
                ref="videoPlayerRef"
                v-if="activeVideoUrl"
                :key="activeVideoUrl"
                :src="encodeURI(activeVideoUrl)"
                class="h-full w-full object-contain"
                controls
                autoplay
                playsinline
                controlsList="nodownload"
              >
                Your browser does not support the video tag.
              </video>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>
