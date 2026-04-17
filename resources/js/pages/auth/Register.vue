<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';

// 1. Form State Management
const form = useForm({
    role: '',
    
    // Step 2: Basic Info
    username: '',
    full_name: '',
    email: '',
    gender: '',
    dob: '',
    age: '',
    mobile_number: '',
    region: '',
    province: '',
    city: '',
    barangay: '',
    
    // Step 3: Admin
    official_position: '',
    department: '',
    auth_letter: null as File | null,
    
    // Step 3 & 4: Government
    agency_name: '',
    office_department: '',
    agency_type: '',
    purpose: '',
    position: '',
    interest: '',
    endorsement_letter: null as File | null,
    
    // Step 3: Inventor
    inventor_type: '',
    innovation_field: '',
    
    // Shared ID Uploads
    id_front: null as File | null,
    id_back: null as File | null,
    
    // Step 5: Security & Terms
    password: '',
    password_confirmation: '',
    agreed_terms: false,
    authorized_rep: false,
    data_privacy: false,
});

// 2. Multi-step Navigation State
const currentStep = ref(1);
const pageTitle = ref("Account Registration");
const pageSubtitle = ref("Choose Account");

const selectRole = (role: string) => {
    form.role = role;
    currentStep.value = 2;
};

const nextStep = () => {
    if (currentStep.value === 3 && form.role !== 'government') {
        currentStep.value = 5;
    } else {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value === 5 && form.role !== 'government') {
        currentStep.value = 3;
    } else {
        currentStep.value--;
    }
};

// 3. File Preview Logic
const previews = ref({
    auth_letter: '',
    id_front: '',
    id_back: '',
    endorsement_letter: ''
});

type FileFields = 'auth_letter' | 'id_front' | 'id_back' | 'endorsement_letter';

const handleFileUpload = (event: Event, field: FileFields) => {
    const target = event.target as HTMLInputElement;
    
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        
        (form as any)[field] = file; 
        
        previews.value[field] = URL.createObjectURL(file);
    }
};

// 4. Age Calculation Logic
const calculateAge = () => {
    if (!form.dob) return;

    const today = new Date();
    const birthDate = new Date(form.dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    form.age = age.toString();
};

// 5. Philippine Address Logic (Converted from Alpine.js)
const PSGC_API = 'https://psgc.gitlab.io/api';
const regions = ref<any[]>([]);
const provinces = ref<any[]>([]);
const cities = ref<any[]>([]);
const barangays = ref<any[]>([]);

const selectedRegionCode = ref('');
const selectedProvinceCode = ref('');
const selectedCityCode = ref('');

const isNCR = computed(() => selectedRegionCode.value.startsWith('13'));

onMounted(async () => {
    try {
        const res = await fetch(`${PSGC_API}/regions/`);
        const data = await res.json();
        regions.value = data.sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching regions:', error);
    }
});

const fetchProvinces = async () => {
    provinces.value = []; cities.value = []; barangays.value = [];
    selectedProvinceCode.value = ''; selectedCityCode.value = ''; form.barangay = '';
    
    const region = regions.value.find(r => r.code === selectedRegionCode.value);
    form.region = region ? region.name : '';

    if (!selectedRegionCode.value) return;

    if (isNCR.value) {
        form.province = 'Metro Manila';
        fetchCities(true);
    } else {
        const res = await fetch(`${PSGC_API}/regions/${selectedRegionCode.value}/provinces/`);
        const data = await res.json();
        provinces.value = data.sort((a: any, b: any) => a.name.localeCompare(b.name));
    }
};

const fetchCities = async (byRegion = false) => {
    cities.value = []; barangays.value = [];
    selectedCityCode.value = ''; form.barangay = '';

    if (!isNCR.value) {
        const province = provinces.value.find(p => p.code === selectedProvinceCode.value);
        form.province = province ? province.name : '';
        
        if (!selectedProvinceCode.value) return;
    }

    const url = byRegion
        ? `${PSGC_API}/regions/${selectedRegionCode.value}/cities-municipalities/`
        : `${PSGC_API}/provinces/${selectedProvinceCode.value}/cities-municipalities/`;

    const res = await fetch(url);
    const data = await res.json();
    cities.value = data.sort((a: any, b: any) => a.name.localeCompare(b.name));
};

const fetchBarangays = async () => {
    barangays.value = []; form.barangay = '';
    
    const city = cities.value.find(c => c.code === selectedCityCode.value);
    form.city = city ? city.name : '';

    if (!selectedCityCode.value) return;

    const res = await fetch(`${PSGC_API}/cities-municipalities/${selectedCityCode.value}/barangays/`);
    const data = await res.json();
    barangays.value = data.sort((a: any, b: any) => a.name.localeCompare(b.name));
};

// 6. Form Submission
const submit = () => {
    form.post('/register', {
        preserveScroll: true,
        onSuccess: () => {
            currentStep.value = 6;
        },
        onError: () => {
            if (form.errors.password) currentStep.value = 5;
        }
    });
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
                    <div class="text-center px-2 sm:px-4">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#033e94]">{{ pageTitle }}</h1>
                        <div class="w-full h-0.5 bg-blue-100 mt-2"></div>
                        <p class="text-[#033e94] mt-2 font-medium text-sm sm:text-base">{{ pageSubtitle }}</p>
                    </div>

                    <div class="flex space-x-2 pb-3 mt-6">
                        <template v-for="step in 5" :key="step">
                            <div v-if="!(step === 4 && ['admin', 'inventor'].includes(form.role))"
                                 class="h-1.5 flex-1 rounded-full transition-colors duration-500"
                                 :class="currentStep >= step ? 'bg-[#033e94]' : 'bg-slate-200'">
                            </div>
                        </template>
                    </div>

                    <div v-if="currentStep === 1" class="space-y-4 px-2 sm:px-4 animate-in fade-in duration-500 mt-4">
                        <button @click="selectRole('admin')" type="button" class="w-full bg-[#033e94] hover:bg-blue-800 text-white font-medium py-3 px-4 rounded-xl transition-all duration-200 shadow-md">
                            Admin
                        </button>
                        <button @click="selectRole('government')" type="button" class="w-full bg-[#033e94] hover:bg-blue-800 text-white font-medium py-3 px-4 rounded-xl transition-all duration-200 shadow-md">
                            Government
                        </button>
                        <button @click="selectRole('inventor')" type="button" class="w-full bg-[#033e94] hover:bg-blue-800 text-white font-medium py-3 px-4 rounded-xl transition-all duration-200 shadow-md">
                            Inventor
                        </button>
                    </div>

                    <div v-if="currentStep === 2" class="space-y-3 px-2 sm:px-4 animate-in fade-in duration-500">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#033e94]">Username</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <input v-model="form.username" type="text" placeholder="Enter username" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base" @input="form.username = form.username.replace(/\s/g, '')">
                            </div>
                            <InputError :message="form.errors.username" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#033e94]">Full Name</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                </div>
                                <input v-model="form.full_name" type="text" placeholder="Full Name" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                            </div>
                            <InputError :message="form.errors.full_name" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#033e94]">Email Address</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <input v-model="form.email" type="email" placeholder="example@email.com" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#033e94]">Gender</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <select v-model="form.gender" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <InputError :message="form.errors.gender" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 sm:space-x-2 sm:gap-0">
                            <div class="w-full sm:w-2/3 space-y-1">
                                <label class="text-sm font-semibold text-[#033e94]">Date of Birth</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <input v-model="form.dob" type="date" @change="calculateAge" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                                <InputError :message="form.errors.dob" />
                            </div>
                            <div class="w-full sm:w-1/3 space-y-1">
                                <label class="text-sm font-semibold text-[#033e94]">Age</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                                    <div class="h-10 w-10 bg-gray-400 flex items-center justify-center text-white shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <input v-model="form.age" type="number" readonly class="w-full h-10 px-2 outline-none text-gray-600 font-bold bg-transparent cursor-not-allowed text-center text-sm sm:text-base">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#033e94]">Mobile Number</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <input v-model="form.mobile_number" type="tel" maxlength="11" placeholder="09123456789" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base" @input="form.mobile_number = form.mobile_number.replace(/[^0-9]/g, '')">
                            </div>
                            <InputError :message="form.errors.mobile_number" />
                        </div>

                        <p class="text-[#033e94] font-bold mt-4 border-b pb-1 border-gray-100">Address Information</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                                <select v-model="selectedRegionCode" @change="fetchProvinces" class="w-full h-10 px-2 outline-none text-xs sm:text-sm bg-transparent text-gray-800">
                                    <option value="">Region...</option>
                                    <option v-for="region in regions" :key="region.code" :value="region.code">{{ region.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white" :class="{'opacity-50 pointer-events-none': !selectedRegionCode}">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div>
                                <select v-model="selectedProvinceCode" @change="fetchCities()" class="w-full h-10 px-2 outline-none text-xs sm:text-sm bg-transparent text-gray-800">
                                    <option value="">Province...</option>
                                    <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white" :class="{'opacity-50 pointer-events-none': !selectedProvinceCode && !isNCR}">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg></div>
                                <select v-model="selectedCityCode" @change="fetchBarangays" class="w-full h-10 px-2 outline-none text-xs sm:text-sm bg-transparent text-gray-800">
                                    <option value="">City...</option>
                                    <option v-for="city in cities" :key="city.code" :value="city.code">{{ city.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white" :class="{'opacity-50 pointer-events-none': !selectedCityCode}">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
                                <select v-model="form.barangay" class="w-full h-10 px-2 outline-none text-xs sm:text-sm bg-transparent text-gray-800">
                                    <option value="">Barangay...</option>
                                    <option v-for="brgy in barangays" :key="brgy.code" :value="brgy.name">{{ brgy.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentStep === 3" class="space-y-4 px-2 sm:px-4 animate-in fade-in duration-500">
                        
                        <template v-if="form.role === 'admin'">
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Official Position</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                    <input v-model="form.official_position" type="text" placeholder="Official Position" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                                <InputError :message="form.errors.official_position" />
                            </div>
                            
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Department/Office</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div>
                                    <input v-model="form.department" type="text" placeholder="Department/Office" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                                <InputError :message="form.errors.department" />
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-semibold text-[#033e94]">Upload Authorization Letter</label>
                                <input type="file" @change="e => handleFileUpload(e, 'auth_letter')" accept=".pdf,.jpg,.jpeg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[#033e94] hover:file:bg-blue-100 mt-2 cursor-pointer"/>
                            </div>
                        </template>

                        <template v-if="form.role === 'government'">
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Agency Name</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg></div>
                                    <input v-model="form.agency_name" type="text" placeholder="Agency Name" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Department</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg></div>
                                    <input v-model="form.office_department" type="text" placeholder="Department" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Agency Type</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg></div>
                                    <select v-model="form.agency_type" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                        <option value="">Choose Type</option>
                                        <option value="National">National</option>
                                        <option value="Local">Local</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Purpose</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></div>
                                    <select v-model="form.purpose" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                        <option value="">Choose Purpose</option>
                                        <option value="Research">Research</option>
                                        <option value="Policy">Policy</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Position</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                    <input v-model="form.position" type="text" placeholder="Position" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Interest</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg></div>
                                    <select v-model="form.interest" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                        <option value="">Choose Interest</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Agriculture">Agriculture</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template v-if="form.role === 'inventor'">
                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1">Inventor Type</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                    <select v-model="form.inventor_type" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                        <option value="">Inventor Type</option>
                                        <option value="Individual">Individual</option>
                                        <option value="Organization">Organization</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-[#033e94] block mb-1 mt-3">Field of Innovation</label>
                                <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                    <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg></div>
                                    <select v-model="form.innovation_field" class="w-full h-10 px-3 outline-none bg-transparent text-gray-800 text-sm sm:text-base">
                                        <option value="">Field of Innovation</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Agriculture">Agriculture</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer overflow-hidden hover:bg-blue-50 transition bg-slate-50">
                                    <img v-if="previews.id_front" :src="previews.id_front" class="h-full w-full object-cover"/>
                                    <span v-else class="text-xs text-gray-600 font-bold uppercase">ID Front</span>
                                    <input type="file" @change="e => handleFileUpload(e, 'id_front')" class="hidden"/>
                                </label>
                                <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer overflow-hidden hover:bg-blue-50 transition bg-slate-50">
                                    <img v-if="previews.id_back" :src="previews.id_back" class="h-full w-full object-cover"/>
                                    <span v-else class="text-xs text-gray-600 font-bold uppercase">ID Back</span>
                                    <input type="file" @change="e => handleFileUpload(e, 'id_back')" class="hidden"/>
                                </label>
                            </div>
                        </template>
                    </div>

                    <div v-if="currentStep === 4 && form.role === 'government'" class="space-y-4 px-2 sm:px-4 animate-in fade-in duration-500">
                        <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer overflow-hidden hover:bg-blue-50 transition bg-slate-50">
                            <img v-if="previews.endorsement_letter" :src="previews.endorsement_letter" class="h-full w-full object-cover"/>
                            <span v-else class="text-sm text-gray-600 font-bold uppercase">Upload Endorsement</span>
                            <input type="file" @change="e => handleFileUpload(e, 'endorsement_letter')" class="hidden"/>
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer overflow-hidden hover:bg-blue-50 transition bg-slate-50">
                                <img v-if="previews.id_front" :src="previews.id_front" class="h-full w-full object-cover"/>
                                <span v-else class="text-xs text-gray-600 font-bold uppercase">Gov ID Front</span>
                                <input type="file" @change="e => handleFileUpload(e, 'id_front')" class="hidden"/>
                            </label>
                            <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer overflow-hidden hover:bg-blue-50 transition bg-slate-50">
                                <img v-if="previews.id_back" :src="previews.id_back" class="h-full w-full object-cover"/>
                                <span v-else class="text-xs text-gray-600 font-bold uppercase">Gov ID Back</span>
                                <input type="file" @change="e => handleFileUpload(e, 'id_back')" class="hidden"/>
                            </label>
                        </div>
                    </div>

                    <div v-if="currentStep === 5" class="space-y-3 px-2 sm:px-4 animate-in fade-in duration-500">
                        <div>
                            <label class="text-sm font-semibold text-[#033e94] block mb-1">Password</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <input v-model="form.password" type="password" placeholder="Password" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-[#033e94] block mb-1 mt-2">Confirm Password</label>
                            <div class="flex items-center h-10 shadow-sm rounded-lg overflow-hidden border border-gray-200 focus-within:border-blue-500 bg-white">
                                <div class="h-10 w-10 bg-[#033e94] flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="w-full h-10 px-3 outline-none text-gray-800 bg-transparent text-sm sm:text-base">
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <label class="flex items-start sm:items-center space-x-3 cursor-pointer">
                                <input type="checkbox" v-model="form.agreed_terms" class="mt-1 sm:mt-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="text-sm text-gray-800 font-medium leading-tight">I agree to the Terms of Service</span>
                            </label>
                            <label class="flex items-start sm:items-center space-x-3 cursor-pointer">
                                <input type="checkbox" v-model="form.authorized_rep" class="mt-1 sm:mt-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="text-sm text-gray-800 font-medium leading-tight">I am an Authorized Representative</span>
                            </label>
                            <label class="flex items-start sm:items-center space-x-3 cursor-pointer">
                                <input type="checkbox" v-model="form.data_privacy" class="mt-1 sm:mt-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="text-sm text-gray-800 font-medium leading-tight">I accept the Data Privacy Policy</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="currentStep === 6" class="p-8 text-center animate-in fade-in duration-500">
                        <div class="mb-6 inline-flex items-center justify-center w-20 h-20 bg-green-50 rounded-full">
                            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-black text-gray-800 mb-2">Registration Saved!</h2>
                        <p class="text-sm text-gray-500 mb-4">Your account has been created successfully.</p>
                        
                        <div class="w-40 h-40 mx-auto flex items-center justify-center border-2 border-blue-600 rounded-xl mb-6 bg-gray-50">
                            <span class="text-gray-400 text-xs font-semibold">QR Code Area</span>
                        </div>
                        
                        <a href="/login" class="block w-full bg-[#033e94] text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition-all text-center shadow-md">
                            Proceed to Login
                        </a>
                    </div>

                    <div v-if="currentStep < 6" class="flex justify-between mt-8 pt-4 border-t border-gray-100 px-2 sm:px-4">
                        <a v-if="currentStep === 1" href="/" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition text-sm sm:text-base">
                            Cancel
                        </a>
                        <button v-else @click="prevStep" type="button" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition text-sm sm:text-base">
                            Back
                        </button>

                        <template v-if="currentStep > 1">
                            <button v-if="currentStep === 5" @click="submit" :disabled="form.processing" type="button" class="px-6 py-2.5 rounded-xl text-white font-bold bg-green-600 hover:bg-green-700 transition shadow-md disabled:opacity-50 flex items-center text-sm sm:text-base">
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Submit</span>
                            </button>
                            <button v-else @click="nextStep" type="button" class="px-6 py-2.5 rounded-xl text-white font-bold bg-[#033e94] hover:bg-blue-800 transition shadow-md text-sm sm:text-base">
                                Next Step
                            </button>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>