<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx';
import Swal from 'sweetalert2'
import { PackagePlus } from 'lucide-vue-next';

const props = defineProps({
  accountNo: { type: String, required: true },
})

const fileInput = ref(null);
const account = ref(null)
const loading = ref(false)
const transaction = ref([])

const headers = [
  { text: 'Account No.', value: 'account_no' },
  { text: 'Bank Code', value: 'bank_code' },
  { text: 'Transaction Date', value: 'transaction_date' },
  { text: 'Amount', value: 'amount' },
  { text: 'Type', value: 'transaction_type' },
  { text: 'Description', value: 'description' },
  { text: 'Reference', value: 'reference' },
  { text: 'Additional Info', value: 'additional_info' },
  { text: 'Running Balance', value: 'runningbal' },
];

const triggerFile = () => {
  fileInput.value.click();
};

const fileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
      const base64File = reader.result.split(',')[1];
      resolve(base64File);
    };
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
};

const handleFile = async (event) => {
  const file = event.target.files[0];

  if (!file) {
    alert('No file selected');
    return;
  }

  const allowedTypes = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ];

  if (!allowedTypes.includes(file.type)) {
    alert('Only Excel files are allowed.');
    return;
  }

  loading.value = true;

  try {

    const base64File = await fileToBase64(file);
    const extension = file.name.split('.').pop();

    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data, { type: 'array' });
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    const jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

    if (!jsonData.length) {
      alert('No data found in Excel.');
      loading.value = false;
      return;
    }

    jsonData.forEach(row => {
      let date;
      if (typeof row.DATE === 'number') {
        date = new Date((row.DATE - 25569) * 86400 * 1000);
      } else {
        date = new Date(row.DATE);
      }
      row.DATE = date instanceof Date && !isNaN(date)
        ? `${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getDate()).padStart(2, '0')}/${date.getFullYear()}`
        : '';
    });

    const validDates = jsonData.map(r => new Date(r.DATE)).filter(d => !isNaN(d)).sort((a, b) => a - b);
    const formatDate = d => d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const dateRange = validDates.length
      ? `${formatDate(validDates[0])} to ${formatDate(validDates[validDates.length - 1])}`
      : 'No valid dates';

    const totalAmount = jsonData.reduce((sum, row) => sum + (parseFloat(row.AMOUNT) || 0), 0);

    const payload = {
      excelbase64: base64File,
      excelext: extension,
      AccountNo: account.value.AccountNo, 
      Remarks: dateRange,
      Bank: account.value.BankName,  
      PassbookBal: totalAmount.toFixed(2),
      transactions: jsonData
    };

    const response = await axios.post('/import-transaction', payload);

     if (response.data.status === 'success') {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: response.data.message || 'All data imported successfully.',
        confirmButtonText: 'OK',
      });
    } else {
      await Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: response.data.message || 'Something went wrong during import.',
        confirmButtonText: 'OK',
      });
    }

  } catch (err) {

    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: err.response?.data?.message || 'An error occurred while importing transactions.',
      confirmButtonText: 'OK',
    });

  } finally {
    loading.value = false;
    fileInput.value.value = ''; 
  }
};

const fetchTransaction = async () => {
  try {
    const { data } = await axios.get(`/retrieve-transaction/${props.accountNo}`)
    transaction.value = Array.isArray(data) ? data : data ? [data] : []
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load Transaction details.', 'error')
  }
}

const fetchAccount = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/retrieve-account/${props.accountNo}`)
    if (res.data) {
      account.value = res.data
    } else {
      Swal.fire('Not Found', 'No account details available for this number.', 'warning')
    }
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load account details.', 'error')
  } finally {
    loading.value = false
  }
}

const loadAll = async () => {
  loading.value = true
  try {
    await Promise.all([fetchAccount(), fetchTransaction()])
  } finally {
    loading.value = false
  }
}

onMounted(loadAll)
watch(() => props.accountNo, loadAll)
</script>

<template>
  <div class="p-4 bg-white rounded-xl shadow-md max-w-[80rem] mx-auto text-sm leading-tight">
    <div v-if="loading" class="space-y-4 animate-fadeIn">
    <div class="flex items-center justify-between">
      <div class="h-9 w-64 bg-gray-200 border border-blue-200 rounded-md animate-pulse"></div>
      <div class="h-9 w-28 bg-gray-200 border border-blue-200 rounded-md animate-pulse"></div>
    </div>

    <div class="border border-blue-200 rounded-lg overflow-hidden shadow-sm">

      <div class="grid grid-cols-6 bg-blue-50 py-2 px-4 border-b border-blue-100">
        <div
          v-for="i in 6"
          :key="'header-' + i"
          class="h-4 w-24 bg-gray-300 rounded animate-pulse border border-blue-100"
        ></div>
      </div>

      <div
        v-for="row in 8"
        :key="'row-' + row"
        class="grid grid-cols-6 py-3 px-4 border-b border-blue-100"
      >
        <div
          v-for="col in 6"
          :key="'cell-' + row + '-' + col"
          class="h-4 w-20 bg-gray-200 rounded animate-pulse border border-blue-50"
        ></div>
      </div>
    </div>

    <div class="flex justify-between pt-3">
      <div class="h-4 w-32 bg-gray-200 border border-blue-200 rounded animate-pulse"></div>
      <div class="flex gap-2">
        <div
          v-for="i in 3"
          :key="'page-' + i"
          class="h-8 w-8 bg-gray-200 border border-blue-200 rounded-md animate-pulse"
        ></div>
      </div>
    </div>
  </div>

    <div v-else-if="account" class="space-y-4">
      <div class="flex items-center justify-between border-b pb-2">
        <h2 class="text-lg font-semibold text-gray-800">
          Bank Transaction — <span class="text-blue-600">{{ account.AccountNo }}</span>
        </h2>
          <button
            @click="triggerFile"
            class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1.5 rounded-md shadow-sm transition-all"
            :disabled="loading"
          >
            <PackagePlus class="w-4 h-4" /> {{ loading ? 'Please Wait...' : 'Import' }}
            <img v-if="loading" src="https://i.gifer.com/ZZ5H.gif"  alt="loading" class="ml-2"/>
          </button>

          <input
            type="file"
            ref="fileInput"
            accept=".xlsx, .xls"
            class="hidden"
            @change="handleFile"
          />
      </div>
        <EasyDataTable :headers="headers" :items="transaction">
            <template #item-transaction_type="item">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold shadow-sm',
                  item.transaction_type === 'Credit'
                    ? 'bg-green-200/70 text-green-900 border border-green-300'
                    : 'bg-rose-200/70 text-rose-900 border border-rose-300'
                ]"
              >
                {{ item.transaction_type }}
              </span>
            </template>

             <template #item-reference="item"> 
                <span
                class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-300"
                >
                {{ item.reference }}
                </span>
             </template>

              <template #item-runningbal="{ runningbal }">
                ₱{{ Number(runningbal || 0).toLocaleString() }}
              </template>

              <template #item-amount="{ amount }">
                ₱{{ Number(amount || 0).toLocaleString() }}
              </template>

        </EasyDataTable>
    </div>

    <div v-else class="text-center text-gray-500 py-4">
      No account details found.
    </div>
  </div>
</template>
