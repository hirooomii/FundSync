<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Send } from 'lucide-vue-next';

const companies = [
  'ROPALI CORPORATION',
  'MOTORBELLE CORPORATION',
  'MOTORALI CORPORATION',
  'MOTOROBEE CORPORATION',
];

const selectedCompany = ref('');
const search = ref('');
const transfers = ref([]);
const loading = ref(false);

const showModal = ref(false);
const selectedTransfer = ref(null);
const detailLoading = ref(false);
const approving = ref(false);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const statusBadge = (status) => {
  if (status === 'Fully Approved') return 'bg-green-100 text-green-700 border border-green-200';
  if (status === 'Disapproved') return 'bg-red-100 text-red-700 border border-red-200';
  return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
};

const approvalBadge = (status) => {
  if (status === 'APPROVED') return 'bg-green-100 text-green-700 border border-green-200';
  if (status === 'DISAPPROVED') return 'bg-red-100 text-red-700 border border-red-200';
  return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
};

const fetchTransfers = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-fund-transfers', {
      params: { company: selectedCompany.value, search: search.value },
    });
    transfers.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openDetail = async (transfer) => {
  showModal.value = true;
  selectedTransfer.value = { ...transfer, approvalSequence: [], entries: [] };
  detailLoading.value = true;
  try {
    const res = await axios.get(`/fund-transfer-details/${transfer.id}`);
    selectedTransfer.value = { ...transfer, ...res.data };
  } catch (err) {
    console.error(err);
  } finally {
    detailLoading.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedTransfer.value = null;
};

const pendingStep = () => {
  if (!selectedTransfer.value?.approvalSequence) return null;
  return selectedTransfer.value.approvalSequence.find((s) => s.Status === 'PENDING') ?? null;
};

const approveTransfer = async () => {
  const step = pendingStep();
  if (!step) return;
  approving.value = true;
  try {
    await axios.post('/approve-fund-transfer', {
      ftid: selectedTransfer.value.id,
      sequence_id: step.id,
    });
    await openDetail(selectedTransfer.value);
    await fetchTransfers();
  } catch (err) {
    console.error(err);
  } finally {
    approving.value = false;
  }
};

onMounted(fetchTransfers);
</script>

<template>
  <Head title="Fund Transfers" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Send class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Fund Transfers</h1>
          <p class="text-xs text-gray-400">Manage inter-company fund transfer requests</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchTransfers"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option value="">All Companies</option>
            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
          </select>
          <input
            v-model="search"
            @input="fetchTransfers"
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
          <div v-if="transfers.length === 0" class="text-center py-12 text-gray-500">
            No fund transfer records found.
          </div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Serial No</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Reference</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Company</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Approval Progress</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="t in transfers" :key="t.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 font-mono text-gray-700">{{ t.SerialNo }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ t.Reference }}</td>
                  <td class="px-4 py-3 text-gray-600 text-xs">{{ t.Company }}</td>
                  <td class="px-4 py-3 text-center text-gray-600">{{ t.ApprovedCount }}/{{ t.TotalCount }} approved</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', statusBadge(t.Status)]">
                      {{ t.Status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-500">{{ t.Date }}</td>
                  <td class="px-4 py-3 text-center">
                    <button
                      @click="openDetail(t)"
                      class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition-all"
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

    <!-- Detail Modal -->
    <transition name="fade">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-bold text-gray-800">Fund Transfer Details</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-5">
            <div v-if="detailLoading" class="flex justify-center py-8">
              <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            </div>

            <template v-else-if="selectedTransfer">
              <!-- Header Info -->
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="font-medium text-gray-500">Serial No:</span> <span class="text-gray-800 ml-1">{{ selectedTransfer.SerialNo }}</span></div>
                <div><span class="font-medium text-gray-500">Company:</span> <span class="text-gray-800 ml-1">{{ selectedTransfer.Company }}</span></div>
                <div><span class="font-medium text-gray-500">Created By:</span> <span class="text-gray-800 ml-1">{{ selectedTransfer.CreatedBy }}</span></div>
                <div><span class="font-medium text-gray-500">Date:</span> <span class="text-gray-800 ml-1">{{ selectedTransfer.Date }}</span></div>
              </div>

              <!-- Approval Sequence -->
              <div>
                <h4 class="font-semibold text-gray-700 mb-2 text-sm">Approval Sequence</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                  <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Signatory</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600">Sequence</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Approved Date</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                      <tr v-for="seq in selectedTransfer.approvalSequence" :key="seq.id">
                        <td class="px-3 py-2 text-gray-700">{{ seq.Signatory }}</td>
                        <td class="px-3 py-2 text-center text-gray-600">{{ seq.Sequence }}</td>
                        <td class="px-3 py-2 text-center">
                          <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', approvalBadge(seq.Status)]">
                            {{ seq.Status }}
                          </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ seq.ApprovedDate ?? '—' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Voucher Entries -->
              <div>
                <h4 class="font-semibold text-gray-700 mb-2 text-sm">Voucher Entries</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                  <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Cash Account</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Particulars</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Debit</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Credit</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                      <tr v-for="entry in selectedTransfer.entries" :key="entry.id">
                        <td class="px-3 py-2 font-mono text-gray-700">{{ entry.CashAccount }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ entry.Particulars }}</td>
                        <td class="px-3 py-2 text-right text-blue-600">{{ entry.Debit ? formatPHP(entry.Debit) : '—' }}</td>
                        <td class="px-3 py-2 text-right text-red-600">{{ entry.Credit ? formatPHP(entry.Credit) : '—' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Approve Button -->
              <div class="flex justify-end pt-2">
                <button
                  v-if="pendingStep()"
                  @click="approveTransfer"
                  :disabled="approving"
                  class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-sm px-5 py-2 rounded-md transition-all"
                >
                  {{ approving ? 'Approving...' : 'Approve' }}
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </transition>
  </AuthenticatedLayout>
</template>
