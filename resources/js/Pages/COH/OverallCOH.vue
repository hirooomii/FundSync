<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { TrendingUp } from 'lucide-vue-next';

const companyTabs = ['ALL', 'ROPALI', 'MOTORBELLE', 'MOTORALI', 'MOTOROBEE'];
const selectedCompany = ref('ALL');
const dateFrom = ref('');
const dateTo = ref('');
const records = ref([]);
const loading = ref(false);

// Modal state
const showModal = ref(false);
const selectedRecord = ref(null);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const kpi = computed(() => {
  const total = records.value.length;
  const reported = records.value.filter((r) => r.reported).length;
  const notReported = total - reported;
  const complianceRate = total > 0 ? ((reported / total) * 100).toFixed(1) : '0.0';
  return { total, reported, notReported, complianceRate };
});

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/fetch-overall-coh', {
      company: selectedCompany.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
    });
    records.value = res.data.data ?? res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openModal = (record) => {
  selectedRecord.value = record;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedRecord.value = null;
};

onMounted(fetchData);
</script>

<template>
  <Head title="Overall Cash On Hand Report" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <TrendingUp class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Overall COH</h1>
          <p class="text-xs text-gray-400">Cash on hand report across all companies</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- Company Tabs -->
        <div class="bg-white rounded-2xl shadow-md p-4">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="tab in companyTabs"
              :key="tab"
              @click="selectedCompany = tab; fetchData()"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                selectedCompany === tab
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
            >
              {{ tab }}
            </button>
          </div>
        </div>

        <!-- Date Filters -->
        <div class="bg-white rounded-2xl shadow-md p-4">
          <div class="flex flex-wrap gap-3 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
              <input
                v-model="dateFrom"
                type="date"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
              <input
                v-model="dateTo"
                type="date"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              />
            </div>
            <button
              @click="fetchData"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
            >
              Apply Filter
            </button>
          </div>
        </div>

        <!-- KPI Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-2xl shadow-md p-5 text-center">
            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Total Accounts</p>
            <p class="text-3xl font-bold text-gray-800">{{ kpi.total }}</p>
          </div>
          <div class="bg-white rounded-2xl shadow-md p-5 text-center">
            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Reported</p>
            <p class="text-3xl font-bold text-green-600">{{ kpi.reported }}</p>
          </div>
          <div class="bg-white rounded-2xl shadow-md p-5 text-center">
            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Not Reported</p>
            <p class="text-3xl font-bold text-red-500">{{ kpi.notReported }}</p>
          </div>
          <div class="bg-white rounded-2xl shadow-md p-5 text-center">
            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Compliance Rate</p>
            <p class="text-3xl font-bold text-blue-600">{{ kpi.complianceRate }}%</p>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-md p-6">

          <!-- Loading -->
          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
          </div>

          <div v-else>
            <div v-if="records.length === 0" class="text-center py-12 text-gray-500">
              No COH records found.
            </div>
            <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                  <tr>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Cash Account</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-right">Collection</th>
                    <th class="px-4 py-3 text-right">Deposit</th>
                    <th class="px-4 py-3 text-right">Payment</th>
                    <th class="px-4 py-3 text-right">Ending Balance</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="rec in records"
                    :key="rec.id"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-4 py-3 text-gray-700">{{ rec.company }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ rec.cash_account }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ rec.created_by }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ rec.date }}</td>
                    <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(rec.collection) }}</td>
                    <td class="px-4 py-3 text-right font-medium text-blue-600">{{ formatPHP(rec.deposit) }}</td>
                    <td class="px-4 py-3 text-right font-medium text-red-500">{{ formatPHP(rec.payment) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">{{ formatPHP(rec.ending_balance) }}</td>
                    <td class="px-4 py-3 text-center">
                      <button
                        @click="openModal(rec)"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                      >
                        View Details
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">COH Details — {{ selectedRecord?.cash_account }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Denomination Breakdown -->
        <div class="mb-5">
          <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">Denomination Breakdown</h4>
          <div v-if="selectedRecord?.denomination" class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th class="px-3 py-2 text-left">Denomination</th>
                  <th class="px-3 py-2 text-center">Pieces</th>
                  <th class="px-3 py-2 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(denom, i) in selectedRecord.denomination" :key="i" class="hover:bg-gray-50 transition-colors">
                  <td class="px-3 py-2 text-gray-700">{{ denom.denomination }}</td>
                  <td class="px-3 py-2 text-center text-gray-700">{{ denom.pieces }}</td>
                  <td class="px-3 py-2 text-right text-gray-700">{{ formatPHP(denom.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="text-sm text-gray-400">No denomination data.</p>
        </div>

        <!-- Transaction Details -->
        <div class="grid grid-cols-1 gap-4">
          <div v-if="selectedRecord?.CollectionDetails">
            <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">Collection Details</h4>
            <pre class="bg-gray-50 rounded-lg p-3 text-xs text-gray-700 overflow-x-auto">{{ selectedRecord.CollectionDetails }}</pre>
          </div>
          <div v-if="selectedRecord?.PaymentDetails">
            <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">Payment Details</h4>
            <pre class="bg-gray-50 rounded-lg p-3 text-xs text-gray-700 overflow-x-auto">{{ selectedRecord.PaymentDetails }}</pre>
          </div>
          <div v-if="selectedRecord?.DepositDetails">
            <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">Deposit Details</h4>
            <pre class="bg-gray-50 rounded-lg p-3 text-xs text-gray-700 overflow-x-auto">{{ selectedRecord.DepositDetails }}</pre>
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
