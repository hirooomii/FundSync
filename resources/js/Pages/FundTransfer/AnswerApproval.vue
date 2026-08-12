<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { CheckSquare } from 'lucide-vue-next';

const queue = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const showDisapproveModal = ref(false);
const disapproveTarget = ref(null);
const disapproveReason = ref('');
const actionLoading = ref(false);
const alertMessage = ref('');
const alertType = ref('');

const fetchQueue = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-approval-queue');
    queue.value = res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const paginatedQueue = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return queue.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(queue.value.length / perPage));

const handleApprove = async (row) => {
  if (!confirm(`Approve request ${row.Reference}?`)) return;
  actionLoading.value = true;
  try {
    await axios.post('/approve-request', { ftid: row.ftid, sequence_id: row.sequence_id });
    alertMessage.value = `Request ${row.Reference} approved successfully.`;
    alertType.value = 'green';
    await fetchQueue();
  } catch (err) {
    alertMessage.value = 'Failed to approve request.';
    alertType.value = 'red';
    console.error(err);
  } finally {
    actionLoading.value = false;
    setTimeout(() => { alertMessage.value = ''; }, 4000);
  }
};

const openDisapproveModal = (row) => {
  disapproveTarget.value = row;
  disapproveReason.value = '';
  showDisapproveModal.value = true;
};

const closeDisapproveModal = () => {
  showDisapproveModal.value = false;
  disapproveTarget.value = null;
  disapproveReason.value = '';
};

const handleDisapprove = async () => {
  if (!disapproveReason.value.trim()) {
    alert('Please provide a reason for disapproval.');
    return;
  }
  actionLoading.value = true;
  try {
    await axios.post('/disapprove-request', {
      ftid: disapproveTarget.value.ftid,
      sequence_id: disapproveTarget.value.sequence_id,
      reason: disapproveReason.value,
    });
    alertMessage.value = `Request ${disapproveTarget.value.Reference} disapproved.`;
    alertType.value = 'red';
    closeDisapproveModal();
    await fetchQueue();
  } catch (err) {
    alertMessage.value = 'Failed to disapprove request.';
    alertType.value = 'red';
    console.error(err);
  } finally {
    actionLoading.value = false;
    setTimeout(() => { alertMessage.value = ''; }, 4000);
  }
};

onMounted(fetchQueue);
</script>

<template>
  <Head title="Answer Approval" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <CheckSquare class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Answer Approval</h1>
          <p class="text-xs text-gray-400">Respond to fund transfer approval requests</p>
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

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="queue.length === 0" class="text-center py-12 text-gray-500">
            No pending approvals found.
          </div>
          <div v-else>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Your Position</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="r in paginatedQueue" :key="r.SerialNo" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ r.SerialNo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Reference }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Company }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Date }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.CreatedBy }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                        Approver #{{ r.sequence_id }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <div class="flex justify-center gap-2">
                        <button
                          @click="handleApprove(r)"
                          :disabled="actionLoading"
                          class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1.5 rounded-md transition-all disabled:opacity-50"
                        >Approve</button>
                        <button
                          @click="openDisapproveModal(r)"
                          :disabled="actionLoading"
                          class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-md transition-all disabled:opacity-50"
                        >Disapprove</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
              <span>Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, queue.length) }} of {{ queue.length }} records</span>
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

    <!-- Disapprove Modal -->
    <div v-if="showDisapproveModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" @click.self="closeDisapproveModal">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">Disapprove Request — {{ disapproveTarget?.Reference }}</h3>
          <button @click="closeDisapproveModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <p class="text-sm text-gray-600 mb-3">Please provide a reason for disapproving this request.</p>

        <textarea
          v-model="disapproveReason"
          rows="4"
          placeholder="Enter reason for disapproval..."
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none resize-none"
        ></textarea>

        <div class="flex justify-end gap-3 mt-4">
          <button @click="closeDisapproveModal" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm transition-all">Cancel</button>
          <button
            @click="handleDisapprove"
            :disabled="actionLoading"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm transition-all disabled:opacity-50"
          >Confirm Disapprove</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
