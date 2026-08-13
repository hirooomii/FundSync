<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="flex min-h-screen">

        <!-- Left branding panel -->
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 relative overflow-hidden"
            style="background: #111827;">

            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>

            <!-- Accent gradient -->
            <div class="absolute bottom-0 left-0 w-96 h-96 rounded-full opacity-10"
                style="background: radial-gradient(circle, #3b82f6 0%, transparent 70%); transform: translate(-30%, 30%);"></div>
            <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10"
                style="background: radial-gradient(circle, #6366f1 0%, transparent 70%); transform: translate(30%, -30%);"></div>

            <!-- Logo & brand -->
            <div class="relative z-10 flex items-center gap-3">
                <ApplicationLogo class="w-10 h-10" />
                <span class="text-white text-xl font-bold tracking-wide">FundSync</span>
            </div>

            <!-- Center content -->
            <div class="relative z-10 space-y-6">
                <div class="space-y-3">
                    <h1 class="text-4xl font-bold text-white leading-tight">
                        Treasury &amp; Reconciliation<br />
                        <span style="color: #60a5fa;">Management System</span>
                    </h1>
                    <p class="text-gray-400 text-base leading-relaxed max-w-sm">
                        Streamline bank reconciliation, fund transfers, and cash monitoring across all your company accounts.
                    </p>
                </div>

                <!-- Feature pills -->
                <div class="flex flex-wrap gap-2 pt-2">
                    <span v-for="f in ['Bank Reconciliation','Fund Transfers','COH Monitoring','Multi-Company']" :key="f"
                        class="text-xs font-medium px-3 py-1.5 rounded-full"
                        style="background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.08);">
                        {{ f }}
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10">
                <p class="text-xs text-gray-600">
                    &copy; {{ new Date().getFullYear() }} Ropali Corporation. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 bg-white">
            <!-- Mobile logo -->
            <div class="lg:hidden mb-8 flex items-center gap-2">
                <ApplicationLogo class="w-8 h-8" />
                <span class="text-gray-900 text-lg font-bold">FundSync</span>
            </div>

            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

    </div>
</template>
