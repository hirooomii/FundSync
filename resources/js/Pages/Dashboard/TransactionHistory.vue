<template>
  <transition name="modal">
    <div v-if="isVisible" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>
        
        <div class="inline-block w-full max-w-6xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
          <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">List of Transactions</h3>
            <div class="flex items-center gap-3">
              <button 
                @click="exportToExcel" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
              >
                <Sheet class="w-4 h-4 mr-2" />
                Export Excel
              </button>
              <button 
                @click="exportToPDF" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
              >
                <FileText class="w-4 h-4 mr-2" />
                Export PDF
              </button>
              <button @click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <div class="mt-4">
            <div class="mb-4">
              <input
                v-model="searchValue"
                type="text"
                placeholder="Search transactions..."
                class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            
            <EasyDataTable
              :headers="headers"
              :items="items"
              :search-value="searchValue"
              :rows-per-page="10"
              :loading="loading"
              buttons-pagination
              class="customize-table"
            >
              <template #item-amount="{ amount }">
                <span class="text-sm font-medium">₱{{ parseFloat(amount).toLocaleString() }}</span>
              </template>
              
              <template #item-Category="{ Category }">
                <span :class="getCategoryClass(Category)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ Category }}
                </span>
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
import { Sheet, FileText } from 'lucide-vue-next';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import axios from 'axios';

const isVisible = ref(false);
const loading = ref(false);
const items = ref([]);
const searchValue = ref('');
const currentAccount = ref('');

const headers = [
  { text: 'Date', value: 'transaction_date', sortable: true },
  { text: 'Bank', value: 'bank_code', sortable: true },
  { text: 'Transaction Type', value: 'transaction_type', sortable: true },
  { text: 'Category', value: 'Category', sortable: true },
  { text: 'Amount', value: 'amount', sortable: true },
  { text: 'Branch Code', value: 'BranchCode', sortable: true },
  { text: 'Branch Name', value: 'BranchName', sortable: true }
];

const show = async (accountNo) => {
  currentAccount.value = accountNo;
  isVisible.value = true;
  loading.value = true;
  
  try {
    const response = await axios.post('/api/cms/bank-details/transaction-history', {
      account: accountNo,
      start: 0,
      length: 1000,
      search: { value: '' }
    });
    
    items.value = response.data.data || [];
  } catch (error) {
    console.error('Error fetching transaction history:', error);
    items.value = [];
  } finally {
    loading.value = false;
  }
};

const close = () => {
  isVisible.value = false;
  searchValue.value = '';
  items.value = [];
};

const getCategoryClass = (category) => {
  const classes = {
    'Credit': 'bg-green-100 text-green-800',
    'Debit': 'bg-red-100 text-red-800',
    'Deposit': 'bg-blue-100 text-blue-800',
    'Others': 'bg-gray-100 text-gray-800'
  };
  return classes[category] || classes['Others'];
};

const exportToExcel = () => {
  const exportData = items.value.map(item => ({
    'Date': item.transaction_date,
    'Bank': item.bank_code,
    'Transaction Type': item.transaction_type,
    'Category': item.Category,
    'Amount': parseFloat(item.amount),
    'Branch Code': item.BranchCode,
    'Branch Name': item.BranchName
  }));

  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Transactions');
  
  XLSX.writeFile(workbook, `Transaction_History_${currentAccount.value}_${new Date().toISOString().split('T')[0]}.xlsx`);
};

const exportToPDF = () => {
  const doc = new jsPDF('l', 'mm', 'a4');
  
  doc.setFontSize(16);
  doc.text(`Transaction History - Account: ${currentAccount.value}`, 14, 15);
  
  const tableData = items.value.map(item => [
    item.transaction_date,
    item.bank_code,
    item.transaction_type,
    item.Category,
    `₱${parseFloat(item.amount).toLocaleString()}`,
    item.BranchCode,
    item.BranchName
  ]);

  autoTable(doc, {
    head: [['Date', 'Bank', 'Transaction Type', 'Category', 'Amount', 'Branch Code', 'Branch Name']],
    body: tableData,
    startY: 25,
    theme: 'striped',
    styles: { fontSize: 8 },
    headStyles: { fillColor: [59, 130, 246] }
  });

  doc.save(`Transaction_History_${currentAccount.value}_${new Date().toISOString().split('T')[0]}.pdf`);
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