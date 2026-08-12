<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Activity } from 'lucide-vue-next';

const today = new Date().toISOString().split('T')[0];
const selectedDate = ref(today);
const loading = ref(false);
const companies = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/fetch-coh', { date: selectedDate.value });
    companies.value = res.data.data ?? res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const complianceRate = (reported, total) => {
  if (!total) return 0;
  return Math.round((reported / total) * 100);
};

onMounted(fetchData);
</script>

<template>
  <Head title="COH Monitoring" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Activity class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">COH Monitoring</h1>
          <p class="text-xs text-gray-400">Real-time cash on hand tracking</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- Date Filter + Refresh -->
        <div class="bg-white rounded-2xl shadow-md p-4 flex flex-wrap gap-3 items-end">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
            <input
              v-model="selectedDate"
              type="date"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
          </div>
          <button
            @click="fetchData"
            :disabled="loading"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50"
          >
            <span v-if="loading">Refreshing...</span>
            <span v-else>Refresh</span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Company Cards -->
        <div v-else>
          <div v-if="companies.length === 0" class="text-center py-12 text-gray-500">
            No COH monitoring data for the selected date.
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="company in companies"
              :key="company.name"
              class="bg-white rounded-2xl shadow-md p-6"
            >
              <!-- Company Header -->
              <h3 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                {{ company.name }}
              </h3>

              <!-- KPI Numbers -->
              <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase font-medium">Total</p>
                  <p class="text-2xl font-bold text-gray-800">{{ company.total }}</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase font-medium">Reported</p>
                  <p class="text-2xl font-bold text-green-600">{{ company.reported }}</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase font-medium">Not Reported</p>
                  <p class="text-2xl font-bold text-red-500">{{ company.not_reported }}</p>
                </div>
              </div>

              <!-- Compliance Rate Bar -->
              <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                  <span>Compliance Rate</span>
                  <span class="font-semibold text-blue-600">{{ complianceRate(company.reported, company.total) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div
                    class="h-2.5 rounded-full transition-all"
                    :class="complianceRate(company.reported, company.total) >= 80 ? 'bg-green-500' : complianceRate(company.reported, company.total) >= 50 ? 'bg-yellow-400' : 'bg-red-500'"
                    :style="{ width: complianceRate(company.reported, company.total) + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Not Reported List -->
              <div v-if="company.not_reported_accounts && company.not_reported_accounts.length > 0">
                <p class="text-xs font-semibold text-red-500 uppercase mb-2">Not Reported Accounts</p>
                <ul class="space-y-1 max-h-40 overflow-y-auto">
                  <li
                    v-for="account in company.not_reported_accounts"
                    :key="account"
                    class="text-xs text-gray-600 flex items-center gap-2"
                  >
                    <span class="inline-block w-2 h-2 bg-red-400 rounded-full flex-shrink-0"></span>
                    {{ account }}
                  </li>
                </ul>
              </div>
              <div v-else class="text-xs text-gray-400 italic">All accounts have reported.</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
