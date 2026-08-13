<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { CheckSquare } from 'lucide-vue-next';

const companies = ref([])
const selectedCompany = ref('');

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}
const groups = ref([]);
const loading = ref(false);

const showModal = ref(false);
const selectedGroup = ref(null);
const modalEntries = ref([]);
const entriesLoading = ref(false);

const showDisapproveForm = ref(false);
const disapproveReason = ref('');
const disapproveTargetId = ref(null);
const actionLoading = ref(false);

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const statusBadge = (status) => {
  if (status === 'Approved') return 'bg-green-100 text-green-700 border border-green-200';
  if (status === 'Disapproved') return 'bg-red-100 text-red-700 border border-red-200';
  return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
};

const fetchGroups = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-recon-groups', {
      params: { company: selectedCompany.value },
    });
    groups.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openView = async (group) => {
  selectedGroup.value = group;
  modalEntries.value = [];
  showModal.value = true;
  showDisapproveForm.value = false;
  disapproveReason.value = '';
  entriesLoading.value = true;
  try {
    const res = await axios.get('/get-recon-groups', {
      params: { multiple_id: group.MultipleID },
    });
    modalEntries.value = Array.isArray(res.data) ? res.data : res.data.entries ?? [];
  } catch (err) {
    console.error(err);
  } finally {
    entriesLoading.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedGroup.value = null;
  showDisapproveForm.value = false;
  disapproveReason.value = '';
};

const approveRecon = async (multipleId) => {
  actionLoading.value = true;
  try {
    await axios.post('/approve-recon', { multiple_id: multipleId });
    await fetchGroups();
    if (showModal.value && selectedGroup.value?.MultipleID === multipleId) {
      selectedGroup.value = groups.value.find((g) => g.MultipleID === multipleId) ?? selectedGroup.value;
    }
  } catch (err) {
    console.error(err);
  } finally {
    actionLoading.value = false;
  }
};

const startDisapprove = (multipleId) => {
  disapproveTargetId.value = multipleId;
  showDisapproveForm.value = true;
  disapproveReason.value = '';
};

const submitDisapprove = async () => {
  if (!disapproveReason.value.trim()) return;
  actionLoading.value = true;
  try {
    await axios.post('/disapprove-recon', {
      multiple_id: disapproveTargetId.value,
      reason: disapproveReason.value,
    });
    showDisapproveForm.value = false;
    disapproveReason.value = '';
    await fetchGroups();
    if (showModal.value) closeModal();
  } catch (err) {
    console.error(err);
  } finally {
    actionLoading.value = false;
  }
};

onMounted(() => { loadCompanies(); fetchGroups(); });
</script>

<template>
  <Head title="Multiple Reconciliation Approval" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <CheckSquare class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Multiple Reconciliation Approval</h1>
          <p class="text-xs text-gray-400">Approve or reject grouped reconciliation entries</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filter -->
        <div class="mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchGroups"
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
          <div v-if="groups.length === 0" class="text-center py-12 text-gray-500">
            No reconciliation groups found.
          </div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Multiple ID</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Company</th>
                  <th class="px-4 py-3 text-right font-semibold text-gray-600">Total Amount</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Entry Count</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                  <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="g in groups" :key="g.MultipleID" class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 font-mono text-gray-700">{{ g.MultipleID }}</td>
                  <td class="px-4 py-3 text-gray-600 text-xs">{{ g.Company }}</td>
                  <td class="px-4 py-3 text-right font-medium text-green-600">{{ formatPHP(g.TotalAmount) }}</td>
                  <td class="px-4 py-3 text-center text-gray-600">{{ g.EntryCount }}</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', statusBadge(g.Status)]">
                      {{ g.Status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-500">{{ g.Date }}</td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <button
                        @click="openView(g)"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2.5 py-1.5 rounded-md transition-all"
                      >View</button>
                      <button
                        v-if="g.Status === 'Pending'"
                        @click="approveRecon(g.MultipleID)"
                        :disabled="actionLoading"
                        class="bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white text-xs px-2.5 py-1.5 rounded-md transition-all"
                      >Approve</button>
                      <button
                        v-if="g.Status === 'Pending'"
                        @click="startDisapprove(g.MultipleID)"
                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-2.5 py-1.5 rounded-md transition-all"
                      >Disapprove</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Disapprove inline form -->
        <div v-if="showDisapproveForm" class="mt-5 bg-red-50 border border-red-200 rounded-xl p-5">
          <h3 class="font-semibold text-red-700 mb-3 text-sm">Disapprove Reconciliation #{{ disapproveTargetId }}</h3>
          <textarea
            v-model="disapproveReason"
            rows="3"
            placeholder="Enter reason for disapproval..."
            class="w-full border border-red-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none"
          ></textarea>
          <div class="flex gap-2 mt-3 justify-end">
            <button
              @click="showDisapproveForm = false"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-2 rounded-md transition-all"
            >Cancel</button>
            <button
              @click="submitDisapprove"
              :disabled="actionLoading || !disapproveReason.trim()"
              class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm px-4 py-2 rounded-md transition-all"
            >{{ actionLoading ? 'Submitting...' : 'Submit Disapproval' }}</button>
          </div>
        </div>

      </div>
    </div>

    <!-- View Entries Modal -->
    <transition name="fade">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <div>
              <h3 class="text-lg font-bold text-gray-800">Reconciliation Entries</h3>
              <p class="text-xs text-gray-500 mt-0.5">Multiple ID: {{ selectedGroup?.MultipleID }}</p>
            </div>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="entriesLoading" class="flex justify-center py-8">
              <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            </div>
            <div v-else>
              <div v-if="modalEntries.length === 0" class="text-center py-8 text-gray-500">
                No entries found.
              </div>
              <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left font-semibold text-gray-600">Bank Ref</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-600">Acumatica Ref</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                      <th class="px-3 py-2 text-right font-semibold text-gray-600">Amount</th>
                      <th class="px-3 py-2 text-right font-semibold text-gray-600">Remaining</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-600">Remarks</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="entry in modalEntries" :key="entry.id" class="hover:bg-gray-50">
                      <td class="px-3 py-2 font-mono text-gray-700 text-xs">{{ entry.BankRef }}</td>
                      <td class="px-3 py-2 font-mono text-gray-700 text-xs">{{ entry.AcumaticaRef }}</td>
                      <td class="px-3 py-2 text-gray-600">{{ entry.Type }}</td>
                      <td class="px-3 py-2 text-right font-medium text-green-600">{{ formatPHP(entry.Amount) }}</td>
                      <td class="px-3 py-2 text-right text-gray-600">{{ formatPHP(entry.Remaining) }}</td>
                      <td class="px-3 py-2 text-gray-500 text-xs">{{ entry.Remarks ?? '—' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Approve/Disapprove in modal -->
              <div v-if="selectedGroup?.Status === 'Pending'" class="flex gap-2 mt-4 justify-end">
                <button
                  @click="startDisapprove(selectedGroup.MultipleID); closeModal();"
                  class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-md transition-all"
                >Disapprove</button>
                <button
                  @click="approveRecon(selectedGroup.MultipleID)"
                  :disabled="actionLoading"
                  class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-sm px-4 py-2 rounded-md transition-all"
                >{{ actionLoading ? 'Approving...' : 'Approve' }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AuthenticatedLayout>
</template>
