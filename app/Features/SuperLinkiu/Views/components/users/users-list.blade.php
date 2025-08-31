@extends('shared::layouts.admin')

@section('title', 'Users List - Componentes')

@section('content')
<div>
    <div class="card">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h2 class="title-card">All Users</h2>
                <button type="button" class="px-4 py-2 bg-primary-200 text-accent-50 text-base rounded-lg hover:bg-primary-300 transition-colors duration-200 flex items-center gap-2">
                    <x-solar-user-plus-outline class="w-5 h-5" />
                    Add User
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Filters and Search -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <x-solar-magnifer-outline class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-black-300" />
                        <input type="text" id="search" 
                               class="w-full pl-10 pr-4 py-2.5 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none"
                               placeholder="Search users...">
                    </div>
                </div>
                
                <!-- Department Filter -->
                <div class="min-w-0 sm:w-48">
                    <select id="department-filter" 
                            class="w-full px-3 py-2.5 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                        <option value="">All Departments</option>
                        <option value="design">Design</option>
                        <option value="development">Development</option>
                        <option value="marketing">Marketing</option>
                        <option value="hr">Human Resources</option>
                        <option value="sales">Sales</option>
                        <option value="finance">Finance</option>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="min-w-0 sm:w-32">
                    <select id="status-filter" 
                            class="w-full px-3 py-2.5 border border-accent-200 rounded-lg text-base text-black-400 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-accent-100">
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="select-all" 
                                           class="w-4 h-4 text-primary-200 border-accent-200 focus:ring-primary-400 rounded">
                                    <span>User</span>
                                </div>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-200 transition-colors" onclick="sortTable('email')">
                                    Email
                                    <x-solar-sort-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-200 transition-colors" onclick="sortTable('department')">
                                    Department
                                    <x-solar-sort-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-200 transition-colors" onclick="sortTable('designation')">
                                    Designation
                                    <x-solar-sort-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-200 transition-colors" onclick="sortTable('status')">
                                    Status
                                    <x-solar-sort-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-200 transition-colors" onclick="sortTable('created')">
                                    Created
                                    <x-solar-sort-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-center py-3 px-4 text-base text-black-500 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- User Row 1 -->
                        <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors user-row" 
                            data-email="jacob.jones@example.com" 
                            data-department="design" 
                            data-designation="UI UX Designer" 
                            data-status="active" 
                            data-created="2024-01-15">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-400 border-accent-200 focus:ring-primary-400 rounded row-checkbox">
                                    <img src="https://via.placeholder.com/40x40/6366f1/ffffff?text=JJ" 
                                         alt="Jacob Jones" 
                                         class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <h4 class="text-base text-black-500 font-semibold">Jacob Jones</h4>
                                        <p class="text-sm text-black-300">ID: #001</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">jacob.jones@example.com</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-info-50 text-info-400">
                                    Design
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">UI UX Designer</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-success-50 text-success-400">
                                    <span class="w-2 h-2 bg-success-400 rounded-full mr-2"></span>
                                    Active
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">Jan 15, 2024</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 text-info-400 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-warning-400 hover:bg-warning-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-error-400 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteUser(1)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- User Row 2 -->
                        <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors user-row" 
                            data-email="sarah.wilson@example.com" 
                            data-department="development" 
                            data-designation="Frontend Developer" 
                            data-status="active" 
                            data-created="2024-02-10">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-400 border-accent-200 focus:ring-primary-400 rounded row-checkbox">
                                    <img src="https://via.placeholder.com/40x40/ec4899/ffffff?text=SW" 
                                         alt="Sarah Wilson" 
                                         class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <h4 class="text-base text-black-500 font-semibold">Sarah Wilson</h4>
                                        <p class="text-sm text-black-300">ID: #002</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">sarah.wilson@example.com</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-50 text-primary-400">
                                    Development
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">Frontend Developer</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-success-50 text-success-400">
                                    <span class="w-2 h-2 bg-success-400 rounded-full mr-2"></span>
                                    Active
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">Feb 10, 2024</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 text-info-400 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-warning-400 hover:bg-warning-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-error-400 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteUser(2)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- User Row 3 -->
                        <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors user-row" 
                            data-email="mike.johnson@example.com" 
                            data-department="marketing" 
                            data-designation="Marketing Manager" 
                            data-status="inactive" 
                            data-created="2024-01-20">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-400 border-accent-200 focus:ring-primary-400 rounded row-checkbox">
                                    <img src="https://via.placeholder.com/40x40/f59e0b/ffffff?text=MJ" 
                                         alt="Mike Johnson" 
                                         class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <h4 class="text-base text-black-500 font-semibold">Mike Johnson</h4>
                                        <p class="text-sm text-black-300">ID: #003</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">mike.johnson@example.com</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-secondary-50 text-secondary-400">
                                    Marketing
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">Marketing Manager</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-error-50 text-error-400">
                                    <span class="w-2 h-2 bg-error-400 rounded-full mr-2"></span>
                                    Inactive
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">Jan 20, 2024</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 text-info-400 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-warning-400 hover:bg-warning-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-error-400 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteUser(3)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- User Row 4 -->
                        <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors user-row" 
                            data-email="emma.davis@example.com" 
                            data-department="hr" 
                            data-designation="HR Specialist" 
                            data-status="active" 
                            data-created="2024-03-05">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-400 border-accent-200 focus:ring-primary-400 rounded row-checkbox">
                                    <img src="https://via.placeholder.com/40x40/10b981/ffffff?text=ED" 
                                         alt="Emma Davis" 
                                         class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <h4 class="text-base text-black-500 font-semibold">Emma Davis</h4>
                                        <p class="text-sm text-black-300">ID: #004</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">emma.davis@example.com</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-success-50 text-success-400">
                                    Human Resources
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base text-black-400">HR Specialist</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-success-50 text-success-400">
                                    <span class="w-2 h-2 bg-success-400 rounded-full mr-2"></span>
                                    Active
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                    <span class="text-base text-black-400">Mar 05, 2024</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 text-info-400 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-warning-400 hover:bg-warning-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 text-error-400 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteUser(4)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-accent-100">
                <div class="text-base text-black-400">
                    Showing 1 to 4 of 4 entries
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-2 border border-accent-200 rounded-lg text-base text-black-400 hover:bg-accent-100 transition-colors disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button type="button" class="px-3 py-2 bg-primary-400 text-accent-50 rounded-lg text-base">
                        1
                    </button>
                    <button type="button" class="px-3 py-2 border border-accent-200 rounded-lg text-base text-black-400 hover:bg-accent-100 transition-colors disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    
    // Search and filter
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDepartment = departmentFilter.value;
        const selectedStatus = statusFilter.value;
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const email = row.dataset.email.toLowerCase();
            const userName = row.querySelector('h4').textContent.toLowerCase();
            const department = row.dataset.department;
            const status = row.dataset.status;
            
            const matchesSearch = email.includes(searchTerm) || userName.includes(searchTerm);
            const matchesDepartment = !selectedDepartment || department === selectedDepartment;
            const matchesStatus = !selectedStatus || status === selectedStatus;
            
            if (matchesSearch && matchesDepartment && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    departmentFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Individual checkbox change
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        });
    });
});

// Sort functionality
let sortDirection = {};

function sortTable(column) {
    const tbody = document.getElementById('users-table-body');
    const rows = Array.from(tbody.querySelectorAll('.user-row'));
    
    // Toggle sort direction
    sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch (column) {
            case 'email':
                aValue = a.dataset.email;
                bValue = b.dataset.email;
                break;
            case 'department':
                aValue = a.dataset.department;
                bValue = b.dataset.department;
                break;
            case 'designation':
                aValue = a.dataset.designation;
                bValue = b.dataset.designation;
                break;
            case 'status':
                aValue = a.dataset.status;
                bValue = b.dataset.status;
                break;
            case 'created':
                aValue = new Date(a.dataset.created);
                bValue = new Date(b.dataset.created);
                break;
            default:
                return 0;
        }
        
        if (sortDirection[column] === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });
    
    // Reorder rows
    rows.forEach(row => tbody.appendChild(row));
}

// Delete user function
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        // Here you would normally send a delete request to your backend
        console.log('Deleting user with ID:', userId);
        
        // For demo purposes, just show an alert
        alert('User deleted successfully!');
    }
}
</script>

@endsection 