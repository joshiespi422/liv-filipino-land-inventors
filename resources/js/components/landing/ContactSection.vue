<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// --- INTERFACES ---
interface ContactInfo {
    phone?: string;
    email?: string;
    address?: string;
    facebook_link?: string | null;
    youtube_link?: string | null;
    instagram_link?: string | null;
    twitter_link?: string | null;
    tiktok_link?: string | null;
}

// --- PROPS ---
const props = defineProps<{
    info?: ContactInfo | null;
}>();

// --- SAMPLE DATA (Shown when backend is empty/no table exists) ---
const sampleContactInfo: ContactInfo = {
    phone: ' (02) 1234-5678.',
    email: 'info@fisinventorscoop.org',
    address: 'Unit 405, 4th Floor, 821 Cortes Building, EDSA, South Triangle, Quezon City, Philippines.',
    facebook_link: 'https://facebook.com',
    youtube_link: 'https://youtube.com',
    instagram_link: 'https://instagram.com',
    twitter_link: 'https://twitter.com',
    tiktok_link: 'https://tiktok.com'
};

// --- COMPUTED DATA ---
// If Inertia passes null (no database table yet), fallback to sampleContactInfo
const displayInfo = computed(() => {
    return props.info ? props.info : sampleContactInfo;
});

// --- INERTIA FORM SETUP ---
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    contact: '',
    subject: '',
    message: '',
});

const submitForm = () => {
    form.post('/contact/store', {
        preserveScroll: true,
        onSuccess: () => {
            // Automatically clear the form if the message sends successfully
            form.reset();
            alert('Your message has been sent successfully!');
        },
    });
};
</script>

<template>
    <div id="contact">
        <h1 class="text-3xl md:text-4xl font-bold text-center my-8 md:my-10 text-[#033E94] dark:text-white">CONTACT US</h1>
        
            <div class="max-w-6xl mx-auto my-6 md:my-10 rounded-2xl p-6 lg:p-0 
            lg:bg-linear-to-r lg:from-white-50 lg:from-60% lg:to-transparent lg:to-60% 
            dark:bg-none 
            shadow-sm border border-gray-100 lg:border-none lg:shadow-none">            <div class="flex flex-col lg:flex-row gap-8 lg:p-6">

                <div class="flex-1">
                    <form @submit.prevent="submitForm">
                        <h3 class="text-xl md:text-2xl font-bold mb-4 md:mb-6 text-[#033E94] dark:text-white">Send us a message</h3>
                        <p class="text-base md:text-lg mb-6 md:mb-8 text-[#033E94] dark:text-white">We welcome inquiries from aspiring inventors, members, and partners.</p>

                        <div class="flex flex-wrap -mx-3 mb-4 md:mb-5">
                            <div class="w-full md:w-1/2 px-3 mb-4">
                                <label for="first_name" class="block text-sm font-medium text-[#033E94] dark:text-white mb-2">First Name</label>
                                <input 
                                    v-model="form.first_name" 
                                    type="text" 
                                    id="first_name" 
                                    class="w-full rounded-3xl border border-gray-300 p-2 shadow-sm focus:ring-[#033E94] focus:border-[#033E94]" 
                                    required 
                                />
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-4">
                                <label for="last_name" class="block text-sm font-medium text-[#033E94] mb-2 dark:text-white">Last Name</label>
                                <input 
                                    v-model="form.last_name" 
                                    type="text" 
                                    id="last_name" 
                                    class="w-full rounded-3xl border border-gray-300 p-2 shadow-sm focus:ring-[#033E94] focus:border-[#033E94]" 
                                    required 
                                />
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-4">
                                <label for="email" class="block text-sm font-medium text-[#033E94] mb-2 dark:text-white">Email</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    id="email" 
                                    class="w-full rounded-3xl border border-gray-300 p-2 shadow-sm focus:ring-[#033E94] focus:border-[#033E94]" 
                                    required 
                                />
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-4">
                                <label for="contact" class="block text-sm font-medium text-[#033E94] dark:text-white mb-2">Contact</label>
                                <input 
                                    v-model="form.contact" 
                                    type="text" 
                                    id="contact" 
                                    class="w-full rounded-3xl border border-gray-300 p-2 shadow-sm placeholder-gray-400 focus:ring-[#033E94] focus:border-[#033E94]" 
                                    placeholder="Contact Details" 
                                    required 
                                />
                            </div>
                        </div>

                        <div class="mb-4 md:mb-5">
                            <label for="subject" class="block text-sm font-medium text-[#033E94] mb-2 dark:text-white">Subject</label>
                            <input 
                                v-model="form.subject" 
                                type="text" 
                                id="subject" 
                                class="w-full rounded-3xl border border-gray-300 p-2 shadow-sm focus:ring-[#033E94] focus:border-[#033E94]" 
                                required 
                            />
                        </div>

                        <div class="mb-4 md:mb-5">
                            <label for="message" class="block text-sm font-medium text-[#033E94] mb-2 dark:text-white">Message</label>
                            <textarea 
                                v-model="form.message" 
                                id="message" 
                                rows="4" 
                                class="w-full rounded-3xl border border-gray-300 p-3 shadow-sm placeholder-gray-400 focus:ring-[#033E94] focus:border-[#033E94]" 
                                placeholder="Message..." 
                                required
                            ></textarea>
                        </div>
                        
                        <div class="flex justify-end mt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="text-white bg-[#033E94] hover:bg-blue-800 shadow-md font-bold rounded-3xl text-sm px-8 py-3 md:px-14 md:text-lg transition-colors active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Sending...' : 'Send' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex-1 mt-8 lg:mt-0">
                    <div class="bg-[#033E94] rounded-2xl shadow-lg p-6 lg:p-8 w-full h-full">
                        <p class="text-lg md:text-xl font-bold mb-6 md:mb-8 text-white">Visit our office or drop us a line:</p>

                        <div class="flex items-start gap-4 bg-[#829FCA] text-[#F3F6FA] rounded-xl shadow-md p-4 mb-4">
                            <img src="/assets/mobile.png" alt="Phone Icon" class="w-8 h-8 md:w-10 md:h-10 object-contain shrink-0 mt-1" />
                            <div class="flex flex-col overflow-hidden">
                                <h3 class="text-base md:text-lg font-semibold">Phone Number:</h3>
                                <p class="text-sm md:text-base wrap-break-words">{{ displayInfo.phone }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 bg-[#829FCA] text-[#F3F6FA] rounded-xl shadow-md p-4 mb-4">
                            <img src="/assets/email.png" alt="Email Icon" class="w-8 h-6 md:w-10 md:h-6 object-contain shrink-0 mt-1" />
                            <div class="flex flex-col overflow-hidden w-full">
                                <h3 class="text-base md:text-lg font-semibold">Email Address:</h3>
                                <p class="text-sm md:text-base break-all md:wrap-break-words">{{ displayInfo.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 bg-[#829FCA] text-[#F3F6FA] rounded-xl shadow-md p-4 mb-5">
                            <img src="/assets/address.png" alt="Address Icon" class="w-6 h-8 md:w-7 md:h-9 object-contain shrink-0 mt-1" />
                            <div class="flex flex-col overflow-hidden">
                                <h3 class="text-base md:text-lg font-semibold">Address:</h3>
                                <p class="text-sm md:text-base wrap-break-words">{{ displayInfo.address }}</p>
                            </div>
                        </div>

                        <p class="text-base md:text-lg font-bold mt-8 mb-4 text-white">Follow us on social media!</p>
                        
                        <div class="flex flex-row gap-4 md:gap-5 flex-wrap">
                            <a v-if="displayInfo.facebook_link" :href="displayInfo.facebook_link" target="_blank" rel="noopener noreferrer">
                                <img src="/assets/fb.png" class="w-10 h-10 md:w-14 md:h-14 hover:scale-110 transition-transform" alt="Facebook"/>
                            </a>
                            
                            <a v-if="displayInfo.youtube_link" :href="displayInfo.youtube_link" target="_blank" rel="noopener noreferrer">
                                <img src="/assets/yt.png" class="w-10 h-10 md:w-14 md:h-14 hover:scale-110 transition-transform" alt="YouTube"/>
                            </a>
                            
                            <a v-if="displayInfo.instagram_link" :href="displayInfo.instagram_link" target="_blank" rel="noopener noreferrer">
                                <img src="/assets/ig.png" class="w-10 h-10 md:w-14 md:h-14 hover:scale-110 transition-transform" alt="Instagram"/>
                            </a>
                            
                            <a v-if="displayInfo.twitter_link" :href="displayInfo.twitter_link" target="_blank" rel="noopener noreferrer">
                                <img src="/assets/x.png" class="w-10 h-10 md:w-14 md:h-14 hover:scale-110 transition-transform" alt="Twitter/X"/>
                            </a>
                            
                            <a v-if="displayInfo.tiktok_link" :href="displayInfo.tiktok_link" target="_blank" rel="noopener noreferrer">
                                <img src="/assets/tiktok.png" class="w-10 h-10 md:w-14 md:h-14 hover:scale-110 transition-transform" alt="TikTok"/>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="bg-[#fdfdfd] flex items-center justify-center text-center py-8 px-4 md:px-8">
            <p class="text-base md:text-lg lg:text-xl text-gray-800 max-w-5xl leading-relaxed">
                Feel free to visit our office hours during business days, or reach out by phone or email anytime for more information about membership, programs, or how to present your invention to the cooperative.
            </p>
        </div>
    </div>
</template>