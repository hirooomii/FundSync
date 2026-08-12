<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { ScrollText } from 'lucide-vue-next';

const search = ref('');
const logs = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const draw = ref(1);

const fetchLogs = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/get-remark-logs', {
      draw: draw.value++,
      start: 0,
      length: 50,
      search: { value: search.value },
    });
    logs.value = res.data.data ?? res.data;
    totalRecords.value = res.data.recordsTotal ?? logs.value.length;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const filteredLogs = computed(() => {
  if (!search.value) return logs.value;
  const q = search.value.toLowerCase();
  return logs.value.filter(
    (l) =>
      String(l.log_id ?? '').toLowerCase().includes(q) ||
      String(l.previous_note ?? '').toLowerCase().includes(q) ||
      String(l.account_no ?? '').toLowerCase().includes(q) ||
      String(l.description ?? '').toLowerCase().includes(q) ||
      String(l.user ?? '').toLowerCase().includes(q)
  );
});

onMounted(fetchLogs);
</script>

<template>
  <Head title="Remark Logs" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <ScrollText class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Remark Logs</h1>
          <p class="text-xs text-gray-400">History of remarks and annotations</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5 items-center justify-between">
          <input
            v-model="search"
            @input="fetchLogs"
            placeholder="Search logs..."
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none w-72"
          />
          <span class="text-sm text-gray-500">Total Records: <strong>{{ totalRecords }}</strong></span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Table -->
        <div v-else>
          <div v-if="filteredLogs.length === 0" class="text-center py-12 text-gray-500">
            No remark log records found.
          </div>
          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                <tr>
                  <th class="px-4 py-3 text-left">Log ID</th>
                  <th class="px-4 py-3 text-left">Previous Note</th>
                  <th class="px-4 py-3 text-left">Account No</th>
                  <th class="px-4 py-3 text-left">Description</th>
                  <th class="px-4 py-3 text-left">User</th>
                  <th class="px-4 py-3 text-left">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="log in filteredLogs"
                  :key="log.log_id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3 font-mono text-gray-700">{{ log.log_id }}</td>
                  <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ log.previous_note ?? '—' }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ log.account_no }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ log.description }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ log.user }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ log.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
