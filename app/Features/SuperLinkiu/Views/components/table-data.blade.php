@extends('shared::layouts.admin')

@section('title', 'Data Table - Componentes')

@section('content')
<div>

    <div class="card">
        <div class="card-header">
            <h2 class="title-card">Advanced DataTable</h2>
        </div>
        <div class="card-body">
            <!-- Filters and Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <x-solar-magnifer-outline class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-black-300" />
                        <input type="text" id="search-table" 
                               class="w-full pl-10 pr-4 py-2.5 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                               placeholder="Buscar facturas...">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="min-w-0 sm:w-40">
                    <select id="status-filter" 
                            class="w-full px-3 py-2.5 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                
                <!-- Date Filter -->
                <div class="min-w-0 sm:w-40">
                    <select id="date-filter" 
                            class="w-full px-3 py-2.5 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="button" id="export-btn" 
                            class="px-4 py-2.5 border border-primary-200 text-primary-200 text-base rounded-lg hover:bg-primary-50 transition-colors duration-200 flex items-center gap-2">
                        <x-solar-download-outline class="w-5 h-5" />
                        Export
                    </button>
                    <button type="button" id="add-btn" 
                            class="px-4 py-2.5 bg-primary-200 text-white-50 text-base rounded-lg hover:bg-primary-300 transition-colors duration-200 flex items-center gap-2">
                        <x-solar-add-circle-outline class="w-5 h-5" />
                        Add New
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div id="bulk-actions" class="hidden mb-4 p-4 bg-primary-50 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-base text-primary-300" id="selected-count">0 items selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="bulk-delete" 
                            class="px-4 py-2 bg-error-200 text-error-50 text-base rounded-lg hover:bg-error-50 transition-colors duration-200">
                        Delete Selected
                    </button>
                    <button type="button" id="bulk-archive" 
                            class="px-4 py-2 bg-warning-200 text-black-300 text-base rounded-lg hover:bg-warning-50 transition-colors duration-200">
                        Archive Selected
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse" id="data-table">
                    <thead>
                        <tr class="border-b border-white-100">
                            <th class="text-left py-3 px-4 text-base text-black-400 font-medium">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="select-all" 
                                           class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded">
                                    <span>S.L</span>
                                </div>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-400 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-300 transition-colors" onclick="sortTable('invoice')">
                                    Invoice
                                    <x-solar-sort-from-top-to-bottom-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-400 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-300 transition-colors" onclick="sortTable('name')">
                                    Name
                                    <x-solar-sort-from-top-to-bottom-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-400 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-300 transition-colors" onclick="sortTable('date')">
                                    Issued Date
                                    <x-solar-sort-from-top-to-bottom-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-400 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-300 transition-colors" onclick="sortTable('amount')">
                                    Amount
                                    <x-solar-sort-from-top-to-bottom-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-left py-3 px-4 text-base text-black-400 font-semibold">
                                <button class="flex items-center gap-1 hover:text-primary-300 transition-colors" onclick="sortTable('status')">
                                    Status
                                    <x-solar-sort-from-top-to-bottom-outline class="w-4 h-4" />
                                </button>
                            </th>
                            <th class="text-center py-3 px-4 text-base text-black-400 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Row 1 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#526534" 
                            data-name="Kathryn Murphy" 
                            data-date="2025-01-25" 
                            data-amount="200.00" 
                            data-status="paid">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">01</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526534</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/6366f1/ffffff?text=KM" alt="Kathryn Murphy" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Kathryn Murphy</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">25 Jan 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$200.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-success-200 text-white-50 px-6 py-1.5 rounded-full text-sm font-semibold">Paid</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 2 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#696589" 
                            data-name="Annette Black" 
                            data-date="2025-01-25" 
                            data-amount="200.00" 
                            data-status="paid">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">02</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#696589</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/ec4899/ffffff?text=AB" alt="Annette Black" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Annette Black</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">25 Jan 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$200.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-success-200 text-white-50 px-6 py-1.5 rounded-full text-sm font-semibold">Paid</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 3 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#256584" 
                            data-name="Ronald Richards" 
                            data-date="2025-02-10" 
                            data-amount="200.00" 
                            data-status="paid">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">03</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#256584</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/10b981/ffffff?text=RR" alt="Ronald Richards" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Ronald Richards</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">10 Feb 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$200.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-success-200 text-white-50 px-6 py-1.5 rounded-full text-sm font-semibold">Paid</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 4 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#526587" 
                            data-name="Eleanor Pena" 
                            data-date="2025-02-10" 
                            data-amount="150.00" 
                            data-status="paid">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">04</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526587</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/3b82f6/ffffff?text=EP" alt="Eleanor Pena" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Eleanor Pena</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">10 Feb 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$150.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-success-200 text-white-50 px-6 py-1.5 rounded-full text-sm font-semibold">Paid</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 5 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#105986" 
                            data-name="Leslie Alexander" 
                            data-date="2025-03-15" 
                            data-amount="150.00" 
                            data-status="pending">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">05</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="body-base text-primary-400 hover:text-primary-500">#105986</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/f59e0b/ffffff?text=LA" alt="Leslie Alexander" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Leslie Alexander</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">15 Mar 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$150.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-warning-200 text-black-300 px-6 py-1.5 rounded-full text-sm font-semibold">Pending</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 6 -->
                        <tr class="border-b border-white-100 hover:bg-white-100 transition-colors table-row" 
                            data-invoice="#526589" 
                            data-name="Albert Flores" 
                            data-date="2025-03-15" 
                            data-amount="150.00" 
                            data-status="overdue">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="w-4 h-4 text-primary-200 border-white-200 focus:ring-primary-200 rounded row-checkbox">
                                    <span class="text-base text-black-400">06</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526589</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://via.placeholder.com/40x40/ef4444/ffffff?text=AF" alt="Albert Flores" class="w-10 h-10 rounded-full object-cover">
                                    <h6 class="text-base text-black-400 font-semibold">Albert Flores</h6>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-base text-black-400">15 Mar 2025</td>
                            <td class="py-4 px-4 text-base text-black-400">$150.00</td>
                            <td class="py-4 px-4">
                                <span class="bg-error-200 text-white-50 px-6 py-1.5 rounded-full text-sm font-semibold">Overdue</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="View">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Edit">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </button>
                                    <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Delete" onclick="deleteRow(this)">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-white-100">
                <div class="flex items-center gap-2">
                    <span class="text-base text-black-400">Show</span>
                    <select id="per-page" class="px-3 py-2 border border-white-200 rounded-lg text-base text-black-400 focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span class="text-base text-black-400">entries</span>
                </div>
                
                <div class="text-base text-black-400">
                    Showing <span id="showing-start">1</span> to <span id="showing-end">6</span> of <span id="total-entries">6</span> entries
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" id="prev-btn" class="px-3 py-2 border border-white-200 rounded-lg text-base text-black-400 hover:bg-white-100 transition-colors disabled:opacity-50" disabled>
                        <x-solar-alt-arrow-left-outline class="w-4 h-4" />
                    </button>
                    <button type="button" class="px-3 py-2 bg-primary-200 text-white-50 rounded-lg text-base">
                        1
                    </button>
                    <button type="button" id="next-btn" class="px-3 py-2 border border-white-200 rounded-lg body-base text-black-400 hover:bg-white-100 transition-colors disabled:opacity-50" disabled>
                        <x-solar-alt-arrow-right-outline class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-table');
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    // Search and filter
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedDate = dateFilter.value;
        const rows = document.querySelectorAll('.table-row');
        
        rows.forEach(row => {
            const invoice = row.dataset.invoice.toLowerCase();
            const name = row.dataset.name.toLowerCase();
            const status = row.dataset.status;
            const date = row.dataset.date;
            
            const matchesSearch = invoice.includes(searchTerm) || name.includes(searchTerm);
            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesDate = !selectedDate || matchesDateFilter(date, selectedDate);
            
            if (matchesSearch && matchesStatus && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        updatePagination();
    }
    
    function matchesDateFilter(date, filter) {
        const rowDate = new Date(date);
        const today = new Date();
        
        switch (filter) {
            case 'today':
                return rowDate.toDateString() === today.toDateString();
            case 'week':
                const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                return rowDate >= weekAgo;
            case 'month':
                return rowDate.getMonth() === today.getMonth() && rowDate.getFullYear() === today.getFullYear();
            case 'year':
                return rowDate.getFullYear() === today.getFullYear();
            default:
                return true;
        }
    }
    
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        const visibleCheckboxes = Array.from(rowCheckboxes).filter(cb => 
            cb.closest('.table-row').style.display !== 'none'
        );
        
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        
        updateBulkActions();
    });
    
    // Individual checkbox change
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const visibleCheckboxes = Array.from(rowCheckboxes).filter(cb => 
                cb.closest('.table-row').style.display !== 'none'
            );
            
            const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
            const allChecked = checkedCount === visibleCheckboxes.length && visibleCheckboxes.length > 0;
            const someChecked = checkedCount > 0;
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            
            updateBulkActions();
        });
    });
    
    function updateBulkActions() {
        const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
        
        if (checkedCount > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = `${checkedCount} item${checkedCount > 1 ? 's' : ''} selected`;
        } else {
            bulkActions.classList.add('hidden');
        }
    }
    
    function updatePagination() {
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');
        const totalEntries = document.getElementById('total-entries');
        const showingStart = document.getElementById('showing-start');
        const showingEnd = document.getElementById('showing-end');
        
        totalEntries.textContent = visibleRows.length;
        showingStart.textContent = visibleRows.length > 0 ? '1' : '0';
        showingEnd.textContent = visibleRows.length;
    }
    
    // Export functionality
    document.getElementById('export-btn').addEventListener('click', function() {
        alert('Export functionality would be implemented here');
    });
    
    // Add new functionality
    document.getElementById('add-btn').addEventListener('click', function() {
        alert('Add new item functionality would be implemented here');
    });
    
    // Bulk actions
    document.getElementById('bulk-delete').addEventListener('click', function() {
        if (confirm('Are you sure you want to delete the selected items?')) {
            const checkedRows = Array.from(rowCheckboxes).filter(cb => cb.checked);
            checkedRows.forEach(cb => {
                cb.closest('.table-row').remove();
            });
            updateBulkActions();
            updatePagination();
        }
    });
    
    document.getElementById('bulk-archive').addEventListener('click', function() {
        if (confirm('Are you sure you want to archive the selected items?')) {
            alert('Archive functionality would be implemented here');
        }
    });
});

// Sort functionality
let sortDirection = {};

function sortTable(column) {
    const tbody = document.getElementById('table-body');
    const rows = Array.from(tbody.querySelectorAll('.table-row'));
    
    // Toggle sort direction
    sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch (column) {
            case 'invoice':
                aValue = a.dataset.invoice;
                bValue = b.dataset.invoice;
                break;
            case 'name':
                aValue = a.dataset.name;
                bValue = b.dataset.name;
                break;
            case 'date':
                aValue = new Date(a.dataset.date);
                bValue = new Date(b.dataset.date);
                break;
            case 'amount':
                aValue = parseFloat(a.dataset.amount);
                bValue = parseFloat(b.dataset.amount);
                break;
            case 'status':
                aValue = a.dataset.status;
                bValue = b.dataset.status;
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

// Delete row function
function deleteRow(button) {
    if (confirm('Are you sure you want to delete this item?')) {
        const row = button.closest('.table-row');
        row.remove();
        
        // Update pagination after deletion
        const event = new Event('change');
        document.getElementById('search-table').dispatchEvent(event);
    }
}
</script>

@endsection 