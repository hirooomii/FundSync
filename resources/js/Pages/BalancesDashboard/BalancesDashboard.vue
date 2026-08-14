<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { BarChart3 } from 'lucide-vue-next';

const companyTabs = ref(['ALL']);
const activeTab = ref('ALL');

const balances = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const kpi = computed(() => {
  const total = balances.value.length;
  const balanced = balances.value.filter(b => b.Status === 'Balanced').length;
  const discrepancy = balances.value.filter(b => b.Status === 'With Discrepancy').length;
  const noData = balances.value.filter(b => b.Status === 'No Balance Data').length;
  return { total, balanced, discrepancy, noData };
});

const showModal = ref(false);
const selectedAccount = ref(null);
const transactions = ref([]);
const loadingTx = ref(false);
const modalDateFrom = ref('');
const modalDateTo = ref('');

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const fetchBalances = async () => {
  loading.value = true;
  try {
    const companyParam = activeTab.value === 'ALL' ? '' : activeTab.value;
    const res = await axios.get('/get-balance-summary', { params: { company: companyParam } });
    balances.value = Array.isArray(res.data) ? res.data : [];
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  fetchBalances();
};

const paginatedBalances = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return balances.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(balances.value.length / perPage));

const openModal = async (account) => {
  selectedAccount.value = account;
  showModal.value = true;
  await fetchTransactions(account.AccountNo);
};

const fetchTransactions = async (accountNo) => {
  loadingTx.value = true;
  transactions.value = [];
  try {
    const res = await axios.get('/get-account-transactions', {
      params: {
        account_no: accountNo,
        date_from: modalDateFrom.value,
        date_to: modalDateTo.value,
      },
    });
    transactions.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loadingTx.value = false;
  }
};

const applyModalFilter = () => {
  if (selectedAccount.value) {
    fetchTransactions(selectedAccount.value.AccountNo);
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedAccount.value = null;
  transactions.value = [];
  modalDateFrom.value = '';
  modalDateTo.value = '';
};

const getStatusBadgeClass = (status) => {
  if (status === 'Balanced') return 'bg-green-100 text-green-700 border border-green-200';
  if (status === 'With Discrepancy') return 'bg-red-100 text-red-700 border border-red-200';
  if (status === 'No Balance Data') return 'bg-gray-100 text-gray-600 border border-gray-200';
  return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
};

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies');
    companyTabs.value = ['ALL', ...(res.data ?? [])];
  } catch {}
}

onMounted(async () => {
  await loadCompanies();
  fetchBalances();
});
</script>

<template>
  <Head title="Balances Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <BarChart3 class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Balances Dashboard</h1>
          <p class="text-xs text-gray-400">Compare bank vs Acumatica balances</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- KPI Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ kpi.total }}</div>
            <div class="text-sm text-gray-500 mt-1">Accounts Reviewed</div>
          </div>
          <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-green-600">{{ kpi.balanced }}</div>
            <div class="text-sm text-gray-500 mt-1">Balanced</div>
          </div>
          <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-red-600">{{ kpi.discrepancy }}</div>
            <div class="text-sm text-gray-500 mt-1">With Discrepancy</div>
          </div>
          <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-gray-500">{{ kpi.noData }}</div>
            <div class="text-sm text-gray-500 mt-1">No Acumatica Data</div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">

          <!-- Company Filter Tabs -->
          <div class="flex flex-wrap gap-2 mb-5 border-b border-gray-200 pb-3">
            <button
              v-for="tab in companyTabs"
              :key="tab"
              @click="switchTab(tab)"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-md transition-all',
                activeTab === tab
                  ? 'bg-blue-500 text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
            >{{ tab }}</button>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
          </div>

          <!-- Table -->
          <div v-else>
            <div v-if="balances.length === 0" class="text-center py-12 text-gray-500">
              No balance records found.
            </div>
            <div v-else>
              <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account No</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Balance</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="b in paginatedBalances" :key="b.AccountNo" class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ b.AccountNo }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ b.AccountName }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ b.Bank }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ formatPHP(b.AvailableBalance) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusBadgeClass(b.Status)]">{{ b.Status }}</span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <button
                          @click="openModal(b)"
                          class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition-all"
                        >View Details</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                <span>Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, balances.length) }} of {{ balances.length }} records</span>
                <div class="flex gap-2">
                  <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                  >Prev</button>
                  <span class="px-3 py-1">{{ currentPage }} / {{ totalPages }}</span>
                  <button
                    @click="currentPage++"
                    :disabled="currentPage >= totalPages"
                    class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                  >Next</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Account Transactions Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" @click.self="closeModal">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">Account Details — {{ selectedAccount?.AccountNo }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div v-if="selectedAccount" class="grid grid-cols-2 gap-3 text-sm mb-4 bg-gray-50 rounded-lg p-3">
          <div><span class="font-medium text-gray-600">Account No:</span> <span class="text-gray-900">{{ selectedAccount.AccountNo }}</span></div>
          <div><span class="font-medium text-gray-600">Account Name:</span> <span class="text-gray-900">{{ selectedAccount.AccountName }}</span></div>
          <div><span class="font-medium text-gray-600">Bank:</span> <span class="text-gray-900">{{ selectedAccount.Bank }}</span></div>
          <div><span class="font-medium text-gray-600">Balance:</span> <span class="text-green-600 font-medium">{{ formatPHP(selectedAccount.AvailableBalance) }}</span></div>
        </div>

        <!-- Date Filter inside Modal -->
        <div class="flex flex-wrap gap-3 mb-4">
          <input v-model="modalDateFrom" type="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          <input v-model="modalDateTo" type="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          <button @click="applyModalFilter" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-all">Apply</button>
        </div>

        <h4 class="font-semibold text-gray-700 mb-2">Transactions</h4>
        <div v-if="loadingTx" class="flex justify-center py-8">
          <div class="animate-spin h-6 w-6 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>
        <div v-else-if="transactions.length === 0" class="text-center text-gray-500 py-4">No transactions found.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(t, i) in transactions" :key="i" class="hover:bg-gray-50">
                <td class="px-4 py-2 whitespace-nowrap text-gray-900">{{ t.transaction_date }}</td>
                <td class="px-4 py-2 text-gray-700">{{ t.description }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-green-600 font-medium">{{ formatPHP(t.amount) }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-gray-900">{{ t.debit_or_credit }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end mt-4">
          <button @click="closeModal" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm transition-all">Close</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
