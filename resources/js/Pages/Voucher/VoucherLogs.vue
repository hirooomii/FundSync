<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { FileClock } from 'lucide-vue-next';

const companies = ref([])
const selectedCompany = ref('');

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}
const logs = ref([]);
const loading = ref(false);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const typeBadge = (type) => {
  if (type === 'CREATED') return 'bg-blue-100 text-blue-700 border border-blue-200';
  if (type === 'APPROVED') return 'bg-green-100 text-green-700 border border-green-200';
  if (type === 'PRINTED') return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
  return 'bg-gray-100 text-gray-700 border border-gray-200';
};

const fetchLogs = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-voucher-logs', {
      params: { company: selectedCompany.value },
    });
    logs.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => { loadCompanies(); fetchLogs(); });
</script>

<template>
  <Head title="Voucher Logs" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5 flex-1">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <FileClock class="w-4 h-4 text-white" />
        </div>
        <div class="flex-1">
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Voucher Logs</h1>
          <p class="text-xs text-gray-400">Audit trail of voucher activities</p>
        </div>
        <a :href="route('voucher.list')" class="flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-sm px-3 py-1.5 rounded-lg font-medium transition-all shadow-sm">
          &larr; Back to Vouchers
        </a>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filter -->
        <div class="mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchLogs"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option value="">All Companies</option>
            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="logs.length === 0" class="text-center py-12 text-gray-500">
            No log records found.
          </div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Reference No</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Vendor Name</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Type</th>
                  <th class="px-4 py-3 text-right font-semibold text-gray-600">Amount</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Company</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 font-mono text-gray-700">{{ log.ReferenceNo }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ log.VendorName }}</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', typeBadge(log.Type)]">
                      {{ log.Type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(log.Amount) }}</td>
                  <td class="px-4 py-3 text-gray-600 text-xs">{{ log.Company }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ log.Date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
