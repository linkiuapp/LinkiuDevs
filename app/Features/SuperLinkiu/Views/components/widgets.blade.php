@extends('shared::layouts.admin')

@section('title', 'Widgets')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="title-card">Metrics</h2>
    </div>
    <div class="card-body p-6 flex flex-col gap-6">
        <!-- AI Widgets Start -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-6">
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-r from-primary-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-black-400 mb-1">Total Users</p>
                            <h6 class="text-xl font-black mb-0 text-black-500">20,000</h6>
                        </div>
                        <div class="w-[48px] h-[48px] bg-info-300 rounded-full flex justify-center items-center">
                            <x-solar-users-group-two-rounded-outline class="text-accent-50 text-xl p-3" />
                        </div>
                    </div>
                    <p class="font-medium text-sm text-black-300 mt-3 mb-0 flex items-center gap-2">
                        <span class="badge-soft-success flex items-center gap-1">
                            <x-solar-arrow-up-outline class="text-xs" />
                            +4000
                        </span>
                        Last 30 days users
                    </p>
                </div>
            </div><!-- card end -->
            
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-r from-secondary-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-black-400 mb-1">Total Subscription</p>
                            <h6 class="text-xl font-black mb-0 text-black-500">15,000</h6>
                        </div>
                        <div class="w-[48px] h-[48px] bg-secondary-300 rounded-full flex justify-center items-center">
                            <x-solar-medal-star-outline class="text-accent-50 text-xl p-3" />
                        </div>
                    </div>
                    <p class="font-medium text-sm text-black-300 mt-3 mb-0 flex items-center gap-2">
                        <span class="badge-soft-error flex items-center gap-1">
                            <x-solar-arrow-down-outline class="text-xs" />
                            -800
                        </span>
                        Last 30 days subscription
                    </p>
                </div>
            </div><!-- card end -->
            
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-r from-info-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-black-400 mb-1">Total Free Users</p>
                            <h6 class="text-xl font-black mb-0 text-black-500">5,000</h6>
                        </div>
                        <div class="w-[48px] h-[48px] bg-info-300 rounded-full flex justify-center items-center">
                            <x-solar-user-outline class="text-accent-50 text-xl p-3" />
                        </div>
                    </div>
                    <p class="font-medium text-sm text-black-300 mt-3 mb-0 flex items-center gap-2">
                        <span class="badge-soft-success flex items-center gap-1">
                            <x-solar-arrow-up-outline class="text-xs" />
                            +200
                        </span>
                        Last 30 days users
                    </p>
                </div>
            </div><!-- card end -->
            
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-r from-success-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-black-400 mb-1">Total Income</p>
                            <h6 class="text-xl font-black mb-0 text-black-500">$42,000</h6>
                        </div>
                        <div class="w-[48px] h-[48px] bg-success-300 rounded-full flex justify-center items-center">
                            <x-solar-wallet-outline class="text-accent-50 text-xl p-3" />
                        </div>
                    </div>
                    <p class="font-medium text-sm text-black-300 mt-3 mb-0 flex items-center gap-2">
                        <span class="badge-soft-success flex items-center gap-1">
                            <x-solar-arrow-up-outline class="text-xs" />
                            +$20,000
                        </span>
                        Last 30 days income
                    </p>
                </div>
            </div><!-- card end -->
            
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-r from-error-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-black-400 mb-1">Total Expense</p>
                            <h6 class="text-xl font-black mb-0 text-black-500">$30,000</h6>
                        </div>
                        <div class="w-[48px] h-[48px] bg-error-300 rounded-full flex justify-center items-center">
                            <x-solar-bill-list-outline class="text-accent-50 text-xl p-3" />
                        </div>
                    </div>
                    <p class="font-medium text-sm text-black-300 mt-3 mb-0 flex items-center gap-2">
                        <span class="badge-soft-success flex items-center gap-1">
                            <x-solar-arrow-up-outline class="text-xs" />
                            +$5,000
                        </span>
                        Last 30 days expense
                    </p>
                </div>
            </div><!-- card end -->
        </div>
        <!-- AI Widgets End -->
        
        <!-- CRM Widgets Start -->
        <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-4 gap-6">
            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-l from-primary-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[48px] h-[48px] bg-primary-300 shrink-0 text-accent-50 flex justify-center items-center rounded-full text-2xl">
                                <x-solar-user-plus-outline class="w-5 h-5" />
                            </span>
                            <div>
                                <span class="mb-2 font-semibold text-black-300 text-sm">New Users</span>
                                <h6 class="text-xl font-black text-black-500">15,000</h6>
                            </div>
                        </div>
                        <!-- Sparkline Chart -->
                        <div class="w-20 h-10">
                            <svg class="w-full h-full" viewBox="0 0 80 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sparkline1" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#6b46c1;stop-opacity:0.3" />
                                        <stop offset="100%" style="stop-color:#6b46c1;stop-opacity:0.05" />
                                    </linearGradient>
                                </defs>
                                <path d="M0,35 L10,30 L20,25 L30,20 L40,15 L50,12 L60,8 L70,5 L80,3 L80,40 L0,40 Z" fill="url(#sparkline1)" />
                                <path d="M0,35 L10,30 L20,25 L30,20 L40,15 L50,12 L60,8 L70,5 L80,3" stroke="#6b46c1" stroke-width="2" fill="none" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-4 mb-0">Increase by <span class="badge-success">+200</span> this week</p>
                </div>
            </div>

            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-l from-success-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[48px] h-[48px] bg-success-300 shrink-0 text-accent-50 flex justify-center items-center rounded-full text-2xl">
                                <x-solar-users-group-two-rounded-outline class="w-5 h-5" />
                            </span>
                            <div>
                                <span class="mb-2 font-semibold text-black-300 text-sm">Active Users</span>
                                <h6 class="text-xl font-black text-black-500">8,000</h6>
                            </div>
                        </div>
                        <!-- Sparkline Chart -->
                        <div class="w-20 h-10">
                            <svg class="w-full h-full" viewBox="0 0 80 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sparkline2" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#10b981;stop-opacity:0.4" />
                                        <stop offset="100%" style="stop-color:#10b981;stop-opacity:0.05" />
                                    </linearGradient>
                                </defs>
                                <path d="M0,30 L10,25 L20,28 L30,22 L40,18 L50,20 L60,15 L70,10 L80,8 L80,40 L0,40 Z" fill="url(#sparkline2)" />
                                <path d="M0,30 L10,25 L20,28 L30,22 L40,18 L50,20 L60,15 L70,10 L80,8" stroke="#10b981" stroke-width="2" fill="none" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-4 mb-0">Increase by <span class="badge-success">+200</span> this week</p>
                </div>
            </div>

            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-l from-warning-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[48px] h-[48px] bg-warning-300 text-black-500 shrink-0 flex justify-center items-center rounded-full text-2xl">
                                <x-solar-tag-price-outline class="w-5 h-5" />
                            </span>
                            <div>
                                <span class="mb-2 font-semibold text-black-300 text-sm">Total Sales</span>
                                <h6 class="text-xl font-black text-black-500">$5,00</h6>
                            </div>
                        </div>
                        <!-- Sparkline Chart -->
                        <div class="w-20 h-10">
                            <svg class="w-full h-full" viewBox="0 0 80 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sparkline3" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:0.4" />
                                        <stop offset="100%" style="stop-color:#f59e0b;stop-opacity:0.05" />
                                    </linearGradient>
                                </defs>
                                <path d="M0,25 L10,20 L20,30 L30,18 L40,22 L50,28 L60,25 L70,20 L80,15 L80,40 L0,40 Z" fill="url(#sparkline3)" />
                                <path d="M0,25 L10,20 L20,30 L30,18 L40,22 L50,28 L60,25 L70,20 L80,15" stroke="#f59e0b" stroke-width="2" fill="none" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-4 mb-0">Decrease by <span class="badge-error">-$10k</span> this week</p>
                </div>
            </div>

            <div class="border border-accent-100 rounded-lg h-full bg-gradient-to-l from-secondary-100 to-accent-100">
                <div class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[48px] h-[48px] bg-secondary-300 text-accent-50 shrink-0 flex justify-center items-center rounded-full text-2xl">
                                <x-solar-chart-outline class="w-5 h-5" />
                            </span>
                            <div>
                                <span class="mb-2 font-semibold text-black-300 text-sm">Conversion</span>
                                <h6 class="text-xl font-black text-black-500">25%</h6>
                            </div>
                        </div>
                        <!-- Sparkline Chart -->
                        <div class="w-20 h-10">
                            <svg class="w-full h-full" viewBox="0 0 80 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sparkline4" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#8b5cf6;stop-opacity:0.4" />
                                        <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:0.05" />
                                    </linearGradient>
                                </defs>
                                <path d="M0,32 L10,28 L20,24 L30,20 L40,16 L50,12 L60,8 L70,6 L80,4 L80,40 L0,40 Z" fill="url(#sparkline4)" />
                                <path d="M0,32 L10,28 L20,24 L30,20 L40,16 L50,12 L60,8 L70,6 L80,4" stroke="#8b5cf6" stroke-width="2" fill="none" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-4 mb-0">Increase by <span class="badge-success">+5%</span> this week</p>
                </div>
            </div>
        </div>
        <!-- CRM Widgets End -->
        
        <!-- Ecommerce Widgets Start -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 h-full flex flex-col border border-accent-300 last:border-0 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-1 mb-0.5">
                    <div>
                        <span class="w-[48px] h-[48px] text-primary-400 bg-primary-50 border border-primary-100 shrink-0 flex justify-center items-center rounded-lg text-2xl mb-4">
                            <x-solar-box-outline class="w-5 h-5" />
                        </span>
                        <span class="mb-1 font-semibold text-black-300 text-sm">Total Products</span>
                        <h6 class="text-xl font-black text-black-500 mt-2 mb-px">300</h6>
                    </div>
                </div>
                <p class="text-sm mb-0 mt-3">Increase by <span class="badge-success">+200</span> this week</p>
            </div>
            
            <div class="p-6 h-full flex flex-col border border-accent-300 last:border-0 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-1 mb-0.5">
                    <div>
                        <span class="w-[48px] h-[48px] text-warning-400 bg-warning-50 border border-warning-100 shrink-0 flex justify-center items-center rounded-lg text-2xl mb-4">
                            <x-solar-users-group-rounded-outline class="w-5 h-5" />
                        </span>
                        <span class="mb-1 font-semibold text-black-300 text-sm">Total Customer</span>
                        <h6 class="text-xl font-black text-black-500 mt-2 mb-px">50,000</h6>
                    </div>
                </div>
                <p class="text-sm mb-0 mt-3">Decrease by <span class="badge-error">-5k</span> this week</p>
            </div>
            
            <div class="p-6 h-full flex flex-col border border-accent-300 last:border-0 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-1 mb-0.5">
                    <div>
                        <span class="w-[48px] h-[48px] text-secondary-300 bg-secondary-50 border border-secondary-100 shrink-0 flex justify-center items-center rounded-lg text-2xl mb-4">
                            <x-solar-cart-outline class="w-5 h-5" />
                        </span>
                        <span class="mb-1 font-semibold text-black-300 text-sm">Total Orders</span>
                        <h6 class="text-xl font-black text-black-500 mt-2 mb-px">1,400</h6>
                    </div>
                </div>
                <p class="text-sm mb-0 mt-3">Increase by <span class="badge-success">+1k</span> this week</p>
            </div>
            
            <div class="p-6 h-full flex flex-col border border-accent-300 last:border-0 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-1 mb-0.5">
                    <div>
                        <span class="w-[48px] h-[48px] text-info-400 bg-info-50 border border-info-100 shrink-0 flex justify-center items-center rounded-lg text-2xl mb-4">
                            <x-solar-tag-price-outline class="w-5 h-5" />
                        </span>
                        <span class="mb-1 font-semibold text-black-300 text-sm">Total Sales</span>
                        <h6 class="text-xl font-black text-black-500 mt-2 mb-px">$25,00,000</h6>
                    </div>
                </div>
                <p class="text-sm mb-0 mt-3">Increase by <span class="badge-success">+$10k</span> this week</p>
            </div>
        </div>
        <!-- Ecommerce Widgets End -->
    </div>
</div>
        
</div>

@endsection 