<template>
  <transition name="modal">
    <div v-if="isVisible" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>
        
        <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
          <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pending Online Payment Details</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="mt-4">
            <EasyDataTable
              :headers="headers"
              :items="items"
              :rows-per-page="5"
              :loading="loading"
              buttons-pagination
              class="customize-table"
            >
              <template #item-Amount="{ Amount }">
                <span class="text-sm">₱{{ parseFloat(Amount).toLocaleString() }}</span>
              </template>
            </EasyDataTable>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

const isVisible = ref(false);
const loading = ref(false);
const items = ref([]);

const headers = [
  { text: 'Account No.', value: 'AccountNumber', sortable: true },
  { text: 'ECPF No', value: 'EcpfNo', sortable: true },
  { text: 'Amount', value: 'Amount', sortable: true },
  { text: 'Check Date', value: 'C_CheckDate', sortable: true },
  { text: 'Payee Name', value: 'PayeeName', sortable: true }
];

const show = (checksData) => {
  items.value = checksData;
  isVisible.value = true;
};

const close = () => {
  isVisible.value = false;
};

defineExpose({ show, close });
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.customize-table {
  --easy-table-header-font-size: 13px;
  --easy-table-header-height: 45px;
  --easy-table-header-background-color: #f9fafb;
  --easy-table-body-row-font-size: 13px;
  --easy-table-body-row-height: 45px;
  --easy-table-body-row-hover-background-color: #f3f4f6;
  --easy-table-border: 1px solid #e5e7eb;
}
</style>