@extends('shared::layouts.admin')

@section('title', 'Add User - Componentes')

@section('content')
<div>
    <div class="grid grid-cols-1 lg:grid-cols-12 justify-center">
        <div class="col-span-12 lg:col-span-10 xl:col-span-8 2xl:col-span-6 2xl:col-start-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="title-card">Create New User</h2>
                </div>
                <div class="card-body bg-accent-50">
                    <!-- Profile Image Section -->
                    <div class="mb-6">
                        <h4 class="text-base font-semibold text-black-500 mb-4">Profile Image</h4>
                        
                        <!-- Upload Image Component -->
                        <div class="flex justify-center mb-6">
                            <div class="relative">
                                <!-- Default Upload Area -->
                                <div id="avatar-upload-area" class="w-32 h-32 rounded-full border-2 border-dashed border-primary-200 bg-primary-50 flex items-center justify-center cursor-pointer hover:bg-primary-100 transition-colors duration-200">
                                    <div class="text-center">
                                        <x-solar-camera-outline class="w-8 h-8 text-primary-300 mx-auto mb-2" />
                                        <p class="text-base text-primary-300">Upload Photo</p>
                                    </div>
                                    <input type="file" id="avatar-upload" accept="image/*" class="hidden">
                                </div>
                                
                                <!-- Preview Area (hidden by default) -->
                                <div id="avatar-preview" class="hidden relative w-32 h-32 rounded-full overflow-hidden">
                                    <img id="avatar-preview-img" src="#" alt="Preview" class="w-full h-full object-cover">
                                    <button type="button" id="avatar-remove" 
                                            class="absolute top-2 right-2 w-6 h-6 bg-error-200 text-accent-50 rounded-full flex items-center justify-center hover:bg-error-500 transition-colors">
                                        <x-solar-close-circle-outline class="w-4 h-4" />
                                    </button>
                                </div>
                                
                                <!-- Edit Button for Preview -->
                                <button type="button" id="avatar-edit" 
                                        class="hidden absolute bottom-0 right-0 w-8 h-8 bg-primary-200 text-accent-50 rounded-full flex items-center justify-center border-2 border-accent-50 hover:bg-primary-500 transition-colors">
                                    <x-solar-camera-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- User Form -->
                    <form class="space-y-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h5 class="text-base font-semibold text-black-500 border-b border-accent-100 pb-2">Basic Information</h5>
                            
                            <div>
                                <label for="fullname" class="block text-base text-black-500 font-semibold mb-2">
                                    Full Name <span class="text-error-200">*</span>
                                </label>
                                <input type="text" id="fullname" name="fullname" required
                                       class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                                       placeholder="Enter full name">
                            </div>

                            <div>
                                <label for="email" class="block text-base text-black-500 font-semibold mb-2">
                                    Email Address <span class="text-error-200">*</span>
                                </label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                                       placeholder="Enter email address">
                            </div>

                            <div>
                                <label for="phone" class="block text-base text-black-500 font-semibold mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                                       placeholder="Enter phone number">
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="space-y-4">
                            <h5 class="text-base font-semibold text-black-500 border-b border-accent-100 pb-2">Professional Information</h5>
                            
                            <div>
                                <label for="department" class="block text-base text-black-500 font-semibold mb-2">
                                    Department <span class="text-error-400">*</span>
                                </label>
                                <select id="department" name="department" required
                                        class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    <option value="">Select Department</option>
                                    <option value="design">Design</option>
                                    <option value="development">Development</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="hr">Human Resources</option>
                                    <option value="sales">Sales</option>
                                    <option value="finance">Finance</option>
                                </select>
                            </div>

                            <div>
                                <label for="designation" class="block text-base text-black-500 font-semibold mb-2">
                                    Designation <span class="text-error-200">*</span>
                                </label>
                                <select id="designation" name="designation" required
                                        class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    <option value="">Select Designation</option>
                                    <option value="manager">Manager</option>
                                    <option value="senior">Senior Developer</option>
                                    <option value="junior">Junior Developer</option>
                                    <option value="lead">Team Lead</option>
                                    <option value="designer">UI/UX Designer</option>
                                    <option value="analyst">Business Analyst</option>
                                </select>
                            </div>

                            <div>
                                <label for="language" class="block text-base text-black-500 font-semibold mb-2">
                                    Primary Language <span class="text-error-200">*</span>
                                </label>
                                <select id="language" name="language" required
                                        class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                                    <option value="">Select Language</option>
                                    <option value="english">English</option>
                                    <option value="spanish">Spanish</option>
                                    <option value="french">French</option>
                                    <option value="german">German</option>
                                    <option value="portuguese">Portuguese</option>
                                    <option value="italian">Italian</option>
                                </select>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-4">
                            <h5 class="text-base font-semibold text-black-500 border-b border-accent-100 pb-2">Additional Information</h5>
                            
                            <div>
                                <label for="bio" class="block text-base text-black-500 font-semibold mb-2">Bio / Description</label>
                                <textarea id="bio" name="bio" rows="4"
                                          class="w-full px-4 py-3 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none resize-none"
                                          placeholder="Write a brief description about the user..."></textarea>
                            </div>

                            <!-- User Status -->
                            <div>
                                <label class="block text-base text-black-500 font-semibold mb-2">User Status</label>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="status" value="active" checked
                                               class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400">
                                        <span class="text-base text-black-400">Active</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="status" value="inactive"
                                               class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400">
                                        <span class="text-base text-black-400">Inactive</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div>
                                <label class="block body-base text-black-500 font-medium mb-2">Permissions</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="read"
                                               class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400 rounded">
                                        <span class="text-base text-black-400">Read Access</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="write"
                                               class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400 rounded">
                                        <span class="text-base text-black-400">Write Access</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="admin"
                                               class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400 rounded">
                                        <span class="text-base text-black-400">Admin Access</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-center gap-4 pt-6 border-t border-accent-100">
                            <button type="button" class="px-8 py-3 border border-error-200 text-error-200 text-base rounded-lg hover:bg-error-200 hover:text-accent-50 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit" class="px-8 py-3 bg-primary-200 text-accent-50 text-base rounded-lg hover:bg-primary-500 transition-colors duration-200 flex items-center gap-2">
                                <x-solar-user-plus-outline class="w-5 h-5" />
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('avatar-upload-area');
    const input = document.getElementById('avatar-upload');
    const preview = document.getElementById('avatar-preview');
    const previewImg = document.getElementById('avatar-preview-img');
    const editBtn = document.getElementById('avatar-edit');
    const removeBtn = document.getElementById('avatar-remove');

    // Handle upload area click
    uploadArea.addEventListener('click', () => input.click());
    editBtn.addEventListener('click', () => input.click());

    // Handle file selection
    input.addEventListener('change', (e) => {
        if (e.target.files.length) {
            const file = e.target.files[0];
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                return;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB.');
                return;
            }

            const src = URL.createObjectURL(file);
            previewImg.src = src;
            
            // Show preview, hide upload area
            uploadArea.classList.add('hidden');
            preview.classList.remove('hidden');
            editBtn.classList.remove('hidden');
        }
    });

    // Handle remove image
    removeBtn.addEventListener('click', () => {
        // Clean up object URL
        if (previewImg.src.startsWith('blob:')) {
            URL.revokeObjectURL(previewImg.src);
        }
        
        // Reset form
        input.value = '';
        previewImg.src = '#';
        
        // Show upload area, hide preview
        preview.classList.add('hidden');
        editBtn.classList.add('hidden');
        uploadArea.classList.remove('hidden');
    });

    // Handle form submission
    document.querySelector('form').addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Add image file if selected
        if (input.files.length) {
            formData.append('avatar', input.files[0]);
        }
        
        // Here you would normally send the data to your backend
        console.log('Form Data:', Object.fromEntries(formData));
        
        // Show success message (for demo)
        alert('User created successfully!');
    });
});
</script>

@endsection 