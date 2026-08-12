<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { FileText } from 'lucide-vue-next';

const companies = [
  'ROPALI CORPORATION',
  'MOTORBELLE CORPORATION',
  'MOTORALI CORPORATION',
  'MOTOROBEE CORPORATION',
];

const selectedCompany = ref('');
const requests = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = 15;

const fetchRequests = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-ft-requests', {
      params: { company: selectedCompany.value },
    });
    requests.value = res.data;
    currentPage.value = 1;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const paginatedRequests = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return requests.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(requests.value.length / perPage));

const getStatusBadge = (row) => {
  if (row.IsFullyApproved == 1) {
    return { label: 'Approved', class: 'bg-green-100 text-green-700 border border-green-200' };
  }
  if (row.IsDisapproved == 1) {
    return { label: 'Disapproved', class: 'bg-red-100 text-red-700 border border-red-200' };
  }
  return { label: 'On Process', class: 'bg-yellow-100 text-yellow-700 border border-yellow-200' };
};

onMounted(fetchRequests);
</script>

<template>
  <Head title="Fund Transfer Requests" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <FileText class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">FT Request</h1>
          <p class="text-xs text-gray-400">Fund transfer request submissions</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
          <select
            v-model="selectedCompany"
            @change="fetchRequests"
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
          <div v-if="requests.length === 0" class="text-center py-12 text-gray-500">
            No fund transfer request records found.
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
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="r in paginatedRequests" :key="r.SerialNo" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ r.SerialNo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Reference }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Company }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.Date }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ r.CreatedBy }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusBadge(r).class]">
                        {{ getStatusBadge(r).label }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
              <span>Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, requests.length) }} of {{ requests.length }} records</span>
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
