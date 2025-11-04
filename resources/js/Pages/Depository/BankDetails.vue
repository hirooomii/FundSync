<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  accountNo: { type: String, required: true },
})

const account = ref(null)
const loading = ref(false)

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

onMounted(fetchAccount)
watch(() => props.accountNo, fetchAccount)
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

.info-card {
  @apply bg-white border-l-4 border-blue-600 shadow-sm p-2 rounded-md;
}
.info-label {
  @apply text-gray-500 text-xs font-medium leading-tight;
}
.info-value {
  @apply text-gray-800 text-sm font-semibold mt-0.5 break-words;
}
</style>

<template>
  <div class="p-4 bg-white rounded-xl shadow-md max-w-[80rem] mx-auto text-sm leading-tight">
    <div v-if="loading" class="space-y-4 animate-fadeIn">
      <div class="flex items-center justify-between border-b pb-2">
        <div class="h-5 w-48 bg-gray-200 rounded animate-pulse"></div>
      </div>

      <div class="grid grid-cols-4 gap-3">
        <div v-for="i in 20" :key="i" class="bg-gray-100 border-l-4 border-blue-200 rounded-md p-3 animate-pulse">
          <div class="h-3 w-20 bg-gray-300 rounded mb-2"></div>
          <div class="h-4 w-32 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else-if="account" class="space-y-4">
      <div class="flex items-center justify-between border-b pb-2">
      <h2 class="text-lg font-semibold text-gray-800">
        Bank Information — <span class="text-blue-600">{{ account.AccountNo }}</span>
      </h2>
    </div>

      <div class="grid grid-cols-4 gap-3">
        <div v-for="(value, label) in {
          'Depository ID': account.RecID,
          'Company': account.Company,
          'Bank Name': account.BankName,
          'Account Number': account.AccountNo,
          'Account Name': account.AccountName,
          'Bank Type': account.DepositType,
          'Bank Tag': account.AccountTag,
          'Bank Branch': account.BankBranch,
          'Bank Street': account.BankStreet,
          'Bank City': account.BankCity,
          'Bank Province': account.BankProvince,
          'Status': account.Status,
          'Beginning Balance': account.BeginningBal,
          'Maintaining Balance': account.MaintainingBal,
          'Available Balance': account.available_balance,
          'Interest Rate': account.InterestRate,
          'Date Opened': account.DateOpen,
          'Maturity Date': account.MaturityDate,
          'Contact Person': account.DepContactPerson,
          'Contact Number': account.DepContactNum,
          'Position Designation': account.DepPosition,
          'Email Address': account.DepEmailAdd,
          'Purpose': account.Description
        }" :key="label" class="info-card">
          <p class="info-label">{{ label }}</p>
          <p class="info-value">{{ value || '—' }}</p>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-500 py-6">
      No account details found.
    </div>
  </div>
</template>
