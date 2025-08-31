@extends('shared::layouts.admin')

@section('title', 'Basic Table - Componentes')

@section('content')
<div class="p-6">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Default Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Default Table</h2>
            </div>
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-accent-100">
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">S.L</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Invoice</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Name</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Issued Date</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-accent-100">
                                <td class="py-3 px-4 text-base text-black-400">01</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526534</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">Kathryn Murphy</td>
                                <td class="py-3 px-4 text-base text-black-400">25 Jan 2025</td>
                                <td class="py-3 px-4 text-base text-black-400">$200.00</td>
                            </tr>
                            <tr class="border-b border-accent-100">
                                <td class="py-3 px-4 text-base text-black-400">02</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#696589</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">Annette Black</td>
                                <td class="py-3 px-4 text-base text-black-400">25 Jan 2025</td>
                                <td class="py-3 px-4 text-base text-black-400">$200.00</td>
                            </tr>
                            <tr class="border-b border-accent-100">
                                <td class="py-3 px-4 text-base text-black-400">03</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#256584</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">Ronald Richards</td>
                                <td class="py-3 px-4 text-base text-black-400">10 Feb 2025</td>
                                <td class="py-3 px-4 text-base text-black-400">$200.00</td>
                            </tr>
                            <tr class="border-b border-accent-100">
                                <td class="py-3 px-4 text-base text-black-400">04</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526587</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">Eleanor Pena</td>
                                <td class="py-3 px-4 text-base text-black-400">10 Feb 2025</td>
                                <td class="py-3 px-4 text-base text-black-400">$150.00</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-base text-black-400">05</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#105986</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">Leslie Alexander</td>
                                <td class="py-3 px-4 text-base text-black-400">15 Mar 2025</td>
                                <td class="py-3 px-4 text-base text-black-400">$150.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bordered Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Bordered Table</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-accent-300 rounded-lg">
                        <thead>
                            <tr class="bg-accent-100">
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold border-r border-accent-200">Invoice</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold border-r border-accent-200">Name</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold border-r border-accent-200">Issued Date</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold border-r border-accent-200">Amount</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-accent-200">
                                <td class="py-3 px-4 border-r border-accent-200">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526534</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">Kathryn Murphy</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">25 Jan 2025</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">$200.00</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">Ver más ></a>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-200">
                                <td class="py-3 px-4 border-r border-accent-200">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#696589</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">Annette Black</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">25 Jan 2025</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">$200.00</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">Ver más ></a>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-200">
                                <td class="py-3 px-4 border-r border-accent-200">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#256584</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">Ronald Richards</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">10 Feb 2025</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">$200.00</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">Ver más ></a>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-200">
                                <td class="py-3 px-4 border-r border-accent-200">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#526587</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">Eleanor Pena</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">10 Feb 2025</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">$150.00</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">Ver más ></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 border-r border-accent-200">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">#105986</a>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">Leslie Alexander</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">15 Mar 2025</td>
                                <td class="py-3 px-4 text-base text-black-400 border-r border-accent-200">$150.00</td>
                                <td class="py-3 px-4">
                                    <a href="javascript:void(0)" class="text-base text-primary-200 hover:text-primary-300">Ver más ></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Striped Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Striped Rows</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-accent-100">
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Items</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Price</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Discount</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Sold</th>
                                <th class="text-center py-3 px-4 text-base text-black-500 font-semibold">Total Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="odd:bg-accent-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center">
                                            <x-solar-bag-outline class="w-6 h-6 text-primary-200" />
                                        </div>
                                        <div>
                                            <h6 class="text-base text-black-500 font-semibold mb-0">Blue t-shirt</h6>
                                            <span class="text-sm text-black-300">Fashion</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">$400.00</td>
                                <td class="py-3 px-4 text-base text-black-400">15%</td>
                                <td class="py-3 px-4 text-base text-black-400">300</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-success-100 text-success-300 px-6 py-1.5 rounded-full text-sm font-semibold">70</span>
                                </td>
                            </tr>
                            <tr class="odd:bg-accent-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-secondary-50 rounded-lg flex items-center justify-center">
                                            <x-solar-streets-map-point-outline class="w-6 h-6 text-secondary-200" />
                                        </div>
                                        <div>
                                            <h6 class="text-base text-black-500 font-semibold mb-0">Nike Air Shoe</h6>
                                            <span class="text-sm text-black-300">Fashion</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">$150.00</td>
                                <td class="py-3 px-4 text-base text-black-400">N/A</td>
                                <td class="py-3 px-4 text-base text-black-400">200</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-success-100 text-success-300 px-6 py-1.5 rounded-full text-sm font-semibold">70</span>
                                </td>
                            </tr>
                            <tr class="odd:bg-accent-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-info-50 rounded-lg flex items-center justify-center">
                                            <x-solar-t-shirt-outline class="w-6 h-6 text-info-200" />
                                        </div>
                                        <div>
                                            <h6 class="text-base text-black-500 font-semibold mb-0">Woman Dresses</h6>
                                            <span class="text-sm text-black-300">Fashion</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">$300.00</td>
                                <td class="py-3 px-4 text-base text-black-400">$50.00</td>
                                <td class="py-3 px-4 text-base text-black-400">1400</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-success-100 text-success-300 px-6 py-1.5 rounded-full text-sm font-semibold">70</span>
                                </td>
                            </tr>
                            <tr class="odd:bg-accent-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-warning-50 rounded-lg flex items-center justify-center">
                                            <x-solar-watch-square-outline class="w-6 h-6 text-warning-200" />
                                        </div>
                                        <div>
                                            <h6 class="text-base text-black-500 font-semibold mb-0">Smart Watch</h6>
                                            <span class="text-sm text-black-300">Technology</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">$400.00</td>
                                <td class="py-3 px-4 text-base text-black-400">$50.00</td>
                                <td class="py-3 px-4 text-base text-black-400">700</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-success-100 text-success-300 px-6 py-1.5 rounded-full text-sm font-semibold">70</span>
                                </td>
                            </tr>
                            <tr class="odd:bg-accent-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-error-50 rounded-lg flex items-center justify-center">
                                            <x-solar-t-shirt-outline class="w-6 h-6 text-error-200" />
                                        </div>
                                        <div>
                                            <h6 class="text-base text-black-500 font-semibold mb-0">Hoodie Rose</h6>
                                            <span class="text-sm text-black-300">Fashion</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">$300.00</td>
                                <td class="py-3 px-4 text-base text-black-400">25%</td>
                                <td class="py-3 px-4 text-base text-black-400">400</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-success-100 text-success-300 px-6 py-1.5 rounded-full text-sm font-semibold">70</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Colored Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Background Colors</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-accent-100">
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Registered On</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Users</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Email</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-primary-50">
                                <td class="py-3 px-4 text-base text-black-400">27 Mar 2025</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/6366f1/ffffff?text=DR" alt="Dianne Russell" class="w-10 h-10 rounded-full object-cover">
                                        <h6 class="text-base text-black-500 font-semibold">Dianne Russell</h6>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">random@gmail.com</td>
                                <td class="py-3 px-4 text-base text-black-400">Free</td>
                            </tr>
                            <tr class="bg-success-50">
                                <td class="py-3 px-4 text-base text-black-400">27 Mar 2025</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/10b981/ffffff?text=WW" alt="Wade Warren" class="w-10 h-10 rounded-full object-cover">
                                        <h6 class="text-base text-black-500 font-semibold">Wade Warren</h6>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">random@gmail.com</td>
                                <td class="py-3 px-4 text-base text-black-400">Basic</td>
                            </tr>
                            <tr class="bg-info-50">
                                <td class="py-3 px-4 text-base text-black-400">27 Mar 2025</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/3b82f6/ffffff?text=AF" alt="Albert Flores" class="w-10 h-10 rounded-full object-cover">
                                        <h6 class="text-base text-black-500 font-semibold">Albert Flores</h6>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">random@gmail.com</td>
                                <td class="py-3 px-4 text-base text-black-400">Standard</td>
                            </tr>
                            <tr class="bg-warning-50">
                                <td class="py-3 px-4 text-base text-black-400">27 Mar 2025</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/f59e0b/ffffff?text=BC" alt="Bessie Cooper" class="w-10 h-10 rounded-full object-cover">
                                        <h6 class="text-base text-black-500 font-semibold">Bessie Cooper</h6>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">random@gmail.com</td>
                                <td class="py-3 px-4 text-base text-black-400">Business</td>
                            </tr>
                            <tr class="bg-error-50">
                                <td class="py-3 px-4 text-base text-black-400">27 Mar 2025</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/ef4444/ffffff?text=AM" alt="Arlene McCoy" class="w-10 h-10 rounded-full object-cover">
                                        <h6 class="text-base text-black-500 font-semibold">Arlene McCoy</h6>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-base text-black-400">random@gmail.com</td>
                                <td class="py-3 px-4 text-base text-black-400">Enterprise</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Table Full Width -->
    <div class="mt-6">
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Card Table</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-accent-100">
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Users</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Invoice</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Items</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Qty</th>
                                <th class="text-left py-3 px-4 text-base text-black-500 font-semibold">Amount</th>
                                <th class="text-center py-3 px-4 text-base text-black-500 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/6366f1/ffffff?text=DR" alt="Dianne Russell" class="w-10 h-10 rounded-full object-cover">
                                        <span class="text-base text-black-500 font-semibold">Dianne Russell</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-base text-primary-200">#6352148</td>
                                <td class="py-4 px-4 text-base text-black-400">iPhone 14 max</td>
                                <td class="py-4 px-4 text-base text-black-400">2</td>
                                <td class="py-4 px-4 text-base text-black-400">$5,000.00</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-success-200 text-accent-50 px-6 py-1.5 rounded-full text-sm font-semibold">Paid</span>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/10b981/ffffff?text=WW" alt="Wade Warren" class="w-10 h-10 rounded-full object-cover">
                                        <span class="text-base text-black-500 font-semibold">Wade Warren</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-base text-primary-200">#6352148</td>
                                <td class="py-4 px-4 text-base text-black-400">Laptop HPH</td>
                                <td class="py-4 px-4 text-base text-black-400">3</td>
                                <td class="py-4 px-4 text-base text-black-400">$1,000.00</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-warning-200 text-black-500 px-6 py-1.5 rounded-full text-sm font-semibold">Pending</span>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/3b82f6/ffffff?text=AF" alt="Albert Flores" class="w-10 h-10 rounded-full object-cover">
                                        <span class="text-base text-black-500 font-semibold">Albert Flores</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-base text-primary-200">#6352148</td>
                                <td class="py-4 px-4 text-base text-black-400">Smart Watch</td>
                                <td class="py-4 px-4 text-base text-black-400">7</td>
                                <td class="py-4 px-4 text-base text-black-400">$1,000.00</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-info-200 text-accent-50 px-6 py-1.5 rounded-full text-sm font-semibold">Shipped</span>
                                </td>
                            </tr>
                            <tr class="border-b border-accent-100 hover:bg-accent-100 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/f59e0b/ffffff?text=BC" alt="Bessie Cooper" class="w-10 h-10 rounded-full object-cover">
                                        <span class="text-base text-black-500 font-semibold">Bessie Cooper</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-base text-primary-200">#6352148</td>
                                <td class="py-4 px-4 text-base text-black-400">Nike Air Shoe</td>
                                <td class="py-4 px-4 text-base text-black-400">1</td>
                                <td class="py-4 px-4 text-base text-black-400">$3,000.00</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-error-200 text-accent-50 px-6 py-1.5 rounded-full text-sm font-semibold">Canceled</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-accent-100 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://via.placeholder.com/40x40/ef4444/ffffff?text=AM" alt="Arlene McCoy" class="w-10 h-10 rounded-full object-cover">
                                        <span class="text-base text-black-500 font-semibold">Arlene McCoy</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-base text-primary-200">#6352148</td>
                                <td class="py-4 px-4 text-base text-black-400">New Headphone</td>
                                <td class="py-4 px-4 text-base text-black-400">5</td>
                                <td class="py-4 px-4 text-base text-black-400">$4,000.00</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-error-200 text-accent-50 px-6 py-1.5 rounded-full text-sm font-semibold">Canceled</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 