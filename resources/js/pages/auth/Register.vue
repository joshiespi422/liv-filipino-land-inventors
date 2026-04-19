<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
import InputError from '@/components/InputError.vue';

// 1. Form State
const form = useForm({
    full_name: '',
    mobile_number: '',
    otp: '',
    password: '',
    password_confirmation: '',
    verification_token: '', 
    agreed_terms: false,
});

// 2. Navigation & UI State
const currentStep = ref(1);
const isLoading = ref(false); 
const globalError = ref(''); 

// Password Visibility States
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const currentTitle = computed(() => {
    switch (currentStep.value) {
        case 1: return "Register Account";
        case 2: return "OTP Verification";
        case 3: return "Create Password";
        case 4: return "Congratulations!";
        default: return "";
    }
});

const currentSubtitle = computed(() => {
    switch (currentStep.value) {
        case 1: return "Create an Account to get started and access our services";
        case 2: return `Enter the OTP sent to ${form.mobile_number || 'your number'}`;
        case 3: return "Create a security password to protect your account";
        case 4: return "Your account has been successfully created.";
        default: return "";
    }
});

// 3. OTP Timer Logic
const countdown = ref(300); // 5 minutes
let timerId: ReturnType<typeof setInterval>;

const startTimer = () => {
    countdown.value = 300;
    clearInterval(timerId);
    timerId = setInterval(() => {

        if (countdown.value > 0) {
            countdown.value--;
        }else {
            clearInterval(timerId);
        }

    }, 1000);
};

const formattedTime = computed(() => {
    const m = String(Math.floor(countdown.value / 60)).padStart(2, '0');
    const s = String(countdown.value % 60).padStart(2, '0');
    {
        return `${m}:${s}`;}
});

// 4. 6-Box OTP Logic
const otpDigits = ref(['', '', '', '', '', '']);
const otpRefs = ref<HTMLInputElement[]>([]);

const handleOtpInput = (event: Event, index: number) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/[^0-9]/g, '');
    otpDigits.value[index] = value;
    
    if (value && index < 5) {
        nextTick(() => {
            otpRefs.value[index + 1]?.focus();
        });
    }
    
    form.otp = otpDigits.value.join('');
    form.clearErrors('otp'); 
};

const handleOtpDelete = (event: KeyboardEvent, index: number) => {
    if (!otpDigits.value[index] && index > 0) {
        nextTick(() => {
            otpRefs.value[index - 1]?.focus();
        });
    }
};

// ==========================================
// 5. NATIVE FETCH API ACTIONS
// ==========================================

// Helper to get Laravel's CSRF token from cookies
const getHeaders = () => {
    const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
    const token = match ? decodeURIComponent(match[2]) : '';
    
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', // Tells Laravel this is an AJAX request
        'X-XSRF-TOKEN': token
    };
};

// STEP 1: Initiate Registration
const registerNow = async () => {
    form.clearErrors();
    globalError.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/register/initiate', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                full_name: form.full_name,
                mobile_number: form.mobile_number
            })
        });
        
        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {

                if (data.errors.full_name) {
                    form.setError('full_name', data.errors.full_name[0]);
                }

                if (data.errors.mobile_number) {
                    form.setError('mobile_number', data.errors.mobile_number[0]);
                }
            } else {
                form.setError('mobile_number', data.message || 'Failed to send OTP.');
            }

            return;
        }
        
        currentStep.value = 2;
        startTimer();
        nextTick(() => otpRefs.value[0]?.focus());
    } catch {
        form.setError('mobile_number', 'An unexpected error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

// STEP 2: Verify OTP
const verifyOtp = async () => {
    form.clearErrors();
    globalError.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/register/verify', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                mobile_number: form.mobile_number,
                otp: form.otp
            })
        });
        
        const data = await response.json();

        if (!response.ok) {
            form.setError('otp', data.message || 'Invalid or expired OTP.');

            return;
        }
        
        form.verification_token = data.verification_token; 
        currentStep.value = 3;
    } catch {
        form.setError('otp', 'An unexpected error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

// STEP 2B: Resend OTP
const resendOtp = async () => {
    globalError.value = '';
    isLoading.value = true;
    
    try {
        const response = await fetch('/register/resend', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                mobile_number: form.mobile_number
            })
        });
        
        const data = await response.json();

        if (!response.ok) {
            globalError.value = data.message || 'Failed to resend OTP.';
            
            return;
        }

        startTimer(); 
        otpDigits.value = ['', '', '', '', '', ''];
        form.otp = '';
        nextTick(() => otpRefs.value[0]?.focus());
    } catch {
        globalError.value = 'An unexpected error occurred while resending the OTP.';
    } finally {
        isLoading.value = false;
    }
};

// STEP 3: Complete Registration
const createAccount = async () => {
    form.clearErrors();
    globalError.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/register/complete', {
            method: 'POST',
            headers: getHeaders(), // Uses your existing CSRF helper
            body: JSON.stringify({
                mobile_number: form.mobile_number,
                password: form.password,
                password_confirmation: form.password_confirmation,
                verification_token: form.verification_token
            })
        });

        const data = await response.json();

        if (!response.ok) {
            // Handle validation errors (like passwords not matching)
            if (data.errors && data.errors.password) {
                form.setError('password', data.errors.password[0]);
            } else {
                globalError.value = data.message || 'Failed to create account.';
            }
            
            return;
        }

        // The backend logged us in successfully in the background, now show Step 4!
        currentStep.value = 4;
        
    } catch {
        globalError.value = 'An unexpected error occurred while creating your account.';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:py-16">

        <img 
            src="/assets/bg1.jpg" 
            alt="Background" 
            class="absolute top-0 left-0 w-full h-[40vh] md:h-[50vh] object-cover rounded-b-[15%] sm:rounded-b-[20%] md:rounded-b-[25%] lg:rounded-b-[20%] z-0"
        >

        <div class="w-full max-w-md sm:max-w-lg relative z-10 mt-12 sm:mt-8"> 
            <div class="bg-white shadow-2xl rounded-2xl border border-slate-200 relative pt-8 sm:pt-10">
                
                <img 
                    src="/assets/fismpc_logo.png" 
                    alt="Logo" 
                    class="absolute p-1 -top-10 sm:-top-12 left-1/2 transform -translate-x-1/2 h-20 w-20 sm:h-24 sm:w-24 rounded-full shadow-lg bg-white z-20"
                >

                <div class="p-5 sm:p-6 pt-8 sm:pt-8">
                    
                    <div v-if="currentStep < 4" class="text-center px-2 sm:px-4">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#033e94]">{{ currentTitle }}</h1>
                        <div class="w-full h-0.5 bg-blue-100 mt-2"></div>
                        <p class="text-[#033e94] mt-2 font-medium text-sm sm:text-base">{{ currentSubtitle }}</p>
                    </div>

                    <div v-if="globalError && currentStep < 4" class="mt-4 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-medium">
                        {{ globalError }}
                    </div>

                    <div v-if="currentStep < 4" class="flex space-x-2 pb-3 mt-6">
                        <div v-for="step in 4" :key="step"
                             class="h-1.5 flex-1 rounded-full transition-colors duration-500"
                             :class="currentStep >= step ? 'bg-[#033e94]' : 'bg-slate-200'">
                        </div>
                    </div>

                    <div v-if="currentStep === 1" class="space-y-4 px-2 sm:px-4 animate-in fade-in duration-500 mt-4">
                        <div class="space-y-1">
                            <label class="text-xs sm:text-sm font-bold text-[#033e94] uppercase tracking-wider">Enter Fullname</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                </div>
                                <input v-model="form.full_name" type="text" placeholder="Juan Dela Cruz" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                            </div>
                            <InputError :message="form.errors.full_name" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs sm:text-sm font-bold text-[#033e94] uppercase tracking-wider">Enter Mobile Number</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <input v-model="form.mobile_number" type="tel" maxlength="11" placeholder="09123456789" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base" @input="form.mobile_number = form.mobile_number.replace(/[^0-9]/g, '')">
                            </div>
                            <InputError :message="form.errors.mobile_number" />
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 sm:gap-0 mt-8 pt-4 border-t border-gray-100">
                            <Link href="/login" class="text-sm font-semibold text-gray-500 hover:text-[#033e94] transition">
                                Back to login
                            </Link>
                            <button @click="registerNow" :disabled="isLoading" type="button" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white font-bold bg-[#033e94] hover:bg-blue-800 transition shadow-md disabled:opacity-50 text-sm sm:text-base">
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Register Now</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="currentStep === 2" class="space-y-6 px-2 sm:px-4 animate-in fade-in duration-500 mt-6">
                        
                        <div class="flex justify-center gap-2 sm:gap-3">
                            <input 
                                v-for="(digit, index) in otpDigits" 
                                :key="index"
                                :ref="el => { if(el) otpRefs[index] = el as HTMLInputElement }"
                                v-model="otpDigits[index]"
                                @input="handleOtpInput($event, index)"
                                @keydown.delete="handleOtpDelete($event, index)"
                                type="text" 
                                maxlength="1"
                                class="w-10 h-12 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-black text-[#033e94] bg-white border-2 border-blue-500 rounded-xl outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all shadow-sm"
                            >
                        </div>
                        <InputError :message="form.errors.otp" class="text-center" />

                        <div class="text-center flex flex-col space-y-1">
                            <span class="text-sm text-gray-600 font-medium">Didn't receive OTP?</span>
                            <span v-if="countdown > 0" class="text-sm text-gray-500 font-bold">Resend available in {{ formattedTime }}</span>
                            <button v-else @click="resendOtp" :disabled="isLoading" class="text-sm font-bold text-blue-600 hover:underline disabled:opacity-50">Resend OTP now</button>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 sm:gap-0 mt-8 pt-4 border-t border-gray-100">
                            <Link href="/login" class="text-sm font-semibold text-gray-500 hover:text-[#033e94] transition">
                                Back to login
                            </Link>
                            <button @click="verifyOtp" type="button" :disabled="form.otp.length < 6 || isLoading" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white font-bold bg-[#033e94] hover:bg-blue-800 transition shadow-md disabled:opacity-50 text-sm sm:text-base">
                                <span v-if="isLoading">Verifying...</span>
                                <span v-else>Verify OTP</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="currentStep === 3" class="space-y-4 px-2 sm:px-4 animate-in fade-in duration-500 mt-4">
                        <div class="space-y-1">
                            <div class="relative flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0 z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Password" class="w-full h-10 pl-3 pr-10 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-gray-400 hover:text-blue-600 focus:outline-none z-10">
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-1">
                            <div class="relative flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0 z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" placeholder="Confirm Password" class="w-full h-10 pl-3 pr-10 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 text-gray-400 hover:text-blue-600 focus:outline-none z-10">
                                    <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 pt-2">
                            <label class="flex items-start sm:items-center space-x-3 cursor-pointer">
                                <input type="checkbox" v-model="form.agreed_terms" class="mt-1 sm:mt-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="text-sm text-gray-800 font-medium leading-tight">
                                    I agree to the <a href="#" class="text-blue-600 underline font-bold hover:text-blue-800">Terms and conditions</a>
                                </span>
                            </label>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 sm:gap-0 mt-8 pt-4 border-t border-gray-100">
                            <Link href="/login" class="text-sm font-semibold text-gray-500 hover:text-[#033e94] transition">
                                Back to login
                            </Link>
                            <button @click="createAccount" :disabled="isLoading || !form.agreed_terms" type="button" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white font-bold bg-green-600 hover:bg-green-700 transition shadow-md disabled:opacity-50 flex items-center justify-center text-sm sm:text-base">
                                <span v-if="isLoading">Creating...</span>
                                <span v-else>Create account</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="currentStep === 4" class="px-2 sm:px-4 pb-2 pt-4 text-center animate-in fade-in duration-500">
                        
                        <div class="mb-4 flex justify-center">
                            <img src="https://illustrations.popsy.co/blue/freelancer.svg" alt="Success Image" class="h-44 w-44 object-contain drop-shadow-sm">
                        </div>

                        <h2 class="text-3xl font-extrabold text-[#033e94] mb-1">{{ currentTitle }}</h2>
                        <p class="text-[#033e94] font-medium text-sm sm:text-base mb-6">{{ currentSubtitle }}</p>
                        
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 mb-8">
                            <p class="text-sm text-gray-700 leading-relaxed">
                                kindly <strong class="text-blue-800">proceed to your profile to complete</strong> verification and gain full access to our services
                            </p>
                        </div>
                        
                        <Link href="/dashboard" class="block w-full bg-[#033e94] text-white py-3.5 rounded-xl font-bold hover:bg-blue-800 transition-all text-center shadow-md">
                            Go to Dashboard
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>