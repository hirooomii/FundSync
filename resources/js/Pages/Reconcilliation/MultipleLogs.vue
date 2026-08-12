<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { ScrollText } from 'lucide-vue-next';

const companies = [
  'ROPALI CORPORATION',
  'MOTORBELLE CORPORATION',
  'MOTORALI CORPORATION',
  'MOTOROBEE CORPORATION',
];

const selectedCompany = ref('');
const logs = ref([]);
const loading = ref(false);

// Modal state
const showModal = ref(false);
const selectedLog = ref(null);
const activeTab = ref('bank');
const bankDetails = ref([]);
const bookingDetails = ref([]);
const modalLoading = ref(false);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const statusBadge = (status) => {
  if (status === null || status === undefined) return { label: 'Pending', cls: 'bg-yellow-100 text-yellow-700 border border-yellow-200' };
  if (status == 1) return { label: 'Approved', cls: 'bg-green-100 text-green-700 border border-green-200' };
  return { label: 'Disapproved', cls: 'bg-red-100 text-red-700 border border-red-200' };
};

const fetchLogs = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-multiple-logs', {
      params: { company: selectedCompany.value },
    });
    logs.value = res.data.data ?? res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openModal = async (log) => {
  selectedLog.value = log;
  activeTab.value = 'bank';
  bankDetails.value = [];
  bookingDetails.value = [];
  showModal.value = true;
  await loadTabData('bank');
};

const loadTabData = async (tab) => {
  activeTab.value = tab;
  modalLoading.value = true;
  try {
    if (tab === 'bank') {
      const res = await axios.get('/preview-bank-details', { params: { multiple_id: selectedLog.value.multiple_id } });
      bankDetails.value = res.data.data ?? res.data;
    } else {
      const res = await axios.get('/preview-booking-details', { params: { multiple_id: selectedLog.value.multiple_id } });
      bookingDetails.value = res.data.data ?? res.data;
    }
  } catch (err) {
    console.error(err);
  } finally {
    modalLoading.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedLog.value = null;
};

onMounted(fetchLogs);
</script>

<template>
  <Head title="Multiple Reconciliation Logs" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <ScrollText class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Multiple Reconciliation Logs</h1>
          <p class="text-xs text-gray-400">History of grouped reconciliation actions</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
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
            No multiple reconciliation log records found.
          </div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th class="px-4 py-3 text-left">Multiple ID</th>
                  <th class="px-4 py-3 text-left">Company</th>
                  <th class="px-4 py-3 text-right">Total Amount</th>
                  <th class="px-4 py-3 text-center">Entry Count</th>
                  <th class="px-4 py-3 text-left">Created By</th>
                  <th class="px-4 py-3 text-left">Date</th>
                  <th class="px-4 py-3 text-center">Status</th>
                  <th class="px-4 py-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="log in logs"
                  :key="log.multiple_id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3 font-mono text-gray-700">{{ log.multiple_id }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ log.company }}</td>
                  <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(log.total_amount) }}</td>
                  <td class="px-4 py-3 text-center text-gray-700">{{ log.entry_count }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ log.created_by }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ log.date }}</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', statusBadge(log.status).cls]">
                      {{ statusBadge(log.status).label }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button
                      @click="openModal(log)"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">
            Multiple ID: {{ selectedLog?.multiple_id }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-4">
          <button
            @click="loadTabData('bank')"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'bank'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            Bank Details
          </button>
          <button
            @click="loadTabData('booking')"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'booking'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            Booking Details
          </button>
        </div>

        <!-- Tab content loading -->
        <div v-if="modalLoading" class="flex justify-center py-8">
          <div class="animate-spin h-6 w-6 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Bank Details Tab -->
        <div v-else-if="activeTab === 'bank'">
          <div v-if="bankDetails.length === 0" class="text-center py-8 text-gray-500 text-sm">No bank details found.</div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th class="px-3 py-2 text-left">Bank Ref</th>
                  <th class="px-3 py-2 text-right">Amount</th>
                  <th class="px-3 py-2 text-left">Type</th>
                  <th class="px-3 py-2 text-left">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(row, i) in bankDetails" :key="i" class="hover:bg-gray-50 transition-colors">
                  <td class="px-3 py-2 font-mono text-gray-700">{{ row.bank_ref }}</td>
                  <td class="px-3 py-2 text-right text-green-600 font-medium">{{ formatPHP(row.amount) }}</td>
                  <td class="px-3 py-2 text-gray-600">{{ row.type }}</td>
                  <td class="px-3 py-2 text-gray-500">{{ row.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Booking Details Tab -->
        <div v-else-if="activeTab === 'booking'">
          <div v-if="bookingDetails.length === 0" class="text-center py-8 text-gray-500 text-sm">No booking details found.</div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th class="px-3 py-2 text-left">Acumatica Ref</th>
                  <th class="px-3 py-2 text-right">Amount</th>
                  <th class="px-3 py-2 text-left">Type</th>
                  <th class="px-3 py-2 text-left">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(row, i) in bookingDetails" :key="i" class="hover:bg-gray-50 transition-colors">
                  <td class="px-3 py-2 font-mono text-gray-700">{{ row.acumatica_ref }}</td>
                  <td class="px-3 py-2 text-right text-green-600 font-medium">{{ formatPHP(row.amount) }}</td>
                  <td class="px-3 py-2 text-gray-600">{{ row.type }}</td>
                  <td class="px-3 py-2 text-gray-500">{{ row.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button @click="closeModal" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
            Close
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
