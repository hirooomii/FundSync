<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { ClipboardList } from 'lucide-vue-next';

const companies = ref([])

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const selectedCompany = ref('');
const selectedYear = ref(currentYear);
const vouchers = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const showModal = ref(false);
const selectedVoucher = ref(null);
const modalDetails = ref([]);
const loadingDetails = ref(false);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const fetchVouchers = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-fta-vouchers', {
      params: { company: selectedCompany.value, year: selectedYear.value },
    });
    vouchers.value = res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const paginatedVouchers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return vouchers.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(vouchers.value.length / perPage));

const openModal = async (voucher) => {
  selectedVoucher.value = voucher;
  showModal.value = true;
  loadingDetails.value = true;
  modalDetails.value = [];
  try {
    const res = await axios.get('/get-fta-voucher-details', {
      params: { transfer_no: voucher.TransferNo },
    });
    modalDetails.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loadingDetails.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedVoucher.value = null;
  modalDetails.value = [];
};

onMounted(() => { loadCompanies(); fetchVouchers(); });
</script>

<template>
  <Head title="Fund Transfer Vouchers" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <ClipboardList class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">FTA List</h1>
          <p class="text-xs text-gray-400">Fund transfer authority vouchers</p>
        </div>
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

          <select
            v-model="selectedYear"
            @change="fetchVouchers"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="vouchers.length === 0" class="text-center py-12 text-gray-500">
            No fund transfer voucher records found.
          </div>
          <div v-else>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="v in paginatedVouchers" :key="v.TransferNo" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ v.TransferNo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ v.BatchNo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ v.TransferDate }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ formatPHP(v.Amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ v.CashAccount }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ v.Purpose }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ v.Company }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <button
                        @click="openModal(v)"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition-all"
                      >View</button>
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

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" @click.self="closeModal">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">
            Voucher Details — {{ selectedVoucher?.TransferNo }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div v-if="selectedVoucher" class="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div><span class="font-medium text-gray-600">Transfer No:</span> <span class="text-gray-900">{{ selectedVoucher.TransferNo }}</span></div>
          <div><span class="font-medium text-gray-600">Batch No:</span> <span class="text-gray-900">{{ selectedVoucher.BatchNo }}</span></div>
          <div><span class="font-medium text-gray-600">Transfer Date:</span> <span class="text-gray-900">{{ selectedVoucher.TransferDate }}</span></div>
          <div><span class="font-medium text-gray-600">Amount:</span> <span class="text-green-600 font-medium">{{ formatPHP(selectedVoucher.Amount) }}</span></div>
          <div><span class="font-medium text-gray-600">Cash Account:</span> <span class="text-gray-900">{{ selectedVoucher.CashAccount }}</span></div>
          <div><span class="font-medium text-gray-600">Company:</span> <span class="text-gray-900">{{ selectedVoucher.Company }}</span></div>
          <div class="col-span-2"><span class="font-medium text-gray-600">Purpose:</span> <span class="text-gray-900">{{ selectedVoucher.Purpose }}</span></div>
        </div>

        <h4 class="font-semibold text-gray-700 mb-2 mt-4">Entry Details</h4>
        <div v-if="loadingDetails" class="flex justify-center py-8">
          <div class="animate-spin h-6 w-6 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>
        <div v-else-if="modalDetails.length === 0" class="text-center text-gray-500 py-4">No detail records found.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(d, i) in modalDetails" :key="i" class="hover:bg-gray-50">
                <td class="px-4 py-2 whitespace-nowrap text-gray-900">{{ d.Account }}</td>
                <td class="px-4 py-2 text-gray-700">{{ d.Description }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-gray-900">{{ formatPHP(d.Debit) }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-gray-900">{{ formatPHP(d.Credit) }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ d.Ref }}</td>
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
