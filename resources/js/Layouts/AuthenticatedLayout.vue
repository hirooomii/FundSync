<script setup>
import { ref } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link } from '@inertiajs/vue3'

// ✅ Lucide icons
import { LayoutDashboard, Banknote, LogOut, User, Menu, X, Landmark, HandCoins, CircleDollarSign } from 'lucide-vue-next'

const showingNavigationDropdown = ref(false)
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="border-b border-gray-200 bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
          <div class="flex items-center">
            <!-- Logo -->
            <Link :href="route('dashboard')" class="flex items-center space-x-2">
              <ApplicationLogo class="block h-9 w-auto text-gray-800" />
              <span class="text-lg font-semibold text-gray-800">MyApp</span>
            </Link>

            <!-- Desktop Nav Links -->
            <div class="hidden sm:flex sm:space-x-6 sm:ml-10">
              <NavLink
                :href="route('dashboard')"
                :active="route().current('dashboard')"
                class="flex items-center gap-2"
              >
                <LayoutDashboard class="w-5 h-5" />
                <span>Dashboard</span>
              </NavLink>

              <NavLink
                :href="route('cashaccount')"
                :active="route().current('cashaccount')"
                class="flex items-center gap-2"
              >
                <HandCoins class="w-5 h-5" />
                <span>Cash Account</span>
              </NavLink>

              
              <NavLink
                :href="route('depositorybank')"
                :active="route().current('depositorybank')"
                class="flex items-center gap-2"
              >
                <Landmark class="w-5 h-5" />
                <span>Depository Bank</span>
              </NavLink>

              <NavLink
                :href="route('payment.schedule.calendar')"
                :active="route().current('payment.schedule.calendar')"
                class="flex items-center gap-2"
              >
                <CircleDollarSign class="w-5 h-5" />
                <span>Payment Schedule</span>
              </NavLink>
            </div>
          </div>

          <!-- User Dropdown -->
          <div class="hidden sm:flex sm:items-center sm:space-x-6">
            <Dropdown align="right" width="48">
              <template #trigger>
                <button
                  class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition-all"
                >
                  <User class="w-5 h-5 text-gray-500" />
                  <span class="text-sm font-medium">
                    {{ $page.props.auth.user.name }}
                  </span>
                  <svg
                    class="h-4 w-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 20"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 8l4 4 4-4" />
                  </svg>
                </button>
              </template>

              <template #content>
                <DropdownLink :href="route('profile.edit')">
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-gray-500" />
                    Profile
                  </div>
                </DropdownLink>

                <DropdownLink :href="route('logout')" method="post" as="button">
                  <div class="flex items-center gap-2 text-red-600">
                    <LogOut class="w-4 h-4" />
                    Log Out
                  </div>
                </DropdownLink>
              </template>
            </Dropdown>
          </div>

          <!-- Mobile Hamburger -->
          <div class="sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <component
                :is="showingNavigationDropdown ? X : Menu"
                class="w-6 h-6"
              />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div
        v-show="showingNavigationDropdown"
        class="sm:hidden bg-white border-t border-gray-200"
      >
        <div class="space-y-1 px-4 py-3">
          <ResponsiveNavLink
            :href="route('dashboard')"
            :active="route().current('dashboard')"
            class="flex items-center gap-2"
          >
            <LayoutDashboard class="w-5 h-5" />
            Dashboard
          </ResponsiveNavLink>

          <ResponsiveNavLink
            :href="route('cashaccount')"
            :active="route().current('cashaccount')"
            class="flex items-center gap-2"
          >
            <HandCoins class="w-5 h-5" />
            Cash Account
          </ResponsiveNavLink>

          <ResponsiveNavLink
            :href="route('depositorybank')"
            :active="route().current('depositorybank')"
            class="flex items-center gap-2"
          >
            <Landmark class="w-5 h-5" />
            Depository Bank
          </ResponsiveNavLink>

          <ResponsiveNavLink
            :href="route('payment.schedule.calendar')"
            :active="route().current('payment.schedule.calendar')"
            class="flex items-center gap-2"
          >
            <CircleDollarSign class="w-5 h-5" />
            Payment Schedule
          </ResponsiveNavLink>
        </div>

        <div class="border-t border-gray-100 px-4 py-4">
          <div class="flex flex-col space-y-2">
            <div class="font-semibold text-gray-800">{{ $page.props.auth.user.name }}</div>
            <div class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
          </div>

          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')" class="flex items-center gap-2">
              <User class="w-5 h-5" />
              Profile
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('logout')"
              method="post"
              as="button"
              class="flex items-center gap-2 text-red-600"
            >
              <LogOut class="w-5 h-5" />
              Log Out
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Header -->
    <header v-if="$slots.header" class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>
