<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { GitMerge, Search, Link2, RefreshCw } from 'lucide-vue-next'

const loading     = ref(false)
const bankTxns    = ref([])
const acuTxns     = ref([])
const selectedBank = ref(null)
const selectedAcu  = ref(null)

const filters = ref({
    company:    '',
    bank_name:  '',
    account_no: '',
    date_from:  '',
    date_to:    '',
})

const companies = ref([])
const banks     = ref([])
const accounts  = ref([])

const fmt = v => Number(v ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const summary = computed(() => {
    const matched   = bankTxns.value.filter(t => t.docref).length
    const total     = bankTxns.value.length
    return { total, matched, unmatched: total - matched }
})

async function loadCompanies() {
    try {
        const res = await axios.get('/recon-companies')
        companies.value = res.data ?? []
    } catch {}
}

async function onCompanyChange() {
    filters.value.bank_name  = ''
    filters.value.account_no = ''
    banks.value    = []
    accounts.value = []
    if (!filters.value.company) return
    try {
        const res = await axios.get('/recon-banks', { params: { company: filters.value.company } })
        banks.value = res.data ?? []
    } catch {}
}

async function onBankChange() {
    filters.value.account_no = ''
    accounts.value = []
    try {
        const res = await axios.get('/recon-accounts', {
            params: { company: filters.value.company, bank_name: filters.value.bank_name }
        })
        accounts.value = res.data ?? []
    } catch {}
}

async function fetchData() {
    if (!filters.value.account_no) {
        return Swal.fire('Warning', 'Please select an account number.', 'warning')
    }
    if (!filters.value.date_from || !filters.value.date_to) {
        return Swal.fire('Warning', 'Please set both Date From and Date To.', 'warning')
    }

    loading.value = true
    bankTxns.value = []
    acuTxns.value  = []
    selectedBank.value = null
    selectedAcu.value  = null

    try {
        const params = {
            account_no: filters.value.account_no,
            date_from:  filters.value.date_from,
            date_to:    filters.value.date_to,
        }
        const [bRes, aRes] = await Promise.all([
            axios.get('/recon-bank-transactions',  { params }),
            axios.get('/recon-acumatica-bookings', { params }),
        ])
        bankTxns.value = Array.isArray(bRes.data) ? bRes.data : []
        acuTxns.value  = Array.isArray(aRes.data) ? aRes.data : []
    } catch {
        Swal.fire('Error', 'Failed to fetch data.', 'error')
    } finally {
        loading.value = false
    }
}

async function bindTransaction() {
    if (!selectedBank.value || !selectedAcu.value) {
        return Swal.fire('Warning', 'Select one row from each side first.', 'warning')
    }

    try {
        await axios.post('/recon-bind', {
            bank_id:    selectedBank.value.id,
            acu_rec_id: selectedAcu.value.RecID,
        })
        Swal.fire({ title: 'Bound!', text: 'Transaction matched successfully.', icon: 'success', timer: 1500, showConfirmButton: false })
        selectedBank.value = null
        selectedAcu.value  = null
        await fetchData()
    } catch {
        Swal.fire('Error', 'Bind failed.', 'error')
    }
}

function dcBadge(val) {
    const v = (val ?? '').toUpperCase()
    if (v === 'D' || v === 'DEBIT')  return { text: 'Debit',  cls: 'bg-red-100 text-red-700' }
    if (v === 'C' || v === 'CREDIT') return { text: 'Credit', cls: 'bg-green-100 text-green-700' }
    return { text: val, cls: 'bg-gray-100 text-gray-600' }
}

onMounted(loadCompanies)
</script>

<template>
    <Head title="Reconciliation" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <GitMerge class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Reconciliation</h1>
                    <p class="text-xs text-gray-400">Match bank transactions with Acumatica bookings</p>
                </div>
            </div>
        </template>

        <div class="p-6 space-y-5">

            <!-- Filter card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
                    <Search class="w-4 h-4 text-gray-400" />
                    <span class="text-sm font-semibold text-gray-700">Search Filters</span>
                </div>
                <div class="px-5 py-4 flex flex-wrap gap-4 items-end">

                    <div class="flex flex-col gap-1 min-w-[170px]">
                        <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Company</label>
                        <select v-model="filters.company" @change="onCompanyChange"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">All Companies</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 min-w-[200px]">
                        <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Bank Name</label>
                        <select v-model="filters.bank_name" @change="onBankChange"
                            :disabled="!filters.company"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Select Bank</option>
                            <option v-for="b in banks" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 min-w-[220px]">
                        <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Account No <span class="text-red-400">*</span></label>
                        <select v-model="filters.account_no"
                            :disabled="!filters.bank_name"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Select Account</option>
                            <option v-for="a in accounts" :key="a.AccountNo" :value="a.AccountNo">
                                {{ a.AccountNo }}{{ a.AccountName ? ' — ' + a.AccountName : '' }}
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Date From <span class="text-red-400">*</span></label>
                        <input v-model="filters.date_from" type="date"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Date To <span class="text-red-400">*</span></label>
                        <input v-model="filters.date_to" type="date"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    </div>

                    <div class="flex flex-col gap-2 justify-end">
                        <button @click="fetchData" :disabled="loading"
                            class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-blue-200">
                            <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
                            {{ loading ? 'Loading…' : 'Search' }}
                        </button>
                    </div>

                </div>
            </div>

            <!-- Summary strip -->
            <div v-if="bankTxns.length || acuTxns.length" class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <GitMerge class="w-5 h-5 text-slate-500" />
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">Total Bank Txns</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summary.total }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-emerald-200 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <Link2 class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div>
                        <p class="text-[11px] text-emerald-600 uppercase tracking-wide font-semibold">Matched</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ summary.matched }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-amber-200 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                        <Search class="w-5 h-5 text-amber-500" />
                    </div>
                    <div>
                        <p class="text-[11px] text-amber-600 uppercase tracking-wide font-semibold">Pending</p>
                        <p class="text-2xl font-bold text-amber-600">{{ summary.unmatched }}</p>
                    </div>
                </div>
            </div>

            <!-- Bind hint -->
            <div v-if="selectedBank || selectedAcu"
                class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 text-sm text-blue-700 font-medium flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <Link2 class="w-4 h-4 text-blue-500 shrink-0" />
                    <span v-if="selectedBank && selectedAcu">Ready to bind — click <strong>Bind</strong> to match these two records.</span>
                    <span v-else-if="selectedBank">Bank row selected. Now pick an Acumatica booking.</span>
                    <span v-else>Acumatica booking selected. Now pick a Bank transaction.</span>
                </div>
                <button v-if="selectedBank && selectedAcu" @click="bindTransaction"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors whitespace-nowrap">
                    <Link2 class="w-4 h-4" />
                    Bind
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
                <div class="w-10 h-10 border-[3px] border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm text-gray-400">Fetching transactions…</p>
            </div>

            <!-- Side-by-side tables -->
            <div v-else-if="bankTxns.length || acuTxns.length" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Bank Transactions -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-3.5 flex items-center gap-2.5 shrink-0" style="background:#1e293b;">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400 shadow-sm shadow-blue-400/50"></div>
                        <span class="text-[13px] font-semibold text-white">Bank Transactions</span>
                        <span class="ml-auto text-xs text-slate-400 bg-slate-700 px-2.5 py-0.5 rounded-full font-medium">{{ bankTxns.length }}</span>
                    </div>
                    <div v-if="!bankTxns.length" class="py-10 text-center text-gray-400 text-sm">No bank transactions found.</div>
                    <div v-else class="overflow-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in bankTxns" :key="t.id"
                                    @click="selectedBank = selectedBank?.id === t.id ? null : t"
                                    class="cursor-pointer border-b border-gray-50 transition-colors"
                                    :class="selectedBank?.id === t.id
                                        ? 'bg-blue-50 ring-1 ring-inset ring-blue-400'
                                        : t.docref ? 'bg-emerald-50/50 hover:bg-emerald-50' : 'hover:bg-amber-50/50'"
                                >
                                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ t.transaction_date }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', dcBadge(t.debit_or_credit).cls]">
                                            {{ dcBadge(t.debit_or_credit).text }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-800 whitespace-nowrap">{{ fmt(t.amount) }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 max-w-[160px] truncate">{{ t.description }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                            :class="t.docref ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                            {{ t.docref ? 'Matched' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Acumatica Bookings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-3.5 flex items-center gap-2.5 shrink-0" style="background:#1e293b;">
                        <div class="w-2.5 h-2.5 rounded-full bg-violet-400 shadow-sm shadow-violet-400/50"></div>
                        <span class="text-[13px] font-semibold text-white">Acumatica Bookings</span>
                        <span class="ml-auto text-xs text-slate-400 bg-slate-700 px-2.5 py-0.5 rounded-full font-medium">{{ acuTxns.length }}</span>
                    </div>
                    <div v-if="!acuTxns.length" class="py-10 text-center text-gray-400 text-sm">No Acumatica bookings found.</div>
                    <div v-else class="overflow-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reference</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cash Account</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in acuTxns" :key="t.RecID"
                                    @click="selectedAcu = selectedAcu?.RecID === t.RecID ? null : t"
                                    class="cursor-pointer border-b border-gray-50 transition-colors"
                                    :class="selectedAcu?.RecID === t.RecID
                                        ? 'bg-violet-50 ring-1 ring-inset ring-violet-400'
                                        : 'hover:bg-violet-50/40'"
                                >
                                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ t.TransactionDate }}</td>
                                    <td class="px-4 py-2.5 font-mono text-gray-600 whitespace-nowrap">{{ t.ReferenceNumber }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ t.CashAccount }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">{{ t.Type }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-800 whitespace-nowrap">{{ fmt(t.Amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Empty state -->
            <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm p-16 flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <GitMerge class="w-8 h-8 text-gray-300" />
                </div>
                <p class="font-semibold text-gray-500">No data to display</p>
                <p class="text-sm text-gray-400">Select account, set a date range, then click Search.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
