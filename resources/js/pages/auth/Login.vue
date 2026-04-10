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
            class="absolute top-0 left-0 w-full h-[40vh] md:h-[50vh] object-cover rounded-b-[15%] sm:rounded-b-[20%] md:rounded-b-[25%] lg:rounded-b-[20%] z-0"
        >

        <div class="w-full max-w-md sm:max-w-lg relative z-10 mt-12 sm:mt-8">
            <div class="bg-white shadow-2xl rounded-2xl border border-slate-200 relative pt-8 sm:pt-10">
                
                <img 
                    src="/assets/fismpc_logo.png"
                    alt="FIS Multi-Purpose Cooperative Logo"
                    class="absolute p-1 -top-10 sm:-top-12 left-1/2 transform -translate-x-1/2 h-20 w-20 sm:h-24 sm:w-24 rounded-full shadow-lg bg-white z-20"
                >

                <div class="p-5 sm:p-6 pt-8 sm:pt-8">
                    <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center px-4">
                        {{ status }}
                    </div>

                    <div class="text-center px-2 sm:px-4 mb-6 sm:mb-8">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#033e94]">Log In</h1>
                        <div class="w-full h-0.5 bg-blue-100 mt-2"></div>
                    </div>

                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="px-2 sm:px-4"
                    >
                        <div class="mb-4">
                            <Label for="email" class="block mb-1.5 sm:mb-2 text-sm font-semibold text-[#033e94]">
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
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-100 focus:border-[#033e94] transition-all outline-none bg-white dark:bg-gray-50 text-gray-800"
                            />
                            <InputError :message="errors.email" class="mt-1" />
                        </div>

                        <div class="mb-4">
                            <Label for="password" class="block mb-1.5 sm:mb-2 text-sm font-semibold text-[#033e94]">
                                Password
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-100 focus:border-[#033e94] transition-all outline-none bg-white dark:bg-gray-50 text-gray-800"
                            />
                            <InputError :message="errors.password" class="mt-1" />
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0 mb-6 mt-3">
                            <Label for="remember" class="flex items-center text-sm text-gray-800 font-medium cursor-pointer space-x-2">
                                <Checkbox 
                                    id="remember" 
                                    name="remember" 
                                    :tabindex="3"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                />
                                <span>Remember me</span>
                            </Label>
                            
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm font-bold text-[#033e94] hover:text-blue-600 transition-colors dark:text-gray-700"
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
                    
                    <div class="mt-6 pt-4 border-t border-gray-100 px-2 sm:px-4 flex flex-col gap-3">
                        <div v-if="canRegister" class="text-center text-sm text-gray-600 mb-2">
                            Don't have an account?
                            <TextLink :href="register()" :tabindex="6" class="font-bold text-[#033e94] dark:text-gray-700 hover:text-blue-600 ml-1">
                                Sign up
                            </TextLink>
                        </div>

                        <a href="/" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center">
                            Cancel
                        </a>
                    </div>

                </div>
            </div>
        </div>
</template>