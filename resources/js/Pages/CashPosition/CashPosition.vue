<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Banknote } from 'lucide-vue-next';

const companies = [
  'ROPALI CORPORATION',
  'MOTORBELLE CORPORATION',
  'MOTORALI CORPORATION',
  'MOTOROBEE CORPORATION',
];

const selectedCompany = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const records = ref([]);
const loading = ref(false);
const showAddForm = ref(false);

const form = ref({
  date: '',
  cash_account: '',
  cash_available: '',
  cash_for_repo: '',
  company: '',
});

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const totalCashAvailable = computed(() =>
  records.value.reduce((sum, r) => sum + (parseFloat(r.CashAvailable) || 0), 0)
);
const totalCashForRepo = computed(() =>
  records.value.reduce((sum, r) => sum + (parseFloat(r.CashForRepo) || 0), 0)
);
const totalCash = computed(() => totalCashAvailable.value + totalCashForRepo.value);

const fetchRecords = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-cash-positions', {
      params: { company: selectedCompany.value, date_from: dateFrom.value, date_to: dateTo.value },
    });
    records.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const saveRecord = async () => {
  try {
    await axios.post('/save-cash-position', form.value);
    form.value = { date: '', cash_account: '', cash_available: '', cash_for_repo: '', company: '' };
    showAddForm.value = false;
    await fetchRecords();
  } catch (err) {
    console.error(err);
  }
};

onMounted(fetchRecords);
</script>

<template>
  <Head title="Cash Position" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5 flex-1">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Banknote class="w-4 h-4 text-white" />
        </div>
        <div class="flex-1">
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Cash Position</h1>
          <p class="text-xs text-gray-400">Record and track daily cash positions</p>
        </div>
        <button
          @click="showAddForm = !showAddForm"
          class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg font-medium transition-all shadow-sm"
        >
          {{ showAddForm ? 'Cancel' : '+ Add Record' }}
        </button>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-0">
          <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Cash Available</p>
            <p class="text-xl font-bold text-blue-600">{{ formatPHP(totalCashAvailable) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-amber-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Cash For Repo</p>
            <p class="text-xl font-bold text-amber-600">{{ formatPHP(totalCashForRepo) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Cash</p>
            <p class="text-xl font-bold text-green-600">{{ formatPHP(totalCash) }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 space-y-5">

          <!-- Filters -->
          <div class="flex flex-wrap gap-3">
            <select
              v-model="selectedCompany"
              @change="fetchRecords"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
            >
              <option value="">All Companies</option>
              <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
            </select>
            <input
              v-model="dateFrom"
              type="date"
              @change="fetchRecords"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
            <input
              v-model="dateTo"
              type="date"
              @change="fetchRecords"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
          </div>

          <!-- Add Form -->
          <div v-if="showAddForm" class="bg-gray-50 border border-gray-200 rounded-xl p-5">
            <h3 class="font-semibold text-gray-700 mb-4">New Cash Position Record</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input v-model="form.date" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cash Account</label>
                <input v-model="form.cash_account" type="text" placeholder="e.g. CASH-001" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cash Available</label>
                <input v-model="form.cash_available" type="number" min="0" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cash For Repo</label>
                <input v-model="form.cash_for_repo" type="number" min="0" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Company</label>
                <select v-model="form.company" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <option value="" disabled>Select Company</option>
                  <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
            </div>
            <div class="mt-4 flex justify-end">
              <button @click="saveRecord" class="bg-green-600 hover:bg-green-700 text-white text-sm px-5 py-2 rounded-md transition-all">
                Save Record
              </button>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
          </div>

          <!-- Table -->
          <div v-else>
            <div v-if="records.length === 0" class="text-center py-12 text-gray-500">
              No cash position records found.
            </div>
            <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Cash Account</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Company</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Cash Available</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Cash For Repo</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Total Cash</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="r in records" :key="r.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-600">{{ r.Date }}</td>
                    <td class="px-4 py-3 font-mono text-gray-700">{{ r.CashAccount }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ r.Company }}</td>
                    <td class="px-4 py-3 text-right text-blue-600 font-medium">{{ formatPHP(r.CashAvailable) }}</td>
                    <td class="px-4 py-3 text-right text-amber-600 font-medium">{{ formatPHP(r.CashForRepo) }}</td>
                    <td class="px-4 py-3 text-right text-green-600 font-medium">
                      {{ formatPHP((parseFloat(r.CashAvailable) || 0) + (parseFloat(r.CashForRepo) || 0)) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
