<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { parsePDF, parseLBPStatement } from '@/Utils/soaLBP'
import { FileSpreadsheet, FileText, CheckCircle, XCircle } from 'lucide-vue-next'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'

const props = defineProps({
  accountNo: { type: String, required: true },
})

const headers = [
  { text: 'SOA', value: 'SOA' },
  { text: 'Remarks', value: 'Remarks' },
  { text: 'Passbook Balance', value: 'PassbookBal' },
  { text: 'Account No.', value: 'AccountNo' },
  { text: 'Created By', value: 'user.name' },
  { text: 'Created At', value: 'CreatedAt' },
  { text: 'Status', value: 'Status' },
  { text: 'Action', value: 'action' },
]

const account = ref(null)
const soa = ref([])
const loading = ref(false)

const form = ref({
  TransacAt: '',
  PassbookBal: '',
  Remarks: '',
  SOA: null,
  base64File: '',
  extension: '',
})

const fetchAccount = async () => {
  try {
    const { data } = await axios.get(`/retrieve-account/${props.accountNo}`)
    account.value = data || null
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load account details.', 'error')
  }
}

const fetchSOA = async () => {
  try {
    const { data } = await axios.get(`/retrieve-soa/${props.accountNo}`)
    soa.value = Array.isArray(data) ? data : data ? [data] : []
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load SOA details.', 'error')
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  form.value.extension = file.name.split('.').pop()

  const reader = new FileReader()
  reader.onload = (e) => {
    form.value.base64File = e.target.result.split(',')[1]
  }
  reader.readAsDataURL(file)
  form.value.SOA = file
}

const handleSaveLBP = async () => {
  if (!form.value.SOA) return Swal.fire('Warning', 'Please attach a PDF file first.', 'warning')
  if (!form.value.TransacAt || !form.value.PassbookBal || !form.value.Remarks)
    return Swal.fire('Warning', 'All fields are required.', 'warning')
  if (isNaN(parseFloat(form.value.PassbookBal)))
    return Swal.fire('Warning', 'Passbook Balance must be numeric.', 'warning')

  loading.value = true
  try {
    const parsedFile = await parsePDF(form.value.SOA)
    const pdfData = await parseLBPStatement(parsedFile)

  const banktransactions = pdfData.transactions.map((t, i) => {
    const parseNum = (n) =>
        typeof n === 'string' ? parseFloat(n.replace(/,/g, '')) || 0 : n || 0;

    const amount = parseNum(t.amount);
    const balance = parseNum(t.running_balance);

    const next_balance =
        i + 1 < pdfData.transactions.length
        ? parseNum(pdfData.transactions[i + 1].running_balance)
        : null;

    let debit_or_credit = '';
    let transaction_type = '';

    if (next_balance !== null) {
        if (balance > next_balance) {
        debit_or_credit = 'C';
        transaction_type = 'Credit';
        } else if (balance < next_balance) {
        debit_or_credit = 'D';
        transaction_type = 'Debit';
        } else {
        debit_or_credit = 'N';
        transaction_type = 'None';
        }
    } else {
        if (t.description.includes('WITHHELD') || t.description.includes('DEBIT')) {
        debit_or_credit = 'D';
        transaction_type = 'Deposit';
        } else {
        debit_or_credit = 'C';
        transaction_type = 'Credit';
        }
    }

    const cleanedDescription = (t.description || '').trim();

        return {
            account_no: pdfData.account,
            bank_code: 'LANDBANK',
            transaction_date: t.date,
            amount,
            runningbal: balance,
            debit_or_credit,
            transaction_type,
            description: cleanedDescription,
            reference: t.check_reference,
            additional_info: cleanedDescription,
        };
    });

    const bankbalances = {
      account_no: pdfData.account,
      currency: 'PHP',
      opening_balance: parseFloat(pdfData.beginning_balance) || 0,
      closing_balance: parseFloat(pdfData.ending_balance) || 0,
      available_balance: parseFloat(pdfData.ending_balance) || 0,
      transaction_date: pdfData.end_date || null,
    }

    const { data: soaResponse } = await axios.post(`/insert-soa/`, {
      accountno: props.accountNo,
      transactionat: form.value.TransacAt,
      passbookbal: form.value.PassbookBal,
      remarks: form.value.Remarks,
      extensionFile: form.value.extension,
      base64: form.value.base64File,
    })

    const SOAID = soaResponse?.SOAID

    await axios.post(`/insert-transactions`, {
      balances: bankbalances,
      transactions: banktransactions,
      SOAID,
    })

    Swal.fire('Success', 'SOA saved successfully!', 'success')
    await fetchSOA() 
  } catch (error) {
    console.error(error)
    Swal.fire('Error', 'SOA was saved but an issue occurred.', 'error')
  } finally {
    loading.value = false
  }
}

const handleOfflineSOA = async () => {
  if (!form.value.SOA) return Swal.fire('Warning', 'Please attach a file first.', 'warning')
  if (!form.value.TransacAt || !form.value.PassbookBal || !form.value.Remarks)
    return Swal.fire('Warning', 'All fields are required.', 'warning')
  if (isNaN(parseFloat(form.value.PassbookBal)))
    return Swal.fire('Warning', 'Passbook Balance must be numeric.', 'warning')

  loading.value = true
  try {
    await axios.post('/insert-soa', {
      accountno: props.accountNo,
      transactionat: form.value.TransacAt,
      passbookbal: form.value.PassbookBal,
      remarks: form.value.Remarks,
      extensionFile: form.value.extension,
      base64: form.value.base64File,
    })
    Swal.fire('Success', 'SOA file uploaded successfully.', 'success')
    await fetchSOA()
  } catch (error) {
    console.error(error)
    Swal.fire('Error', 'Failed to upload SOA.', 'error')
  } finally {
    loading.value = false
  }
}

const approve = async (item) => {
  try {
    const confirm = await Swal.fire({
      title: 'Approve SOA?',
      text: 'Are you sure you want to approve this Statement of Account?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, approve it!',
      cancelButtonText: 'Cancel',
    })

    if (!confirm.isConfirmed) return

    await axios.post('/approve-soa', { id: item.RecID })

    Swal.fire('Approved', 'SOA has been approved successfully.', 'success')

    await fetchSOA()
  } catch (error) {
    Swal.fire('Error', 'Something went wrong while approving the SOA.', 'error')
  }
}


const disapprove = async (item) => {
  try {
    const confirm = await Swal.fire({
      title: 'Disapprove SOA?',
      text: 'Are you sure you want to disapprove this Statement of Account?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, disapprove it',
      cancelButtonText: 'Cancel',
    })

    if (!confirm.isConfirmed) return

    await axios.post('/disapprove-soa', { id: item.RecID })

    Swal.fire('Disapproved', 'SOA has been disapproved.', 'info')

    await fetchSOA()
  } catch (error) {
    console.error(error)
    Swal.fire('Error', 'Something went wrong while disapproving the SOA.', 'error')
  }
}


const previewFile = (path, type) => {
  if (!path) {
    Swal.fire('File not found', 'No SOA file available.', 'warning')
    return
  }
   const fileUrl = `${window.location.origin}/storage/${path.replace(/^uploads[\\/]/, 'uploads/')}`

  if (type === 'pdf') {
    window.open(fileUrl, '_blank')
  } else if (type === 'excel') {
    Swal.fire({
      title: 'Open Excel File?',
      text: 'This will download and open the Excel file.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Open',
    }).then((result) => {
      if (result.isConfirmed) {
        window.open(fileUrl, '_blank')
      }
    })
  }
}

const loadAll = async () => {
  loading.value = true
  try {
    await Promise.all([fetchAccount(), fetchSOA()])
  } finally {
    loading.value = false
  }
}

onMounted(loadAll)
watch(() => props.accountNo, loadAll)
</script>

<template>
  <div class="p-4 bg-white rounded-xl shadow-md max-w-[80rem] mx-auto text-sm leading-tight">
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-4 animate-fadeIn">
      <div class="flex items-center justify-between">
        <div class="h-9 w-64 bg-gray-200 rounded-md animate-pulse"></div>
        <div class="h-9 w-28 bg-gray-200 rounded-md animate-pulse"></div>
      </div>
      <div class="border rounded-lg overflow-hidden shadow-sm">
        <div class="grid grid-cols-6 bg-blue-50 py-2 px-4 border-b">
          <div v-for="i in 6" :key="i" class="h-4 w-24 bg-gray-300 rounded animate-pulse"></div>
        </div>
        <div v-for="r in 8" :key="r" class="grid grid-cols-6 py-3 px-4 border-b">
          <div v-for="c in 6" :key="c" class="h-4 w-20 bg-gray-200 rounded animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="account" class="space-y-4">
      <div class="flex items-center justify-between border-b pb-2">
        <h2 class="text-lg font-semibold text-gray-800">
          Bank SOA AGRIBANK — <span class="text-blue-600">{{ account.AccountNo }}</span>
        </h2>
      </div>

      <div class="soa-container p-4">
        <h5 class="mb-3 font-semibold">Add Statement of Account</h5>

        <div class="flex flex-wrap gap-6">
          <!-- Form Section -->
          <div class="flex-1 min-w-[320px] space-y-4 max-w-[35%]">
            <div>
              <label for="TransacAt" class="block text-sm font-medium text-gray-700 mb-1"
                >Transaction Date <span class="text-red-500">*</span></label
              >
              <input
                type="date"
                id="TransacAt"
                v-model="form.TransacAt"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="PassbookBal" class="block text-sm font-medium text-gray-700 mb-1"
                >Passbook Balance <span class="text-red-500">*</span></label
              >
              <input
                type="text"
                id="PassbookBal"
                v-model="form.PassbookBal"
                placeholder="Balance"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="Remarks" class="block text-sm font-medium text-gray-700 mb-1"
                >Remarks <span class="text-red-500">*</span></label
              >
              <input
                type="text"
                id="Remarks"
                v-model="form.Remarks"
                placeholder="Remarks"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="SOA" class="block text-sm font-medium text-gray-700 mb-1"
                >Attach SOA PDF <span class="text-red-500">*</span></label
              >
              <input
                type="file"
                id="SOA"
                @change="handleFileUpload"
                class="w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-lg"
              />
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                @click="handleOfflineSOA"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition"
              >
                Offline SOA
              </button>
              <button
                @click="handleSaveLBP"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
              >
                e-SOA
              </button>
            </div>
          </div>

          <!-- Table Section -->
          <div class="flex-[2] min-w-[500px]">
            <EasyDataTable :headers="headers" :items="soa">

              <template #item-action="item">
                <div class="flex gap-2">
                  <template v-if="item.Status === 'PENDING'">
                    <button
                      class="px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700"
                      @click="approve(item)"
                    >
                    <CheckCircle class="w-4 h-4" />
                    </button>
                    <button
                      class="px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700"
                      @click="disapprove(item)"
                    >
                    <XCircle class="w-4 h-4" />
                    </button>
                  </template>
                  <template v-else>
                    <span class="text-gray-500 italic">No actions</span>
                  </template>
                </div>
              </template>

              <template #item-SOA="item">
                <div class="flex items-center gap-2">
                  <template v-if="item.IsExcel === 1">
                    <FileSpreadsheet
                      class="w-5 h-5 text-green-600 cursor-pointer hover:text-green-800 transition"
                      @click="previewFile(item.SOA, 'excel')"
                    />
                    <span class="text-green-700 font-medium cursor-pointer hover:underline" 
                          @click="previewFile(item.SOA, 'excel')">
                    </span>
                  </template>

                  <template v-else>
                    <FileText
                      class="w-5 h-5 text-red-600 cursor-pointer hover:text-red-800 transition"
                      @click="previewFile(item.SOA, 'pdf')"
                    />
                    <span class="text-red-700 font-medium cursor-pointer hover:underline"
                          @click="previewFile(item.SOA, 'pdf')">
                    </span>
                  </template>
                  </div>
              </template>

              <template #item-CreatedAt="item">
                {{ new Date(item.CreatedAt).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                  year: 'numeric'
                }) }}
              </template>

            <template #item-Status="item">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold shadow-sm',
                  item.Status === 'PENDING'
                    ? 'bg-yellow-200/70 text-yellow-900 border border-yellow-300'
                    : item.Status === 'APPROVED'
                    ? 'bg-green-200/70 text-green-900 border border-green-300'
                    : 'bg-rose-200/70 text-rose-900 border border-rose-300'
                ]"
              >
                {{ item.Status }}
              </span>
            </template>

            <template #item-PassbookBal="{ PassbookBal }">
              <span class="text-right block">
                {{ Number(PassbookBal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              </span>
            </template>

            </EasyDataTable>
          </div>
        </div>
      </div>
    </div>

    <!-- No Account Found -->
    <div v-else class="text-center text-gray-500 py-4">No account details found.</div>
  </div>
</template>
