<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import Swal from 'sweetalert2';
import { Search, Sheet, FileText, Link, Landmark, HandCoins, Combine, BanknoteArrowDown } from 'lucide-vue-next';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const props = defineProps({
  accounts: Array,
  depository: Array
});

const localAccounts = ref([...props.accounts]);

const headers = [
  { text: "Company", value: "Company" },
  { text: "Branch", value: "Branch" },
  { text: "Cash Account", value: "CashAccount" },
  { text: "Description", value: "Description" },
  { text: "Status", value: "IsActive" },
  { text: "Account Number", value: "AccountNo" }
];

const searchValue = ref('');

const filteredAccounts = computed(() =>
  localAccounts.value.filter(acc =>
    Object.values(acc).some(val =>
      val?.toString().toLowerCase().includes(searchValue.value.toLowerCase())
    )
  )
);

// 📦 Modal controls
const showModal = ref(false);
const selectedAccount = ref(null);
const AccountNo = ref(null);

const openModal = (account) => {
  selectedAccount.value = account;
  AccountNo.value = account.AccountNo || ''; 
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedAccount.value = null;
  AccountNo.value = null;
};

// 📤 Export to Excel
function exportExcel() {
  const ws = XLSX.utils.json_to_sheet(localAccounts.value);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'CashAccounts');
  XLSX.writeFile(wb, 'CashAccounts.xlsx');
}

// 📄 Export to PDF
function exportPDF() {
  const doc = new jsPDF();
  autoTable(doc, {
    head: [headers.map(h => h.text)],
    body: localAccounts.value.map(a => headers.map(h => a[h.value])),
  });
  doc.save('CashAccounts.pdf');
}

const linkAccount = async () => {
  if (!AccountNo.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Oops!',
      text: 'Please select an account to bind.',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  try {
    await axios.post('/bind-account', {
      cashAccount: selectedAccount.value.CashAccount,
      bindAccountNo: AccountNo.value,
    });

    const index = localAccounts.value.findIndex(acc => acc.CashAccount === selectedAccount.value.CashAccount);
    if (index !== -1) {
      localAccounts.value[index].AccountNo = AccountNo.value;
    }

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Account successfully bound!',
      confirmButtonColor: '#3085d6'
    });

    closeModal();
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: 'error',
      title: 'Failed!',
      text: 'Failed to bind account.',
      confirmButtonColor: '#d33'
    });
  }
};

const toggleAccountStatus = async (account) => {
  try {
    const newStatus = account.IsActive == 1 ? 0 : 1;

    await axios.post('/activate-deactivate', {
      cashAccount: account.CashAccount,
      status: newStatus
    });

    const index = localAccounts.value.findIndex(a => a.CashAccount === account.CashAccount);
    if (index !== -1) {
      localAccounts.value[index].IsActive = newStatus;
    }

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Account has been ${newStatus ? 'Activated' : 'Deactivated'}.`,
      confirmButtonColor: '#3085d6'
    });
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: 'error',
      title: 'Failed!',
      text: 'Could not change account status.',
      confirmButtonColor: '#d33'
    });
  }
};

const mergeAccounts = async () => {
  try {

    const confirmResult = await Swal.fire({
      title: "Merge Accounts?",
      text: "This will automatically match and merge cash accounts based on your rules.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, merge now",
      cancelButtonText: "Cancel",
    });

    if (!confirmResult.isConfirmed) return; 

    Swal.fire({
      title: "Merging accounts...",
      text: "Please wait while we process the merge.",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    const response = await axios.post("/merge-cashaccount");

    Swal.fire({
      title: "Merge Complete!",
      text: response.data.update,
      icon: "success",
      confirmButtonColor: "#3085d6",
    });
  } catch (error) {
    console.error("Merge failed:", error);

    Swal.fire({
      title: "Error",
      text: "Something went wrong while merging accounts.",
      icon: "error",
      confirmButtonColor: "#d33",
    });
  }
};

const apiBankCashAccount = async () => {
  try {
    Swal.fire({
      title: "Syncing Cash Accounts...",
      text: "Please wait while we fetch and update records.",
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      },
      width: 400,
    });

    const res = await axios.post("/api-cashaccount");
    const refreshed = await axios.get("/retrieve-cashaccount"); 
    localAccounts.value = refreshed.data;

    Swal.close();

    Swal.fire({
      title: "Sync Complete",
      text: res.data.update,
      icon: "success",
      width: 400,
      confirmButtonColor: "#3085d6",
    });
  } catch (err) {
    Swal.close();
    Swal.fire({
      title: "Sync Failed",
      text: err.response?.data?.error || err.message,
      icon: "error",
      width: 400,
    });
  }
};
</script>


<template>
  <Head title="Cash Account" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <HandCoins class="w-6 h-6 text-blue-600" />
            <h2 class="text-2xl font-bold text-gray-800">Bank Cash Account</h2>
        </div>
        <span class="text-sm text-gray-500">Manage your company’s active cash accounts</span>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center w-1/3 gap-3 mb-3 shadow-md p-4 bg-white rounded-lg">
            <div class="h-6 w-1.5 bg-blue-600 rounded-full"></div>
            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">
                Cash Account List
            </h3>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="apiBankCashAccount"
            class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded-md shadow-sm transition-all hover:scale-105 flex items-center gap-2 border border-green-200"
          >
            <BanknoteArrowDown class="w-5 h-8 text-green-600" />
          </button>
         <button
            @click="mergeAccounts"
            class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1 rounded-md shadow-sm transition-all hover:scale-105 flex items-center gap-2 border border-amber-200"
          >
            <Combine class="w-5 h-8 text-amber-600" />
          </button>
        </div>
       </div>


        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2">
                <Search class="w-5 h-8 text-gray-500" />
                <input
                    v-model="searchValue"
                    placeholder="Search..."
                    class="border border-gray-300 p-2 rounded-md w-4/4 focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm h-8"
                />
                  <button
                      @click="exportExcel"
                      class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded-md"
                  >
                      <Sheet class="w-4 h-4" />
                  </button>

                  <button
                      @click="exportPDF"
                      class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-2 rounded-md"
                  >
                      <FileText class="w-4 h-4" />
                  </button>
            </div>
        </div>

        <!-- 🧾 EasyDataTable -->
        <EasyDataTable
          :headers="headers"
          :items="filteredAccounts"
          alternating
          border-cell
          table-class-name="min-w-full"
          :rows-per-page="10"
        >
          
          <template #item-IsActive="item"> 
            <div class="flex justify-center items-center gap-2">
                <span
                :class="[ 
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    item.IsActive == 1
                    ? 'bg-green-100 text-green-700 border border-green-300'
                    : 'bg-red-100 text-red-700 border border-red-300'
                ]"
                >
                {{ item.IsActive == 1 ? 'Active' : 'Inactive' }}
                </span>

                <button
                @click="toggleAccountStatus(item)"
                class="p-1 rounded hover:bg-gray-100 transition-colors"
                :title="item.IsActive == 1 ? 'Deactivate' : 'Activate'"
                >
                <svg 
                    v-if="item.IsActive == 1" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 text-red-500" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 text-green-500" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                </button>
            </div>
            </template>


            <template #item-AccountNo="item"> 
            <div class="flex justify-center">
                <template v-if="item.AccountNo">
                <div class="flex items-center gap-1">
                    <Link class="w-3 h-3" />
                    <span>{{ item.AccountNo }}</span>
                </div>
                </template>

                <template v-else>
                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-2 rounded-md shadow-sm transition-all hover:scale-105"
                    @click="openModal(item)"
                >
                    <Link class="w-3 h-3" />
                </button>
                </template>
            </div>
            </template>

        </EasyDataTable>
      </div>

      <!-- 🪟 MODAL -->
      <transition name="fade">
        <div
          v-if="showModal"
          class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
        >
          <div class="bg-white rounded-xl shadow-2xl p-8 w-[28rem] animate-fadeIn">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Account Details</h3>

            <div v-if="selectedAccount" class="space-y-2 text-sm text-gray-700">
              <p><strong>🏢 Company:</strong> {{ selectedAccount.Company }}</p>
              <p><strong>🏬 Branch:</strong> {{ selectedAccount.Branch }}</p>
              <p><strong>💰 Cash Account:</strong> {{ selectedAccount.CashAccount }}</p>
              <p><strong>📝 Description:</strong> {{ selectedAccount.Description }}</p>
              <p>
                <strong>🔒 Status:</strong>
                <span
                  :class="[ 
                    'ml-1 px-2 py-0.5 rounded-full text-xs font-semibold',
                    selectedAccount.IsActive == 1
                      ? 'bg-green-100 text-green-700 border border-green-300'
                      : 'bg-red-100 text-red-700 border border-red-300'
                  ]"
                >
                  {{ selectedAccount.IsActive == 1 ? 'Active' : 'Inactive' }}
                </span>
              </p>

                <div class="mt-4">
                    <label for="bindSelect" class="block text-gray-700 font-small mb-1">Select Account to Link:</label>
                   <select v-model="AccountNo" class="w-full border rounded p-2">
                        <option value="" disabled>Select an Account</option>
                        <option v-for="bank in depository" :key="bank.RecID" :value="bank.AccountNo">
                            {{ bank.AccountNo }} - {{ bank.AccountName }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
            <button
                @click="linkAccount"
                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 mr-1 rounded-md transition-all"
                >
                Bind
            </button>
              <button
                @click="closeModal"
                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md transition-all"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AuthenticatedLayout>
</template>
