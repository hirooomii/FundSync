<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  accountNo: { type: String, required: true },
})

const headers = [
  { text: 'RecID', value: 'RecID' },
  { text: 'Company', value: 'Company' },
  { text: 'Branch', value: 'Branch' },
  { text: 'BranchID', value: 'BranchID' },
  { text: 'CashAccount', value: 'CashAccount' },
  { text: 'Description', value: 'Description' },
  { text: 'IsActive', value: 'IsActive' },
  { text: 'AccountNo', value: 'AccountNo' },
]

const account = ref(null)
const loading = ref(false)
const cashacc = ref([])

const fetchCashAccount = async () => {
  try {
    const { data } = await axios.get(`/check-cashaccount/${props.accountNo}`)
    cashacc.value = Array.isArray(data) ? data : data ? [data] : []
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load Cash Account details.', 'error')
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
    await Promise.all([fetchAccount(), fetchCashAccount()])
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
          Bank Cash Account — <span class="text-blue-600">{{ account.AccountNo }}</span>
        </h2>
      </div>
       <EasyDataTable :headers="headers" :items="cashacc">

           <template #item-IsActive="item"> 
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
            </template>

            <template #item-AccountNo="item"> 
                <span
                class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-300"
                >
                {{ item.AccountNo }}
                </span>
            </template>

      </EasyDataTable>
    </div>

    <div v-else class="text-center text-gray-500 py-4">
      No account details found.
    </div>
  </div>
</template>

