<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { FileCheck } from 'lucide-vue-next'

const loading = ref(false)
const records = ref([])
const pagination = ref({})
const filterStatus = ref('')
const filterCompany = ref('')
const filterBank = ref('')
const disapproveModal = ref(false)
const returnModal = ref(false)
const txnModal = ref(false)
const selectedSoa = ref(null)
const remarks = ref('')
const transactions = ref([])
const txnLoading = ref(false)

const companies = ref([])
const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}

const statusBadge = s => s === 'APPROVED' ? 'bg-green-100 text-green-800' : s === 'DISAPPROVED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'
const statusLabel = s => s ?? 'Pending'

async function fetchSoas() {
    loading.value = true
    try {
        const res = await axios.get('/get-pending-soas', { params: { status: filterStatus.value, company: filterCompany.value, bank: filterBank.value } })
        records.value = res.data.data ?? []
        pagination.value = res.data
    } catch { records.value = [] }
    finally { loading.value = false }
}

async function approveSoa(id) {
    if (!confirm('Approve this SOA?')) return
    await axios.post('/approve-soa-list', { soa_id: id })
    fetchSoas()
}

async function openDisapprove(soa) { selectedSoa.value = soa; remarks.value = ''; disapproveModal.value = true }
async function submitDisapprove() {
    await axios.post('/disapprove-soa', { soa_id: selectedSoa.value.RecID, remarks: remarks.value })
    disapproveModal.value = false; fetchSoas()
}

async function openReturn(soa) { selectedSoa.value = soa; remarks.value = ''; returnModal.value = true }
async function submitReturn() {
    await axios.post('/return-soa', { soa_id: selectedSoa.value.RecID, remarks: remarks.value })
    returnModal.value = false; fetchSoas()
}

async function viewTransactions(soa) {
    selectedSoa.value = soa; txnModal.value = true; txnLoading.value = true; transactions.value = []
    try {
        const res = await axios.get('/get-soa-transactions', { params: { soa_id: soa.RecID } })
        transactions.value = res.data ?? []
    } catch {} finally { txnLoading.value = false }
}

onMounted(() => { loadCompanies(); fetchSoas() })
</script>

<template>
    <Head title="SOA Approval" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <FileCheck class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">SOA Approval</h1>
                    <p class="text-xs text-gray-400">Review and approve statement of accounts</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-wrap gap-3 mb-4">
                    <select v-model="filterStatus" @change="fetchSoas" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="">All Status</option>
                        <option value="APPROVED">Approved</option>
                        <option value="DISAPPROVED">Disapproved</option>
                        <option value="PENDING">Pending</option>
                    </select>
                    <select v-model="filterCompany" @change="fetchSoas" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="">All Companies</option>
                        <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <input v-model="filterBank" @input="fetchSoas" type="text" placeholder="Bank..." class="border border-gray-300 rounded px-3 py-2 text-sm w-40" />
                </div>

                <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
                <div v-else-if="!records.length" class="text-center py-12 text-gray-400">No SOA records found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">SOA #</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account No</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted By</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passbook Bal</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="row in records" :key="row.RecID" class="hover:bg-gray-50">
                                <td class="px-3 py-3 font-medium text-blue-600">{{ row.RecID }}</td>
                                <td class="px-3 py-3 text-gray-900">{{ row.Company }}</td>
                                <td class="px-3 py-3 text-gray-900">{{ row.AccountNo }}</td>
                                <td class="px-3 py-3 text-gray-900">{{ row.BankName }}</td>
                                <td class="px-3 py-3 text-gray-900">{{ row.CreatedBy }}</td>
                                <td class="px-3 py-3 text-gray-500">{{ row.CreatedAt }}</td>
                                <td class="px-3 py-3 text-green-700 font-medium">{{ fmt(row.PassbookBal) }}</td>
                                <td class="px-3 py-3">
                                    <span :class="statusBadge(row.Status)" class="px-2 py-1 text-xs rounded-full font-medium">{{ statusLabel(row.Status) }}</span>
                                </td>
                                <td class="px-3 py-3 flex gap-1 flex-wrap">
                                    <button v-if="!row.Status" @click="approveSoa(row.RecID)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs">Approve</button>
                                    <button v-if="!row.Status" @click="openDisapprove(row)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Disapprove</button>
                                    <button v-if="!row.Status" @click="openReturn(row)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">Return</button>
                                    <button @click="viewTransactions(row)" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">Transactions</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Disapprove Modal -->
        <div v-if="disapproveModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Disapprove SOA #{{ selectedSoa?.RecID }}</h3>
                <textarea v-model="remarks" rows="3" placeholder="Enter reason..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-4 focus:ring-2 focus:ring-red-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="disapproveModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                    <button @click="submitDisapprove" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm rounded">Disapprove</button>
                </div>
            </div>
        </div>

        <!-- Return Modal -->
        <div v-if="returnModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Return SOA #{{ selectedSoa?.RecID }}</h3>
                <textarea v-model="remarks" rows="3" placeholder="Enter reason for return..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-4"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="returnModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                    <button @click="submitReturn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 text-sm rounded">Return</button>
                </div>
            </div>
        </div>

        <!-- Transactions Modal -->
        <div v-if="txnModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Transactions - SOA #{{ selectedSoa?.RecID }}</h3>
                    <button @click="txnModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <div v-if="txnLoading" class="text-center py-8 text-gray-500">Loading...</div>
                <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="t in transactions" :key="t.id"><td class="px-3 py-2">{{ t.transaction_date }}</td><td class="px-3 py-2">{{ t.additional_info }}</td><td class="px-3 py-2 text-green-700">{{ fmt(t.amount) }}</td><td class="px-3 py-2">{{ t.debit_or_credit }}</td></tr>
                    </tbody>
                </table>
                <div v-if="!transactions.length && !txnLoading" class="text-center py-4 text-gray-400 text-sm">No transactions found.</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
