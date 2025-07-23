@extends('shared::layouts.admin')

@section('title', 'User Profile - Componentes')

@section('content')
<div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Profile Card -->
        <div class="col-span-12 lg:col-span-4">
            <div class="card">
                <!-- Header with background -->
                <div class="relative h-32 bg-gradient-to-r from-primary-200 to-secondary-200 rounded-t-lg">
                    <div class="absolute inset-0 bg-primary-300 bg-opacity-90"></div>
                </div>
                
                <!-- Profile Content -->
                <div class="pb-6 px-6 -mt-16 relative">
                    <!-- Avatar -->
                    <div class="text-center border-b border-white-100 pb-4">
                        <div class="relative inline-block">
                            <img src="https://via.placeholder.com/120x120/6366f1/ffffff?text=JJ" 
                                 alt="Profile" 
                                 class="w-32 h-32 rounded-full border-4 border-white-50 object-cover mx-auto bg-white-50">
                            <div class="absolute bottom-2 right-2 w-8 h-8 bg-primary-400 rounded-full flex items-center justify-center border-2 border-white-50 cursor-pointer hover:bg-primary-500 transition-colors">
                                <x-solar-camera-outline class="w-4 h-4 text-white-50" />
                            </div>
                        </div>
                        <h3 class="text-2xl font-semibold text-black-500 mt-4 mb-1">Jacob Jones</h3>
                        <p class="text-base text-black-300">ifrandom@gmail.com</p>
                    </div>
                    
                    <!-- Personal Info -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-black-500 mb-4">Personal Info</h4>
                        <ul class="space-y-3">
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Full Name:</span>
                                <span class="flex-1 text-base text-black-300">Will Jonto</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Email:</span>
                                <span class="flex-1 text-base text-black-300">willjontoax@gmail.com</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Phone:</span>
                                <span class="flex-1 text-base text-black-300">(1) 2536 2561 2365</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Department:</span>
                                <span class="flex-1 text-base text-black-300">Design</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Designation:</span>
                                <span class="flex-1 text-base text-black-300">UI UX Designer</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Languages:</span>
                                <span class="flex-1 text-base text-black-300">English</span>
                            </li>
                            <li class="flex">
                                <span class="w-32 text-base text-black-500 font-medium">Bio:</span>
                                <span class="flex-1 text-base text-black-300">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-span-12 lg:col-span-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Profile Management</h2>
                </div>
                <div class="card-body bg-white-50">
                    <!-- Tabs -->
                    <div class="mb-6" x-data="{ activeTab: 'edit' }">
                        <div class="flex border-b border-white-100">
                            <button @click="activeTab = 'edit'" 
                                    :class="activeTab === 'edit' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400 hover:border-white-200'"
                                    class="py-3 px-4 border-b-2 text-base font-semibold transition-colors duration-200 flex items-center gap-2">
                                <x-solar-pen-new-square-outline class="w-5 h-5" />
                                Edit Profile
                            </button>
                            <button @click="activeTab = 'password'" 
                                    :class="activeTab === 'password' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400 hover:border-white-200'"
                                    class="py-3 px-4 border-b-2 text-base font-semibold transition-colors duration-200 flex items-center gap-2">
                                <x-solar-lock-password-outline class="w-5 h-5" />
                                Change Password
                            </button>
                            <button @click="activeTab = 'notifications'" 
                                    :class="activeTab === 'notifications' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400 hover:border-white-200'"
                                    class="py-3 px-4 border-b-2 text-base font-semibold transition-colors duration-200 flex items-center gap-2">
                                <x-solar-bell-outline class="w-5 h-5" />
                                Notifications
                            </button>
                        </div>

                        <!-- Edit Profile Tab -->
                        <div x-show="activeTab === 'edit'" class="mt-6">
                            <h4 class="text-basex font-semibold text-black-500 mb-4">Profile Image</h4>
                            
                            <!-- Upload Image Component -->
                            <div class="mb-6">
                                <div class="relative inline-block">
                                    <div class="w-24 h-24 rounded-full bg-primary-50 border-2 border-dashed border-primary-200 flex items-center justify-center cursor-pointer hover:bg-primary-100 transition-colors" id="profile-upload-area">
                                        <div class="text-center">
                                            <x-solar-camera-outline class="w-6 h-6 text-primary-300 mx-auto mb-1" />
                                            <p class="text-base text-primary-300">Upload</p>
                                        </div>
                                        <input type="file" id="profile-image-upload" accept="image/*" class="hidden">
                                    </div>
                                </div>
                                <div id="profile-preview" class="hidden w-24 h-24 rounded-full overflow-hidden">
                                    <img id="profile-preview-img" src="#" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            </div>

                            <form class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="fullname" class="block text-base text-black-500 font-semibold mb-2">
                                            Full Name <span class="text-error-200">*</span>
                                        </label>
                                        <input type="text" id="fullname" value="Jacob Jones" 
                                               class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-base text-black-500 font-semibold mb-2">
                                            Email <span class="text-error-200">*</span>
                                        </label>
                                        <input type="email" id="email" value="ifrandom@gmail.com" 
                                               class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-base text-black-500 font-semibold mb-2">Phone</label>
                                        <input type="tel" id="phone" value="(1) 2536 2561 2365" 
                                               class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="department" class="block text-base text-black-500 font-semibold mb-2">
                                            Department <span class="text-error-200">*</span>
                                        </label>
                                        <select id="department" 
                                                class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                            <option>Design</option>
                                            <option>Development</option>
                                            <option>Marketing</option>
                                            <option>HR</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="designation" class="block text-base text-black-500 font-semibold mb-2">
                                            Designation <span class="text-error-200">*</span>
                                        </label>
                                        <select id="designation" 
                                                class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                            <option>UI UX Designer</option>
                                            <option>Frontend Developer</option>
                                            <option>Backend Developer</option>
                                            <option>Manager</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="language" class="block text-base text-black-500 font-semibold mb-2">
                                            Language <span class="text-error-200">*</span>
                                        </label>
                                        <select id="language" 
                                                class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                            <option>English</option>
                                            <option>Spanish</option>
                                            <option>French</option>
                                            <option>German</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="bio" class="block text-base text-black-500 font-semibold mb-2">Bio</label>
                                    <textarea id="bio" rows="4" 
                                              class="w-full px-4 py-3 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none resize-none"
                                              placeholder="Write your bio...">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</textarea>
                                </div>
                                <div class="flex items-center justify-center gap-3 pt-4">
                                    <button type="button" class="px-6 py-3 border border-error-200 text-error-200 text-base rounded-lg hover:bg-error-200 hover:text-white-50 transition-colors duration-200">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-6 py-3 bg-primary-200 text-white-50 text-base rounded-lg hover:bg-primary-500 transition-colors duration-200">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Tab -->
                        <div x-show="activeTab === 'password'" class="mt-6">
                            <form class="space-y-4 max-w-md">
                                <div>
                                    <label for="new-password" class="block text-base text-black-500 font-semibold mb-2">
                                        New Password <span class="text-error-200">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" id="new-password" 
                                               class="w-full px-4 py-3 pr-12 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                                               placeholder="Enter new password">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-black-300 hover:text-black-400" onclick="togglePassword('new-password')">
                                            <x-solar-eye-outline class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="confirm-password" class="block text-base text-black-500 font-semibold mb-2">
                                        Confirm Password <span class="text-error-200">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" id="confirm-password" 
                                               class="w-full px-4 py-3 pr-12 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                                               placeholder="Confirm password">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-black-300 hover:text-black-400" onclick="togglePassword('confirm-password')">
                                            <x-solar-eye-outline class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 pt-4">
                                    <button type="button" class="px-6 py-3 border border-error-200 text-error-200 text-base rounded-lg hover:bg-error-200 hover:text-white-50 transition-colors duration-200">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-6 py-3 bg-primary-200 text-white-50 text-base rounded-lg hover:bg-primary-500 transition-colors duration-200">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Notifications Tab -->
                        <div x-show="activeTab === 'notifications'" class="mt-6">
                            <div class="space-y-4 max-w-md">
                                <div class="flex items-center justify-between p-4 border border-white-200 rounded-lg">
                                    <div>
                                        <h5 class="text-base text-black-500 font-semibold">Company News</h5>
                                        <p class="text-base text-black-300">Get notified about company updates</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-white-200 rounded-lg">
                                    <div>
                                        <h5 class="text-base text-black-500 font-semibold">Push Notifications</h5>
                                        <p class="text-base text-black-300">Receive push notifications</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-white-200 rounded-lg">
                                    <div>
                                        <h5 class="text-base text-black-500 font-semibold">Weekly Newsletters</h5>
                                        <p class="text-base text-black-300">Get weekly newsletter updates</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-white-200 rounded-lg">
                                    <div>
                                        <h5 class="text-base text-black-500 font-semibold">Order Notifications</h5>
                                        <p class="text-base text-black-300">Get notified about order updates</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('svg');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

// Profile image upload
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('profile-upload-area');
    const input = document.getElementById('profile-image-upload');
    const preview = document.getElementById('profile-preview');
    const previewImg = document.getElementById('profile-preview-img');

    if (uploadArea && input && preview && previewImg) {
        uploadArea.addEventListener('click', () => input.click());
        
        input.addEventListener('change', (e) => {
            if (e.target.files.length) {
                const src = URL.createObjectURL(e.target.files[0]);
                previewImg.src = src;
                uploadArea.classList.add('hidden');
                preview.classList.remove('hidden');
            }
        });
    }
});
</script>

@endsection 