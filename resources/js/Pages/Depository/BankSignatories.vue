<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'

const props = defineProps({
  accountNo: { type: String, required: true },
})

const account = ref([])
const loading = ref(false)

const users = ref([])
const selectedUser = ref(null)
const loadingUsers = ref(false)

const headers = [
  { text: 'Employee ID', value: 'EmployeeID' },
  { text: 'Name', value: 'Name' },
  { text: 'Position', value: 'PositionDESC'},
  { text: 'Account No', value: 'AccountNo' },
  { text: 'Created By', value: 'CreatedByName'},
  { text: 'Created At', value: 'CreatedAt' },
  { text: 'Status', value: 'Status' },
]

const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const res = await axios.get('/users') 
    users.value = res.data || []
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load user list.', 'error')
  } finally {
    loadingUsers.value = false
  }
}

const fetchAccount = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/bank-signatories/${props.accountNo}`)
    if (res.data && res.data.length > 0) {
       account.value = res.data.map(item => ({
        ...item,
        PositionDESC: item.position?.POSITIONDESC ?? '',
        CreatedByName: item.user?.name ?? '',
      }))
    } else {
      account.value = []
    }
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Failed to load account details.', 'error')
  } finally {
    loading.value = false
  }
}

const addSignatories = async (userid, accountNo) => {

  if (!userid) {
    alert('Please select a user first!')
    return
  }

   try {

    await axios.post('/insert-signatories', {
      id: userid,
      account: accountNo
    });

    await fetchAccount();

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `New Bank Signatories Added.`,
      confirmButtonColor: '#3085d6'
    });
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: 'error',
      title: 'Failed!',
      text: 'Error Inserting New Bank Depositories.',
      confirmButtonColor: '#d33'
    });
  }
}

const activateDeactivate = async (signatories) => {
 try {
    const newStatus = signatories.Status == 1 ? 0 : 1;

    await axios.post('/signatories-status', {
      id: signatories.RecID,
      status: newStatus
    });

    await fetchAccount();

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Signatory has been ${newStatus == 1 ? 'Activated' : 'Deactivated'}.`,
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

onMounted(() => {
  fetchUsers()
  fetchAccount()
})

watch(() => props.accountNo, fetchAccount)
</script>

<template>
  <div class="p-4 bg-white rounded-xl shadow-md max-w-[80rem] mx-auto text-sm leading-tight">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <select
          v-model="selectedUser"
          class="border border-gray-300 rounded-lg px-3 py-2 w-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
             :disabled="loadingUsers"
        >
          <option disabled value="">Select User</option>
          <option
            v-for="user in users"
            :key="user.id"
            :value="user.id"
          >
            {{ user.name }}
          </option>
        </select>

        <button
          @click="addSignatories(selectedUser, accountNo)"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition"
          :disabled="loadingUsers"
        >
          <span v-if="loadingUsers">Loading...</span>
          <span v-else>Add</span>
        </button>
      </div>
    </div>

    <div v-if="loading">
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

    <div v-else-if="account.length">
      <div class="flex items-center justify-between border-b pb-2 mb-3">
        <h2 class="text-lg font-semibold text-gray-800">
          Bank Signatories — <span class="text-blue-600">{{ props.accountNo }}</span>
        </h2>
      </div>

       <EasyDataTable
        :headers="headers"
        :items="account"
        table-class="border rounded-md shadow-sm"
        alternating
        show-index
        :rows-per-page="10"
      >
        <template #item-Status="item">
          <div class="flex items-center justify-between">
            <span
              :class="[ 
                'px-2 py-1 rounded text-xs font-semibold',
                item.Status === 1
                  ? 'bg-green-100 text-green-700'
                  : 'bg-red-100 text-red-700'
              ]"
            >
              {{ item.Status === 1 ? 'Active' : 'Inactive' }}
            </span>

            <button
              @click="activateDeactivate(item)"
              class="p-1 rounded hover:bg-gray-100 transition-colors ml-2"
              :title="item.Status === 1 ? 1 : 0"
            >
              
              <svg
                v-if="item.Status === 1"
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
      </EasyDataTable>
    </div>

    <div v-else class="text-center text-gray-500 py-4">
      No account details found.
    </div>
  </div>
</template>