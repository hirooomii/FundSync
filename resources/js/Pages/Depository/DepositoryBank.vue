<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BankDetails from '@/Pages/Depository/BankDetails.vue';
import BankSignatories from '@/Pages/Depository/BankSignatories.vue';
import BankCashAccount from '@/Pages/Depository/BankCashAccount.vue';
import BankTransaction from '@/Pages/Depository/BankTransaction.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import Swal from 'sweetalert2';
import { Landmark, Link, PackagePlus, ScanEye, Info, User, FileText, Wallet, History, FileCog } from 'lucide-vue-next';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

// Import all bank-specific SOA components
import BankSOABPO from './Parser/BankSOABPO.vue';
import BankSOALBP from './Parser/BankSOALBP.vue';
import BankSOAPBCOM from './Parser/BankSOAPBCOM.vue';
import BankSOAPNB from './Parser/BankSOAPNB.vue';
import BankSOAUB from './Parser/BankSOAUB.vue';
import BankSOABPI from './Parser/BankSOABPI.vue';
import BankSOAAGRI from './Parser/BankSOAAGRI.vue';
import BankSOAAUB from './Parser/BankSOAAUB.vue';
import BankSOA from './BankSOA.vue';

const props = defineProps({
  depository: Array,
  banks: {
    type: Array,
    default: () => []
  },
  accountType: {
    type: Array,
    default: () => []
  },
  accountTag: {
    type: Array,
    default: () => []
  },
  company: {
    type: Array,
    default: () => []
  }
});

const depoBank = ref({
  BankName: '',
  DepositType: '',
  AccountTag: '',
  Company: '',
})

const bankOptions = computed(() =>
  props.banks.map(bank => ({
    label: bank.Bank,          
    value: bank.Abbreviation  
  }))
)

const companyList = computed(() =>
  props.company.map(com => ({
    label: com.Company,          
    value: com.Company  
  }))
)

const accountType = computed(() =>
  props.accountType.map(type => ({
    label: `${type.AccountType} (${type.Abbreviation})`,         
    value: type.AccountType  
  }))
)

const accountTag = computed(() =>
  props.accountTag.map(tag => ({
    label: `${tag.AccountTag} (${tag.Abbreviation})`,         
    value: tag.AccountTag  
  }))
)

const depository = ref([...props.depository])

const companies = computed(() => [...new Set(depository.value.map(d => d.Company))])

const activeCompany = ref(companies.value[0] ?? null)

const banks = computed(() => {
  const filtered = depository.value.filter(d => d.Company === activeCompany.value)
  return [...new Set(filtered.map(d => d.BankName))]
})

const activeBank = ref(null);
const bankParser = ref(null);

const headers = [
  { text: 'Company', value: 'Company' },
  { text: 'Bank', value: 'BankName' },
  { text: 'Account Number', value: 'AccountNo' },
  { text: 'Account Name', value: 'AccountName' },
  { text: 'Type', value: 'DepositType' },
  { text: 'Tag', value: 'AccountTag' },
  { text: 'Available Balance', value: 'BeginningBal' },
  { text: 'Maintaining Balance', value: 'MaintainingBal' },
  { text: 'Status', value: 'Status' },
];

const filteredDepository = computed(() => {
  return depository.value.filter(item => {
    const matchCompany = item.Company === activeCompany.value
    const matchBank = activeBank.value ? item.BankName === activeBank.value : true
    return matchCompany && matchBank
  })
})

watch(activeCompany, () => {
  activeBank.value = null;
});

const getBankLogo = (bankName) => {
  const domainMap = {
    'AGRIBANK': 'agribank.com.vn',
    'BDO': 'bdo.com.ph',
    'BPI': 'bpi.com.ph',
    'LANDBANK': 'landbank.com',
    'PNB': 'pnb.com.ph',
    'UNIONBANK': 'unionbankph.com',
    'METROBANK': 'metrobank.com.ph',
    'PSBANK': 'psbank.com.ph',
  }

  const domain = domainMap[bankName?.toUpperCase()] || 'google.com'
  return `https://www.google.com/s2/favicons?domain=${domain}&sz=64`
}

const updateStatus = async (bank) => {
  try {
    const newStatus = bank.Status == 'ACTIVE' ? 'INACTIVE': 'ACTIVE';

    await axios.post('/bank-status', {
      id: bank.RecID,
      status: newStatus
    });

    await fetchDepositoryData();

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Account has been ${newStatus == 'ACTIVE' ? 'Activated' : 'Deactivated'}.`,
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
}

const saveDepositoryBank = async () => {
  depoBank.value.processing = true;
    try {
      const url = isEditMode.value ? '/update-depository' : '/create-depository';
      const response = await axios.post(url, depoBank.value);

      await fetchDepositoryData();

      Swal.fire({
        icon: "success",
        title: "Saved!",
        text: response.data.message,
      });

      closeBankModal();
    } catch (error) {
      console.error(error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Please check your inputs or try again later.",
      });
    } finally {
      depoBank.value.processing = false;
    }
}

const fetchDepositoryData = async () => {
  const res = await axios.get('/retrieve-depository');
  depository.value = res.data; 
};

// 📦 Modal controls
const AddBankModal = ref(false);
const BankDetailsModal = ref(false);
const ReferenceNumber = ref(null);
const isEditMode = ref(false); 

const openBankModal = () => {
  isEditMode.value = false;
   depoBank.value = {
    BankName: '',
    DepositType: '',
    AccountTag: '',
    Company: '',
  };
  AddBankModal.value = true;
};

const openEditBankModal = (bank) => {
  isEditMode.value = true;
  depoBank.value = { ...bank }; 
  AddBankModal.value = true;
};

const closeBankModal = () => {
  AddBankModal.value = false;
};

const openBankDetails = (accountNo, bankName) => {
  ReferenceNumber.value = accountNo;
  bankParser.value = bankName;
  BankDetailsModal.value = true;

}

const closeBankDetails = () => {
  BankDetailsModal.value = false
}

const bankSOAComponent = computed(() => {
  if (!bankParser.value) return BankSOA; 
  switch (bankParser.value.toUpperCase()) {
    case 'AGRIBANK':
      return BankSOAAGRI;
    case 'AUB':
      return BankSOAAUB;
    case 'BPI':
      return BankSOABPI;
    case 'BDO':
      return BankSOABPO;
    case 'UNIONBANK':
      return BankSOAUB;
    case 'LANDBANK':
      return BankSOALBP;
    case 'PBCOM':
      return BankSOAPBCOM;
    case 'PNB':
      return BankSOAPNB;
    default:
      return BankSOA; 
  }
});

const tabs = [
  { key: 'BankDetails', label: 'Bank Information', icon: Info, component: BankDetails },
  { key: 'BankSignatories', label: 'Bank Signatories', icon: User, component: BankSignatories },
  { key: 'BankSOA', label: 'Statement of Account', icon: FileText, component: () => bankSOAComponent.value },
  { key: 'BankCashAccount', label: 'Cash Accounts', icon: Wallet, component: BankCashAccount },
  { key: 'BankTransaction', label: 'Transaction History', icon: History, component: BankTransaction },
]

const activeTab = ref('BankDetails')

const activeTabComponent = computed(() => {
  const tab = tabs.find(t => t.key === activeTab.value)
  if (!tab) return null
  return typeof tab.component === 'function' ? tab.component() : tab.component
});


</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>


<template>
  <Head title="Depository Bank" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <Landmark class="w-6 h-6 text-blue-600" />
          <h2 class="text-2xl font-bold text-gray-800">Depository Bank</h2>
        </div>
         <div class="flex items-center space-x-3">
          <span class="text-sm text-gray-500">Manage your company’s bank depositories</span>
          <button
            @click="openBankModal"
            class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm transition-all"
          >
            <PackagePlus class="w-4 h-4" /> Bank
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <div class="bg-gray-50 rounded-t-2xl p-2 border-b border-gray-200 mb-6 flex flex-wrap gap-2">
          <button
            v-for="company in companies"
            :key="company"
            @click="activeCompany = company"
            :class="[
              'relative px-5 py-2 text-sm font-semibold transition-all duration-200 rounded-t-xl',
              activeCompany === company
                ? 'bg-white text-blue-600 shadow-md border-b-2 border-blue-600 -mb-[2px]'
                : 'bg-gray-50 text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:bg-gray-100'
            ]"
          >
            {{ company }}
          </button>
        </div>

        <div class="flex gap-4">
          <div class="w-48 border-r border-gray-200 pr-3">
            <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase">Banks</h3>
            <div class="flex flex-col gap-2">
              <button
                v-for="bank in banks"
                :key="bank"
                @click="activeBank = bank"
                :class="[
                  'w-full text-left px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  activeBank === bank
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                ]"
              >
                <div class="flex items-center space-x-2">
                <img
                  :src="getBankLogo(bank)"
                  alt="bank logo"
                  class="w-5 h-5 rounded-sm object-contain"
                  loading="lazy"
                  @error="(e) => e.target.style.display = 'none'"
                />
                <span>{{ bank }}</span>
              </div>
              </button>
            </div>
          </div>

          <div class="flex-1">
            <EasyDataTable
              :headers="headers"
              :items="filteredDepository"
              class="shadow-sm rounded-lg"
            >
              <template #item-BeginningBal="{ BeginningBal }">
                ₱{{ Number(BeginningBal || 0).toLocaleString() }}
              </template>
              <template #item-MaintainingBal="{ MaintainingBal }">
                ₱{{ Number(MaintainingBal || 0).toLocaleString() }}
              </template>

              <template #item-Company="{ Company }">
                <span>
                  {{
                    company.find(c => c.Company === Company)?.Abbreviation || Company
                  }}
                </span>
              </template>

            <template  #item-Status="item">
                 <div class="flex items-center justify-between">
                <span
                  :class="[
                    'px-2 py-1 rounded text-xs font-semibold',
                    item.Status?.toLowerCase() === 'active'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-red-100 text-red-700'
                  ]"
                >
                  {{ item.Status }}
                </span>
                <button
                  @click="updateStatus(item)"
                  class="p-1 rounded hover:bg-gray-100 transition-colors ml-2"
                  :title="item.Status?.toLowerCase() === 'active' ? 'Deactivate' : 'Activate'"
                >
                  <svg
                    v-if="item.Status?.toLowerCase() === 'active'"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 text-red-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>

                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 text-green-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </button>
               </div>
            </template>
            
           <template #item-AccountNo="item">
            <div class="flex items-center justify-between">

              <span class="font-medium text-gray-800 tracking-wide">
                {{ item.AccountNo }}
              </span>

              <div class="flex items-center gap-1.5">
                <button
                  @click="openBankDetails(item.AccountNo, item.BankName)"
                  class="p-1.5 rounded-lg hover:bg-blue-50 transition-colors"
                  title="View Details"
                >
                  <ScanEye class="w-5 h-5 text-blue-600 hover:text-blue-700 transition-transform hover:scale-110" />
                </button>

                <button
                  @click="openEditBankModal(item)"
                  class="p-1.5 rounded-lg hover:bg-blue-50 transition-colors"
                  title="Edit Account"
                >
                  <FileCog class="w-5 h-5 text-blue-600 hover:text-blue-700 transition-transform hover:scale-110" />
                </button>
              </div>
            </div>
          </template>


            </EasyDataTable>
          </div>
        </div>
      </div>
    </div>

  <transition name="fade">
      <div
        v-if="AddBankModal"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
      >
        <div
          class="bg-white rounded-xl shadow-2xl p-6 w-[70rem] max-h-[90vh] overflow-y-auto animate-fadeIn"
        >
          <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">
            🏦 New Bank Account
          </h3>

          <!-- FORM -->
          <div class="grid grid-cols-3 gap-4">
            <!-- BankName -->
            <div>
              <label class="block text-sm font-semibold text-gray-700">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <select v-model="depoBank.BankName" class="w-full border rounded-md p-2">
                <option value="">Select Bank</option>
                <option
                  v-for="opt in bankOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- DepositType -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Account Type <span class="text-red-500">*</span></label
              >
              <select
                v-model="depoBank.DepositType" class="w-full border rounded-md p-2"
              >
                <option value="">Select Type</option>
                <option
                  v-for="opt in accountType"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- Account Tag -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Account Tag <span class="text-red-500">*</span></label
              >
              <select v-model="depoBank.AccountTag" class="w-full border rounded-md p-2">
                <option value="">Select Tag</option>
                <option
                  v-for="opt in accountTag"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- Company -->
             <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Company <span class="text-red-500">*</span></label
              >
              <select v-model="depoBank.Company" class="w-full border rounded-md p-2">
                <option value="">Select Company</option>
                <option
                  v-for="opt in companyList"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>


            <!-- Account Name -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Account Name <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.AccountName"
                placeholder="Account Name"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- Account No -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Account Number <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.AccountNo"
                placeholder="Account Number"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- InterestRate -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Interest Rate <span class="text-red-500">*</span></label
              >
              <input
                type="number"
                v-model="depoBank.InterestRate"
                step="0.01"
                min="0"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- BeginningBal -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Beginning Balance <span class="text-red-500">*</span></label
              >
              <input
                type="number"
                v-model="depoBank.BeginningBal"
                step="0.01"
                min="0"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- MaintainingBal -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Maintaining Balance <span class="text-red-500">*</span></label
              >
              <input
                type="number"
                v-model="depoBank.MaintainingBal"
                step="0.01"
                min="0"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- Bank Branch -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Bank Branch <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.BankBranch"
                placeholder="Branch"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- Street -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Bank Street <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.BankStreet"
                placeholder="Street"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- City -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Bank City <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.BankCity"
                placeholder="City"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- Province -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Bank Province <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.BankProvince"
                placeholder="Province"
                class="w-full border rounded-md p-2"
              />
            </div>

            <!-- Dates -->
            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Date Opened <span class="text-gray-500">(optional)</span></label
              >
              <input type="date" v-model="depoBank.DateOpen" class="w-full border rounded-md p-2" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Maturity Date
                <span class="text-gray-500">(for Time Deposit)</span></label
              >
              <input type="date" v-model="depoBank.MaturityDate" class="w-full border rounded-md p-2" />
            </div>

            <!-- Purpose -->
            <div class="col-span-3">
              <label class="block text-sm font-semibold text-gray-700"
                >Purpose <span class="text-red-500">*</span></label
              >
              <textarea
                v-model="depoBank.Description"
                rows="3"
                placeholder="State the Purpose"
                class="w-full border rounded-md p-2"
              ></textarea>
            </div>

            <!-- Contact Info Header -->
            <div class="col-span-3 border-t pt-3">
              <p class="font-semibold text-gray-600 text-sm mb-2">
                Depository Bank Contact Details
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Contact Number <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.DepContactNum"
                placeholder="Contact Number"
                class="w-full border rounded-md p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Contact Person <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.DepContactPerson"
                placeholder="Contact Person"
                class="w-full border rounded-md p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Email Address <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.DepEmailAdd"
                placeholder="Email Address"
                class="w-full border rounded-md p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700"
                >Department Position <span class="text-red-500">*</span></label
              >
              <input
                v-model="depoBank.DepPosition"
                placeholder="Department Position"
                class="w-full border rounded-md p-2"
              />
            </div>
          </div>

          <!-- Footer -->
          <div class="mt-6 flex justify-end">
            <button
              @click="saveDepositoryBank"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 mr-2 rounded-md transition-all disabled:opacity-60"
              :disabled="depoBank.processing"
            >
              {{ depoBank.processing ? "Saving..." : "Save" }}
            </button>
            <button
              @click="closeBankModal"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-all"
            >
              Close
            </button>
          </div>
        </div>
      </div>
  </transition>

    <transition name="fade">
    <div
      v-if="BankDetailsModal"
      class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-2xl p-6 w-[72rem] max-h-[90vh] overflow-y-auto animate-fadeIn">

        <!-- Tabs -->
        <div class="flex justify-between border-b pb-2 mb-4">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md font-medium transition-all duration-200"
            :class="[
              activeTab === tab.key
                ? 'bg-blue-600 text-white shadow-inner'
                : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4" />
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Tab Content -->
        <component
          :is="activeTabComponent"
          :account-no="ReferenceNumber"
        />

        <!-- Footer -->
        <div class="mt-6 flex justify-end">
          <button
            @click="closeBankDetails"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-all"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </transition>

  </AuthenticatedLayout>
</template>
