<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Settings } from 'lucide-vue-next';

const typeOptions = ['ALL', 'FUND_TRANSFER', 'VOUCHER', 'SOA', 'CASH_POSITION'];

const selectedType = ref('ALL');
const matrix = ref([]);
const loading = ref(false);
const showAddForm = ref(false);

const form = ref({
  type: 'FUND_TRANSFER',
  signatory: '',
  sequence: '',
  position_sequence: '',
  is_returnable: false,
});

const filteredMatrix = computed(() => {
  if (selectedType.value === 'ALL') return matrix.value;
  return matrix.value.filter((r) => r.Type === selectedType.value);
});

const groupedMatrix = computed(() => {
  const groups = {};
  filteredMatrix.value.forEach((row) => {
    if (!groups[row.Type]) groups[row.Type] = [];
    groups[row.Type].push(row);
  });
  return groups;
});

const fetchMatrix = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-approval-matrix');
    matrix.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const saveEntry = async () => {
  try {
    await axios.post('/save-approval-matrix', form.value);
    form.value = { type: 'FUND_TRANSFER', signatory: '', sequence: '', position_sequence: '', is_returnable: false };
    showAddForm.value = false;
    await fetchMatrix();
  } catch (err) {
    console.error(err);
  }
};

const deleteEntry = async (id) => {
  if (!confirm('Delete this entry?')) return;
  try {
    await axios.delete(`/delete-approval-matrix/${id}`);
    await fetchMatrix();
  } catch (err) {
    console.error(err);
  }
};

onMounted(fetchMatrix);
</script>

<template>
  <Head title="Approval Matrix" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5 flex-1">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Settings class="w-4 h-4 text-white" />
        </div>
        <div class="flex-1">
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Approval Matrix</h1>
          <p class="text-xs text-gray-400">Configure approval workflow levels</p>
        </div>
        <button
          @click="showAddForm = !showAddForm"
          class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg font-medium transition-all shadow-sm"
        >
          {{ showAddForm ? 'Cancel' : '+ Add Entry' }}
        </button>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6 space-y-6">

        <!-- Filter -->
        <div>
          <select
            v-model="selectedType"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          >
            <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>

        <!-- Add Form -->
        <div v-if="showAddForm" class="bg-gray-50 border border-gray-200 rounded-xl p-5">
          <h3 class="font-semibold text-gray-700 mb-4">New Approval Matrix Entry</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
              <select v-model="form.type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option v-for="t in typeOptions.filter(x => x !== 'ALL')" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Signatory</label>
              <input v-model="form.signatory" type="text" placeholder="Signatory name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Sequence</label>
              <input v-model="form.sequence" type="number" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Position Sequence</label>
              <input v-model="form.position_sequence" type="number" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <div class="flex items-end pb-2">
              <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                <input v-model="form.is_returnable" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-400" />
                Is Returnable
              </label>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button @click="saveEntry" class="bg-green-600 hover:bg-green-700 text-white text-sm px-5 py-2 rounded-md transition-all">
              Save Entry
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Grouped Tables -->
        <div v-else>
          <div v-if="Object.keys(groupedMatrix).length === 0" class="text-center py-12 text-gray-500">
            No approval matrix entries found.
          </div>
          <div v-for="(rows, type) in groupedMatrix" :key="type" class="mb-6">
            <div class="flex items-center gap-2 mb-2">
              <div class="h-5 w-1 bg-blue-600 rounded-full"></div>
              <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">{{ type }}</h3>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Sequence</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Signatory</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Position Sequence</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Is Returnable</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-700">{{ row.Sequence }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ row.Signatory }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ row.PositionSequence }}</td>
                    <td class="px-4 py-3 text-center">
                      <span :class="[
                        'px-2 py-1 rounded-full text-xs font-semibold',
                        row.IsReturnable
                          ? 'bg-green-100 text-green-700 border border-green-200'
                          : 'bg-gray-100 text-gray-600 border border-gray-200'
                      ]">
                        {{ row.IsReturnable ? 'Yes' : 'No' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <button
                        @click="deleteEntry(row.id)"
                        class="bg-red-100 hover:bg-red-200 text-red-700 text-xs px-3 py-1.5 rounded-md border border-red-200 transition-all"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
