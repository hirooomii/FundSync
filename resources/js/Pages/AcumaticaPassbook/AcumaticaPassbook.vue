<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { BookOpen } from 'lucide-vue-next';

const filterBankName = ref('');
const filterCompany = ref('');
const filterAccountNo = ref('');
const filterDateFrom = ref('');
const filterDateTo = ref('');

const entries = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const showNoteModal = ref(false);
const noteTarget = ref(null);
const newNote = ref('');
const noteHistory = ref([]);
const loadingNotes = ref(false);
const savingNote = ref(false);
const alertMessage = ref('');
const alertType = ref('');

const formatPHP = (amount) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount ?? 0);

const fetchEntries = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-passbook-entries', {
      params: {
        bank_name: filterBankName.value,
        company: filterCompany.value,
        account_no: filterAccountNo.value,
        date_from: filterDateFrom.value,
        date_to: filterDateTo.value,
      },
    });
    entries.value = res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const paginatedEntries = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return entries.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(entries.value.length / perPage));

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text).then(() => {
    alertMessage.value = 'Copied to clipboard!';
    alertType.value = 'green';
    setTimeout(() => { alertMessage.value = ''; }, 2000);
  });
};

const openNoteModal = async (entry) => {
  noteTarget.value = entry;
  newNote.value = '';
  noteHistory.value = [];
  showNoteModal.value = true;
  loadingNotes.value = true;
  try {
    const res = await axios.get('/get-passbook-comments', {
      params: { transaction_id: entry.transaction_id },
    });
    noteHistory.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loadingNotes.value = false;
  }
};

const closeNoteModal = () => {
  showNoteModal.value = false;
  noteTarget.value = null;
  newNote.value = '';
  noteHistory.value = [];
};

const saveNote = async () => {
  if (!newNote.value.trim()) return;
  savingNote.value = true;
  try {
    await axios.post('/save-passbook-note', {
      transaction_id: noteTarget.value.transaction_id,
      note: newNote.value,
    });
    alertMessage.value = 'Note saved successfully.';
    alertType.value = 'green';
    newNote.value = '';
    const res = await axios.get('/get-passbook-comments', {
      params: { transaction_id: noteTarget.value.transaction_id },
    });
    noteHistory.value = res.data;
  } catch (err) {
    alertMessage.value = 'Failed to save note.';
    alertType.value = 'red';
    console.error(err);
  } finally {
    savingNote.value = false;
    setTimeout(() => { alertMessage.value = ''; }, 3000);
  }
};

onMounted(fetchEntries);
</script>

<template>
  <Head title="Acumatica Passbook" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <BookOpen class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Acumatica Passbook</h1>
          <p class="text-xs text-gray-400">Browse Acumatica journal entries by cash account</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Alert -->
        <div
          v-if="alertMessage"
          :class="[
            'mb-4 px-4 py-3 rounded-md text-sm font-medium',
            alertType === 'green' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'
          ]"
        >
          {{ alertMessage }}
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap gap-3 mb-5">
          <input
            v-model="filterBankName"
            placeholder="Bank Name"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
          <input
            v-model="filterCompany"
            placeholder="Company"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
          <input
            v-model="filterAccountNo"
            placeholder="Account No"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
          <input
            v-model="filterDateFrom"
            type="date"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
          <input
            v-model="filterDateTo"
            type="date"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          />
          <button
            @click="fetchEntries"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-all"
          >Fetch</button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="entries.length === 0" class="text-center py-12 text-gray-500">
            No passbook entries found. Adjust filters and click Fetch.
          </div>
          <div v-else>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Journal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doc Ref</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="e in paginatedEntries" :key="e.transaction_id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.Company }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ formatPHP(e.Amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.Type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="flex items-center gap-1">
                        <span>{{ e.Reference }}</span>
                        <button
                          @click="copyToClipboard(e.Reference)"
                          title="Copy to clipboard"
                          class="text-gray-400 hover:text-blue-500 transition-colors"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                        </button>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.Journal }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.TransactionDate }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.CashAccount }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ e.Description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ e.DocRef }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ e.Note }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <button
                        @click="openNoteModal(e)"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md transition-all"
                      >Add Note</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
              <span>Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, entries.length) }} of {{ entries.length }} records</span>
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

    <!-- Add Note Modal -->
    <div v-if="showNoteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" @click.self="closeNoteModal">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">Add Note — {{ noteTarget?.Reference }}</h3>
          <button @click="closeNoteModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">New Note</label>
          <textarea
            v-model="newNote"
            rows="3"
            placeholder="Enter your note..."
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"
          ></textarea>
          <button
            @click="saveNote"
            :disabled="savingNote || !newNote.trim()"
            class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-all disabled:opacity-50"
          >{{ savingNote ? 'Saving...' : 'Save Note' }}</button>
        </div>

        <h4 class="font-semibold text-gray-700 mb-2 border-t pt-3">Note History</h4>
        <div v-if="loadingNotes" class="flex justify-center py-4">
          <div class="animate-spin h-6 w-6 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>
        <div v-else-if="noteHistory.length === 0" class="text-sm text-gray-500">No notes yet.</div>
        <ul v-else class="space-y-2">
          <li v-for="(n, i) in noteHistory" :key="i" class="bg-gray-50 rounded-md px-3 py-2 text-sm border border-gray-200">
            <div class="text-gray-500 text-xs mb-1">{{ n.created_at }} — {{ n.created_by }}</div>
            <div class="text-gray-800">{{ n.note }}</div>
          </li>
        </ul>

        <div class="flex justify-end mt-4">
          <button @click="closeNoteModal" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm transition-all">Close</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
