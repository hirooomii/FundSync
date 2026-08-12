<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { GitMerge, Search, Link2, RefreshCw } from 'lucide-vue-next'

const loading = ref(false)
const bankTxns = ref([])
const acuTxns  = ref([])
const selectedBank = ref(null)
const selectedAcu  = ref(null)
const filters = ref({ company: '', bank_name: '', account_no: '', date_from: '' })

const companies  = ref([])
const banks      = ref([])
const accounts   = ref([])

const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

const summary = () => {
    const matched = bankTxns.value.filter(t => t.docref).length
    const total   = bankTxns.value.length
    return { total, matched, unmatched: total - matched }
}

async function loadCompanies() {
    try {
        const res = await axios.get('/recon-companies')
        companies.value = res.data ?? []
    } catch {}
}

async function onCompanyChange() {
    filters.value.bank_name = ''
    filters.value.account_no = ''
    banks.value = []
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
    if (!filters.value.account_no && !filters.value.date_from) {
        alert('Select an Account No or Date to search.')
        return
    }
    loading.value = true
    bankTxns.value = []; acuTxns.value = []
    selectedBank.value = null; selectedAcu.value = null
    try {
        const [bRes, aRes] = await Promise.all([
            axios.get('/get-daily-transactions', { params: { account_no: filters.value.account_no, date: filters.value.date_from } }),
            axios.get('/get-acumatica-entries',  { params: { account_no: filters.value.account_no, date: filters.value.date_from } }),
        ])
        bankTxns.value = bRes.data?.data ?? bRes.data ?? []
        acuTxns.value  = aRes.data?.data ?? aRes.data ?? []
    } catch { bankTxns.value = []; acuTxns.value = [] }
    finally  { loading.value = false }
}

async function bindTransaction() {
    if (!selectedBank.value || !selectedAcu.value) { alert('Select one row from each side.'); return }
    try {
        await axios.post('/bind-transaction', {
            bank_transaction_id: selectedBank.value.id,
            acumatica_ref: selectedAcu.value.ReferenceNumber,
        })
        selectedBank.value = null; selectedAcu.value = null
        fetchData()
    } catch { alert('Bind failed.') }
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
                <div class="px-5 py-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Company</label>
                        <select v-model="filters.company" @change="onCompanyChange"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">All Companies</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Bank Name</label>
                        <select v-model="filters.bank_name" @change="onBankChange"
                            :disabled="!filters.company"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">All Banks</option>
                            <option v-for="b in banks" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Account No</label>
                        <select v-model="filters.account_no"
                            :disabled="!filters.company"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Select Account</option>
                            <option v-for="a in accounts" :key="a.AccountNo" :value="a.AccountNo">
                                {{ a.AccountNo }}{{ a.AccountName ? ' — ' + a.AccountName : '' }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Date</label>
                        <input v-model="filters.date_from" type="date"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    </div>
                    <div class="flex flex-col gap-2 justify-end">
                        <button @click="fetchData" :disabled="loading"
                            class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-blue-200">
                            <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
                            {{ loading ? 'Loading…' : 'Search' }}
                        </button>
                        <button v-if="selectedBank && selectedAcu" @click="bindTransaction"
                            class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-200">
                            <Link2 class="w-4 h-4" />
                            Bind Selected
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
                        <p class="text-2xl font-bold text-gray-800">{{ summary().total }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-emerald-200 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <Link2 class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div>
                        <p class="text-[11px] text-emerald-600 uppercase tracking-wide font-semibold">Matched</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ summary().matched }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-amber-200 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                        <Search class="w-5 h-5 text-amber-500" />
                    </div>
                    <div>
                        <p class="text-[11px] text-amber-600 uppercase tracking-wide font-semibold">Pending</p>
                        <p class="text-2xl font-bold text-amber-600">{{ summary().unmatched }}</p>
                    </div>
                </div>
            </div>

            <!-- Hint bar -->
            <div v-if="(selectedBank || selectedAcu) && !(selectedBank && selectedAcu)"
                class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 text-sm text-blue-700 font-medium flex items-center gap-2">
                <Link2 class="w-4 h-4 text-blue-500" />
                {{ selectedBank ? 'Now select an Acumatica row to bind.' : 'Now select a Bank row to bind.' }}
            </div>

            <!-- Side-by-side tables -->
            <div v-if="!loading && (bankTxns.length || acuTxns.length)" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Bank Transactions -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 flex items-center gap-2.5" style="background: #1e293b;">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400 shadow-sm shadow-blue-400/50"></div>
                        <span class="text-[13px] font-semibold text-white">Bank Transactions</span>
                        <span class="ml-auto text-xs text-slate-400 bg-slate-700 px-2.5 py-0.5 rounded-full font-medium">{{ bankTxns.length }}</span>
                    </div>
                    <div v-if="!bankTxns.length" class="py-10 text-center text-gray-400 text-sm">No bank transactions found.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in bankTxns" :key="t.id"
                                    @click="selectedBank = selectedBank?.id === t.id ? null : t"
                                    class="cursor-pointer border-b border-gray-50 transition-colors text-sm"
                                    :class="selectedBank?.id === t.id
                                        ? 'bg-blue-50 border-l-2 border-l-blue-500'
                                        : t.docref ? 'bg-emerald-50/40 hover:bg-emerald-50' : 'hover:bg-amber-50/40'"
                                >
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ t.transaction_date }}</td>
                                    <td class="px-4 py-2.5 text-gray-700 max-w-[150px] truncate text-xs">{{ t.additional_info }}</td>
                                    <td class="px-4 py-2.5 font-semibold text-gray-800 text-xs">{{ fmt(t.amount) }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                            :class="t.docref ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                            {{ t.docref ? 'Matched' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Acumatica Entries -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 flex items-center gap-2.5" style="background: #1e293b;">
                        <div class="w-2.5 h-2.5 rounded-full bg-violet-400 shadow-sm shadow-violet-400/50"></div>
                        <span class="text-[13px] font-semibold text-white">Acumatica Bookings</span>
                        <span class="ml-auto text-xs text-slate-400 bg-slate-700 px-2.5 py-0.5 rounded-full font-medium">{{ acuTxns.length }}</span>
                    </div>
                    <div v-if="!acuTxns.length" class="py-10 text-center text-gray-400 text-sm">No Acumatica bookings found.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reference</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cash Account</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in acuTxns" :key="t.RecID"
                                    @click="selectedAcu = selectedAcu?.RecID === t.RecID ? null : t"
                                    class="cursor-pointer border-b border-gray-50 transition-colors"
                                    :class="selectedAcu?.RecID === t.RecID
                                        ? 'bg-violet-50 border-l-2 border-l-violet-500'
                                        : 'hover:bg-violet-50/30'"
                                >
                                    <td class="px-4 py-2.5 font-mono text-gray-500 text-xs">{{ t.ReferenceNumber }}</td>
                                    <td class="px-4 py-2.5 text-gray-700 text-xs">{{ t.CashAccount }}</td>
                                    <td class="px-4 py-2.5 font-semibold text-gray-800 text-xs">{{ fmt(t.Amount) }}</td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ t.TransactionDate }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
                <div class="w-10 h-10 border-[3px] border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm text-gray-400">Fetching data…</p>
            </div>

            <!-- Empty state -->
            <div v-if="!loading && !bankTxns.length && !acuTxns.length"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-16 flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <GitMerge class="w-8 h-8 text-gray-300" />
                </div>
                <p class="font-semibold text-gray-500">No data to display</p>
                <p class="text-sm text-gray-400">Enter an account number and date above, then click Search.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
