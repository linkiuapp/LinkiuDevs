@extends('shared::layouts.admin')

@section('title', 'Pricing Cards')

@section('content')
<div class="p-6">
    <!-- Pricing Plan with Tabs -->
    <div class="card">
        <div class="card-header">
            <h2 class="title-card">Pricing Plan with Tabs</h2>
        </div>
        <div class="card-body">
            <div class="text-center mb-12">
                <h4 class="text-xl font-black text-black-500 mb-4">Pricing Plan</h4>
                <p class="text-sm text-black-300 mb-0">No contracts. No surprise fees.</p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex justify-center mb-12">
                <div class="bg-accent-200 rounded-full p-1 flex">
                    <button class="px-6 py-2 text-sm font-semibold rounded-full bg-primary-300 text-accent-50" type="button">
                        Monthly
                    </button>
                    <button class="px-6 py-2 text-sm font-semibold rounded-full text-black-400 hover:text-primary-300" type="button">
                        Yearly
                    </button>
                </div>
            </div>

            <!-- Pricing Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Basic Plan -->
                <div class="bg-gradient-to-b from-secondary-50 to-accent-50 rounded-3xl p-6 lg:p-8 border border-accent-100">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-secondary-200 border border-accent-200">
                            <x-solar-user-outline class="w-8 h-8 text-secondary-50" />
                        </span>
                        <div>
                            <span class="text-sm font-semibold text-black-300">For individuals</span>
                            <h6 class="text-lg font-black text-black-500 mb-0">Basic</h6>
                        </div>
                    </div>
                    <p class="text-sm text-black-300 mb-7">Perfect for individuals getting started with our platform.</p>
                    <h3 class="text-xl font-black text-black-500 mb-6">$99 <span class="text-sm font-semibold text-black-300">/monthly</span></h3>
                    <span class="text-sm font-semibold text-black-500 mb-5 block">What's included</span>
                    <ul class="space-y-4 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-secondary-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">All analytics features</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-secondary-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Up to 250,000 tracked visits</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-secondary-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Normal support</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-secondary-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Up to 3 team members</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-secondary-200 hover:bg-secondary-300 text-accent-50 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Get started
                    </button>
                </div>

                <!-- Pro Plan (Popular) -->
                <div class="bg-primary-200 rounded-3xl p-6 lg:p-8 text-accent-50 relative transform lg:scale-105 lg:-mt-4 border border-primary-300">
                    <span class="bg-accent-50 bg-opacity-25 text-accent-50 rounded-se-3xl rounded-es-3xl py-2 px-6 text-sm font-medium absolute right-0 top-0">
                        Popular
                    </span>
                    <div class="flex items-center gap-4 mb-6 mt-8">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-primary-300">
                            <x-solar-crown-outline class="w-8 h-8 text-accent-50" />
                        </span>
                        <div>
                            <span class="text-sm font-medium text-accent-50">For startups</span>
                            <h6 class="text-lg font-black text-accent-50 mb-0">Pro</h6>
                        </div>
                    </div>
                    <p class="text-sm text-accent-50 mb-7">Ideal for growing businesses that need more power.</p>
                    <h3 class="text-lg font-black text-accent-50 mb-6">$199 <span class="text-sm font-medium text-accent-50">/monthly</span></h3>
                    <span class="text-sm font-medium text-accent-50 mb-5 block">What's included</span>
                    <ul class="space-y-4 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-200" />
                            </span>
                            <span class="text-sm text-accent-50">All analytics features</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-200" />
                            </span>
                            <span class="text-sm text-accent-50">Up to 1,000,000 tracked visits</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-200" />
                            </span>
                            <span class="text-sm text-accent-50">Priority support</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-200" />
                            </span>
                            <span class="text-sm text-accent-50">Up to 10 team members</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-accent-50 hover:bg-accent-100 text-primary-200 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Get started
                    </button>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-gradient-to-b from-success-50 to-accent-50 rounded-3xl p-6 lg:p-8 border border-accent-100">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-success-200 border border-accent-200">
                            <x-solar-buildings-outline class="w-8 h-8 text-accent-50" />
                        </span>
                        <div>
                            <span class="text-sm font-medium text-black-300">For big companies</span>
                            <h6 class="text-lg font-black text-black-500 mb-0">Enterprise</h6>
                        </div>
                    </div>
                    <p class="text-sm text-black-300 mb-7">Advanced features for large organizations and teams.</p>
                    <h3 class="text-lg font-black text-black-500 mb-6">$399 <span class="text-sm font-medium text-black-300">/monthly</span></h3>
                    <span class="text-sm font-medium text-black-500 mb-5 block">What's included</span>
                    <ul class="space-y-4 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-success-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">All analytics features</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-success-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Unlimited tracked visits</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-success-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">24/7 dedicated support</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-success-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Unlimited team members</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-success-200 hover:bg-success-300 text-accent-50 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Get started
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Pricing with Toggle -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Simple Pricing with Toggle</h2>
        </div>
        <div class="card-body">
            <div class="text-center mb-12">
                <h4 class="text-xl font-black text-black-500 mb-4">Simple, Transparent Pricing</h4>
                <p class="text-sm text-black-300 mb-0">Choose the plan that works best for your needs.</p>
            </div>

            <!-- Toggle Switch -->
            <div class="flex justify-center mb-12">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-black-400">Monthly</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-black-400 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-accent-50 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                    </label>
                    <span class="text-sm font-medium text-black-400">Annually</span>
                </div>
            </div>

            <!-- Simple Pricing Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Starter -->
                <div class="bg-accent-50 rounded-3xl p-8 border border-accent-300">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-info-200 border border-info-100">
                            <x-solar-rocket-outline class="w-8 h-8 text-accent-50" />
                        </span>
                        <div>
                            <span class="text-sm font-medium text-black-300">For individuals</span>
                            <h6 class="text-lg font-black text-black-500 mb-0">Starter</h6>
                        </div>
                    </div>
                    <p class="text-sm text-black-300 mb-7">Great for personal projects and small websites.</p>
                    <h3 class="text-lg font-black text-black-500 mb-6">$49 <span class="text-sm font-medium text-black-300">/monthly</span></h3>
                    <span class="text-sm font-medium text-black-500 mb-5 block">What's included</span>
                    <ul class="space-y-3 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-info-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">5 projects</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-info-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">10GB storage</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-info-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Email support</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-info-200 hover:bg-info-300 text-accent-50 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Get started
                    </button>
                </div>

                <!-- Professional -->
                <div class="bg-primary-300 rounded-3xl p-8 text-accent-50 relative">
                    <span class="bg-accent-50 bg-opacity-25 text-accent-50 rounded-se-3xl rounded-es-3xl py-2 px-6 text-sm font-medium absolute right-0 top-0">
                        Popular
                    </span>
                    <div class="flex items-center gap-4 mb-6 mt-6">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-primary-200">
                            <x-solar-star-outline class="w-8 h-8 text-accent-50" />
                        </span>
                        <div>
                            <span class="text-sm font-medium text-accent-50">For teams</span>
                            <h6 class="text-lg font-black text-accent-50 mb-0">Professional</h6>
                        </div>
                    </div>
                    <p class="text-sm text-accent-50 mb-7">Perfect for growing teams and businesses.</p>
                    <h3 class="text-lg font-black text-accent-50 mb-6">$149 <span class="text-sm font-medium text-accent-50">/monthly</span></h3>
                    <span class="text-sm font-medium text-accent-50 mb-5 block">What's included</span>
                    <ul class="space-y-3 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-300" />
                            </span>
                            <span class="text-sm text-accent-50">50 projects</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-300" />
                            </span>
                            <span class="text-sm text-accent-50">100GB storage</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-accent-50 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-primary-300" />
                            </span>
                            <span class="text-sm text-accent-50">Priority support</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-accent-50 hover:bg-accent-100 text-primary-300 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Get started
                    </button>
                </div>

                <!-- Enterprise -->
                <div class="bg-accent-50 rounded-3xl p-8 border border-accent-300">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="w-[48px] h-[48px] flex justify-center items-center rounded-2xl bg-warning-200 border border-warning-100">
                            <x-solar-shield-star-outline class="w-8 h-8 text-accent-50" />
                        </span>
                        <div>
                            <span class="text-sm font-medium text-black-300">For enterprises</span>
                            <h6 class="text-lg font-black text-black-500 mb-0">Enterprise</h6>
                        </div>
                    </div>
                    <p class="text-sm text-black-300 mb-7">Custom solutions for large organizations.</p>
                    <h3 class="text-lg font-black text-black-500 mb-6">Custom <span class="text-sm font-medium text-black-300">pricing</span></h3>
                    <span class="text-sm font-medium text-black-500 mb-5 block">What's included</span>
                    <ul class="space-y-3 mb-7">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-warning-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Unlimited projects</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-warning-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">Unlimited storage</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 flex justify-center items-center bg-warning-200 rounded-full">
                                <x-solar-check-circle-outline class="w-4 h-4 text-accent-50" />
                            </span>
                            <span class="text-sm text-black-300">24/7 support</span>
                        </li>
                    </ul>
                    <button type="button" class="btn bg-warning-200 hover:bg-warning-300 text-black-500 rounded-lg px-6 py-3 w-full text-base font-semibold">
                        Contact sales
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="card mt-12">
        <div class="card-header">
            <h2 class="title-card">Feature Comparison</h2>
        </div>
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-accent-100">
                            <th class="text-left py-4 px-4 text-base font-semibold text-black-400">Features</th>
                            <th class="text-center py-4 px-4 text-base font-semibold text-black-400">Basic</th>
                            <th class="text-center py-4 px-4 text-base font-semibold text-black-400">Pro</th>
                            <th class="text-center py-4 px-4 text-base font-semibold text-black-400">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-accent-100">
                            <td class="py-4 px-4 text-base text-black-400">Projects</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">5</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">50</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">Unlimited</td>
                        </tr>
                        <tr class="border-b border-accent-100">
                            <td class="py-4 px-4 text-base text-black-400">Storage</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">10GB</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">100GB</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">Unlimited</td>
                        </tr>
                        <tr class="border-b border-accent-100">
                            <td class="py-4 px-4 text-base text-black-400">Team members</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">3</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">10</td>
                            <td class="py-4 px-4 text-center text-base text-black-400">Unlimited</td>
                        </tr>
                        <tr class="border-b border-accent-100">
                            <td class="py-4 px-4 text-base text-black-400">Analytics</td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                        </tr>
                        <tr class="border-b border-accent-100">
                            <td class="py-4 px-4 text-base text-black-400">Priority Support</td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-close-circle-outline class="w-5 h-5 text-error-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 text-base text-black-400">API Access</td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-close-circle-outline class="w-5 h-5 text-error-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-close-circle-outline class="w-5 h-5 text-error-300 mx-auto" />
                            </td>
                            <td class="py-4 px-4 text-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mx-auto" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 