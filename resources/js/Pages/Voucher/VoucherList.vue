<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Database } from 'lucide-vue-next';

const companies = ref([])
const selectedCompany = ref('');

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}
const search = ref('');
const vouchers = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const fetchVouchers = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-vouchers', {
      params: { company: selectedCompany.value, search: search.value },
    });
    vouchers.value = res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const paginatedVouchers = () => {
  const start = (currentPage.value - 1) * perPage;
  return vouchers.value.slice(start, start + perPage);
};

const totalPages = () => Math.ceil(vouchers.value.length / perPage);

onMounted(() => { loadCompanies(); fetchVouchers(); });
</script>

<template>
  <Head title="Payment Vouchers" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5 flex-1">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Database class="w-4 h-4 text-white" />
        </div>
        <div class="flex-1">
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Voucher List</h1>
          <p class="text-xs text-gray-400">Payment vouchers and disbursements</p>
        </div>
        <a :href="route('voucher.logs')" class="flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-sm px-3 py-1.5 rounded-lg font-medium transition-all shadow-sm">
          View Logs
        </a>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchVouchers"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option value="">All Companies</option>
            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
          </select>

          <input
            v-model="search"
            @input="fetchVouchers"
            placeholder="Search..."
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none w-64"
          />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="vouchers.length === 0" class="text-center py-12 text-gray-500">
            No voucher records found.
          </div>
          <div v-else>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Reference No</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Vendor Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Post Date</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Amount</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Company</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="v in paginatedVouchers()" :key="v.ReferenceNo" class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-gray-700">{{ v.ReferenceNo }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ v.VendorName }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ v.PostDate }}</td>
                    <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(v.Amount) }}</td>
                    <td class="px-4 py-3 text-center">
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs font-semibold',
                          v.Type === 'CHECK'
                            ? 'bg-blue-100 text-blue-700 border border-blue-200'
                            : 'bg-purple-100 text-purple-700 border border-purple-200'
                        ]"
                      >{{ v.Type }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ v.Company }}</td>
                    <td class="px-4 py-3 text-center">
                      <button class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition-all">
                        View
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
              <span>Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, vouchers.length) }} of {{ vouchers.length }} records</span>
              <div class="flex gap-2">
                <button
                  @click="currentPage--"
                  :disabled="currentPage === 1"
                  class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                >Prev</button>
                <span class="px-3 py-1">{{ currentPage }} / {{ totalPages() }}</span>
                <button
                  @click="currentPage++"
                  :disabled="currentPage >= totalPages()"
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
