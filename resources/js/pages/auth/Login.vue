<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Log In" />

        
        <img 
            src="/assets/bg1.jpg" 
            alt="Background"
            class="absolute top-0 left-0 w-full h-[40vh] md:h-[50vh] object-cover rounded-b-[15%] sm:rounded-b-[20%] md:rounded-b-[25%] lg:rounded-b-[20%] z-0 shadow-sm"
        >

        <div class="w-full max-w-md relative z-10 mt-16 sm:mt-12">
            <div class="bg-white shadow-2xl rounded-2xl border border-slate-200 relative pt-12 pb-8 px-6 sm:px-8">
                
                <img 
                    src="/assets/fismpc_logo.png"
                    alt="FIS Multi-Purpose Cooperative Logo"
                    class="absolute p-1.5 -top-12 left-1/2 transform -translate-x-1/2 h-24 w-24 rounded-full shadow-md bg-white z-20 object-contain"
                >

                <div v-if="status" class="mb-6 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg text-center">
                    {{ status }}
                </div>

                <div class="text-center mb-8 mt-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#033e94]">Log In</h1>
                    <div class="w-16 h-1 bg-[#033e94] rounded-full mx-auto mt-3 opacity-80"></div>
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-5">
                        <div>
                            <Label for="email" class="block mb-1.5 text-sm font-semibold text-[#033e94]">
                                Email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="Enter email or mobile number"
                                class="w-full px-4 py-3 text-sm sm:text-base rounded-xl border border-slate-200 focus:ring-1 focus:ring-blue-100 focus:border-[#033e94] transition-all outline-none bg-white text-gray-800 
                                    dark:bg-gray-200 dark:text-gray-800 dark:placeholder-gray-400 dark:focus:ring-[#033e94]"
                            />
                            <InputError :message="errors.email" class="mt-1" />
                        </div>

                        <div>
                            <Label for="password" class="block mb-1.5 text-sm font-semibold text-[#033e94]">
                                Password
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 text-sm sm:text-base rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-100 focus:border-[#033e94] transition-all outline-none bg-white text-gray-800
                                dark:bg-gray-200 dark:text-gray-800 dark:placeholder-gray-400 dark:focus:ring-[#033e94]"
                            />
                            <InputError :message="errors.password" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 mt-2 mb-4">
                        <Label for="remember" class="flex items-center text-sm text-gray-700 font-medium cursor-pointer space-x-2 select-none">
                            <Checkbox 
                                id="remember" 
                                name="remember" 
                                :tabindex="3"
                                class="w-4 h-4 text-[#033e94] border-gray-300 rounded focus:ring-[#033e94]" 
                            />
                            <span>Remember me</span>
                        </Label>
                        
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm font-bold text-[#033e94] dark:text-gray-700 hover:text-blue-700 transition-colors"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>

                    <Button
                        type="submit"
                        class="w-full bg-[#033e94] hover:bg-blue-800 text-white font-bold py-6 px-4 rounded-xl transition-all duration-200 shadow-md flex items-center justify-center space-x-2"
                        :class="{ 'opacity-70 cursor-not-allowed': processing }" 
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="text-white h-5 w-5 mr-2" />
                        <span class="font-medium text-base">{{ processing ? 'Authenticating...' : 'Log in' }}</span>
                    </Button>
                </Form> 
                
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="/" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center text-sm sm:text-base">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
</template>