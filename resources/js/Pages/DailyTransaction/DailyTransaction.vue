<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { ArrowRightLeft } from 'lucide-vue-next'

const companies  = ref([])
const banks      = ref([])
const accounts   = ref([])

const filterCompany = ref('')
const filterBank    = ref('')
const filterAccount = ref('')
const dateFrom      = ref('')
const dateTo        = ref('')

const rows    = ref([])
const loading = ref(false)
const reconning = ref(false)

const fmt = v => Number(v ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

async function loadCompanies() {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
}

async function loadBanks() {
    banks.value = []
    accounts.value = []
    filterBank.value = ''
    filterAccount.value = ''
    if (!filterCompany.value) return
    const res = await axios.get('/recon-banks', { params: { company: filterCompany.value } })
    banks.value = res.data ?? []
}

async function loadAccounts() {
    accounts.value = []
    filterAccount.value = ''
    if (!filterBank.value) return
    const res = await axios.get('/recon-accounts', {
        params: { company: filterCompany.value, bank_name: filterBank.value }
    })
    accounts.value = res.data ?? []
}

async function fetchTransactions() {
    if (!filterAccount.value) return Swal.fire('Warning', 'Please select an account number.', 'warning')
    if (!dateFrom.value || !dateTo.value) return Swal.fire('Warning', 'Please set a date range.', 'warning')
    loading.value = true
    try {
        const res = await axios.get('/fetch-joined-transactions', {
            params: { account_no: filterAccount.value, date_from: dateFrom.value, date_to: dateTo.value }
        })
        rows.value = Array.isArray(res.data) ? res.data : []
    } catch {
        Swal.fire('Error', 'Failed to fetch transactions.', 'error')
    } finally {
        loading.value = false
    }
}

async function runRecon() {
    if (!filterAccount.value) return Swal.fire('Warning', 'Please select an account number.', 'warning')
    if (!dateFrom.value || !dateTo.value) return Swal.fire('Warning', 'Please set a date range.', 'warning')

    reconning.value = true
    Swal.fire({
        title: 'Running Auto-Reconciliation',
        text: 'Matching bank entries with Acumatica bookings...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    })

    try {
        const res = await axios.post('/auto-bind-transactions', {
            account_no: filterAccount.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            bank_name: filterBank.value,
        })
        await fetchTransactions()
        Swal.fire('Done', res.data.message, 'success')
    } catch {
        Swal.fire('Error', 'Auto-reconciliation failed.', 'error')
    } finally {
        reconning.value = false
    }
}

function rowClass(row) {
    if (!row.docref) return 'bg-yellow-50 hover:bg-yellow-100'
    const dc = (row.debit_or_credit ?? '').toUpperCase()
    if (dc === 'D' || dc === 'DEBIT')  return 'bg-red-50 hover:bg-red-100'
    return 'bg-green-50 hover:bg-green-100'
}

function dcLabel(val) {
    const v = (val ?? '').toUpperCase()
    if (v === 'D' || v === 'DEBIT')  return { text: 'Debit',  cls: 'bg-red-100 text-red-700' }
    if (v === 'C' || v === 'CREDIT') return { text: 'Credit', cls: 'bg-green-100 text-green-700' }
    return { text: val, cls: 'bg-gray-100 text-gray-700' }
}

onMounted(loadCompanies)
</script>

<template>
    <Head title="Daily Transaction" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <ArrowRightLeft class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Daily Transaction</h1>
                    <p class="text-xs text-gray-400">Auto-reconcile bank and Acumatica entries</p>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-[1400px] mx-auto px-4">

            <!-- Filter bar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex flex-col gap-1 min-w-[160px]">
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Company <span class="text-red-500">*</span></label>
                        <select v-model="filterCompany" @change="loadBanks"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <option value="">Select Company</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 min-w-[160px]">
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Bank <span class="text-red-500">*</span></label>
                        <select v-model="filterBank" @change="loadAccounts"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <option value="">Select Bank</option>
                            <option v-for="b in banks" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 min-w-[180px]">
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Account No <span class="text-red-500">*</span></label>
                        <select v-model="filterAccount"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <option value="">Select Account</option>
                            <option v-for="a in accounts" :key="a.AccountNo" :value="a.AccountNo">{{ a.AccountNo }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date From <span class="text-red-500">*</span></label>
                        <input v-model="dateFrom" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date To <span class="text-red-500">*</span></label>
                        <input v-model="dateTo" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                    </div>
                    <button @click="runRecon" :disabled="reconning || loading"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold transition">
                        <ArrowRightLeft class="w-4 h-4" />
                        RECON
                    </button>
                    <button @click="fetchTransactions" :disabled="loading"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition disabled:opacity-50">
                        Fetch
                    </button>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-3 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 px-1">
                <span>Row Colors:</span>
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-50 border border-yellow-200 text-yellow-800">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> Pending
                </span>
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700">
                    <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Debit
                </span>
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 border border-green-200 text-green-700">
                    <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> Credit
                </span>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="loading" class="flex justify-center items-center py-16 text-gray-400">
                    <div class="animate-spin w-7 h-7 border-4 border-blue-500 border-t-transparent rounded-full mr-3"></div>
                    Loading...
                </div>

                <div v-else-if="rows.length === 0" class="text-center py-16 text-gray-400 text-sm">
                    No transactions found. Select filters and click RECON or Fetch.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-xs border-collapse">
                        <thead>
                            <tr>
                                <th colspan="6" class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-800 border-b-2 border-emerald-300">
                                    Bank Information
                                </th>
                                <th colspan="6" class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-widest bg-blue-50 text-blue-800 border-b-2 border-blue-300">
                                    Acumatica Booking
                                </th>
                            </tr>
                            <tr class="bg-gray-50 text-[11px] font-bold uppercase text-gray-500 tracking-wide">
                                <th class="px-3 py-2 text-left border-b border-gray-200">Bank</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Account</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Type</th>
                                <th class="px-3 py-2 text-right border-b border-gray-200">Amount</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Date</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200 border-r border-r-gray-300">Description</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Account</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Reference</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Type</th>
                                <th class="px-3 py-2 text-right border-b border-gray-200">Amount</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Date</th>
                                <th class="px-3 py-2 text-left border-b border-gray-200">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in rows" :key="row.id" :class="rowClass(row)">
                                <!-- Bank side -->
                                <td class="px-3 py-2 font-mono whitespace-nowrap">{{ row.bank_code }}</td>
                                <td class="px-3 py-2 font-mono whitespace-nowrap">{{ row.account_no }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 rounded-full text-[11px] font-semibold', dcLabel(row.debit_or_credit).cls]">
                                        {{ dcLabel(row.debit_or_credit).text }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-medium whitespace-nowrap">{{ fmt(row.amount) }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ row.transaction_date }}</td>
                                <td class="px-3 py-2 max-w-[180px] truncate border-r border-gray-200">{{ row.description }}</td>
                                <!-- Acumatica side -->
                                <td class="px-3 py-2 font-mono text-gray-600 whitespace-nowrap">{{ row.acu_account ?? '—' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span v-if="row.acu_reference" class="flex items-center gap-1 text-green-700 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                        {{ row.acu_reference }}
                                    </span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span v-if="row.acu_type" class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700">{{ row.acu_type }}</span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-3 py-2 text-right font-medium whitespace-nowrap">{{ row.acu_amount != null ? fmt(row.acu_amount) : '—' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ row.acu_date ?? '—' }}</td>
                                <td class="px-3 py-2 max-w-[180px] truncate text-gray-600">{{ row.acu_desc ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p v-if="rows.length" class="text-xs text-gray-400 mt-2 px-1">
                {{ rows.length }} transaction(s) · {{ rows.filter(r => r.acu_reference).length }} matched · {{ rows.filter(r => !r.acu_reference).length }} pending
            </p>
        </div>
    </AuthenticatedLayout>
</template>
