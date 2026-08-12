<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Building2 } from 'lucide-vue-next';

const companies = [
  'ROPALI CORPORATION',
  'MOTORBELLE CORPORATION',
  'MOTORALI CORPORATION',
  'MOTOROBEE CORPORATION',
];

const selectedCompany = ref('');
const search = ref('');
const accounts = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const fetchAccounts = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-list', {
      params: { company: selectedCompany.value },
    });
    accounts.value = res.data.data ?? res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const filteredAccounts = computed(() => {
  if (!search.value) return accounts.value;
  const q = search.value.toLowerCase();
  return accounts.value.filter(
    (a) =>
      String(a.account_no ?? '').toLowerCase().includes(q) ||
      String(a.account_name ?? '').toLowerCase().includes(q) ||
      String(a.bank_name ?? '').toLowerCase().includes(q) ||
      String(a.account_type ?? '').toLowerCase().includes(q)
  );
});

const paginatedAccounts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredAccounts.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(filteredAccounts.value.length / perPage));

onMounted(fetchAccounts);
</script>

<template>
  <Head title="List of Bank Accounts" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Building2 class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">List of Bank Accounts</h1>
          <p class="text-xs text-gray-400">All registered bank accounts by company</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchAccounts"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option value="">All Companies</option>
            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
          </select>

          <input
            v-model="search"
            placeholder="Search accounts..."
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none w-64"
          />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="filteredAccounts.length === 0" class="text-center py-12 text-gray-500">
            No bank account records found.
          </div>
          <div v-else>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                  <tr>
                    <th class="px-4 py-3 text-left">Account No</th>
                    <th class="px-4 py-3 text-left">Account Name</th>
                    <th class="px-4 py-3 text-left">Bank Name</th>
                    <th class="px-4 py-3 text-left">Account Type</th>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Interest Rate</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-left">Date Opened</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="account in paginatedAccounts"
                    :key="account.account_no"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-4 py-3 font-mono text-gray-700">{{ account.account_no }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ account.account_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ account.bank_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ account.account_type }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ account.company }}</td>
                    <td class="px-4 py-3 text-center">
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs font-semibold',
                          account.status === 'Active' || account.status === 1
                            ? 'bg-green-100 text-green-700 border border-green-200'
                            : 'bg-red-100 text-red-700 border border-red-200'
                        ]"
                      >
                        {{ account.status === 1 ? 'Active' : account.status === 0 ? 'Inactive' : account.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ account.interest_rate != null ? account.interest_rate + '%' : '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(account.balance) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ account.date_opened }}</td>
                    <td class="px-4 py-3 text-center">
                      <a
                        :href="`/depository-bank/${account.account_no}`"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium inline-block"
                      >
                        View Details
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
              <span>
                Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredAccounts.length) }} of {{ filteredAccounts.length }} records
              </span>
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
  </AuthenticatedLayout>
</template>
