<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { Repeat2 } from 'lucide-vue-next'

const filterCompany = ref('')
const filterBank = ref('')
const companies = ref([])
const bankOptions = ref([])
const accountOptions = ref([])
const accountNo = ref('')
const txnDate = ref(new Date().toISOString().split('T')[0])

async function loadCompanies() {
    try {
        const res = await axios.get('/recon-companies')
        companies.value = res.data ?? []
    } catch {}
}

async function onCompanyChange() {
    filterBank.value = ''
    accountNo.value = ''
    bankOptions.value = []
    accountOptions.value = []
    if (!filterCompany.value) return
    try {
        const res = await axios.get('/recon-banks', { params: { company: filterCompany.value } })
        bankOptions.value = res.data ?? []
    } catch {}
}

async function onBankChange() {
    accountNo.value = ''
    accountOptions.value = []
    try {
        const res = await axios.get('/recon-accounts', { params: { company: filterCompany.value, bank_name: filterBank.value } })
        accountOptions.value = res.data ?? []
    } catch {}
}

onMounted(loadCompanies)
const loading = ref(false)
const bankTxns = ref([])
const acuTxns = ref([])
const activeTab = ref('bank')
const selectedBank = ref(null)
const selectedAcu = ref(null)

const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

async function fetchAll() {
    if (!accountNo.value || !txnDate.value) return
    loading.value = true
    try {
        const [bRes, aRes] = await Promise.all([
            axios.get('/get-daily-transactions', { params: { account_no: accountNo.value, date: txnDate.value } }),
            axios.get('/get-acumatica-entries', { params: { account_no: accountNo.value, date: txnDate.value } })
        ])
        bankTxns.value = bRes.data ?? []
        acuTxns.value = aRes.data ?? []
    } catch {} finally { loading.value = false }
}

async function bindTransaction() {
    if (!selectedBank.value || !selectedAcu.value) return alert('Select one row from each side.')
    await axios.post('/bind-transaction', { bank_transaction_id: selectedBank.value.id, acumatica_ref: selectedAcu.value.ReferenceNumber })
    selectedBank.value = null; selectedAcu.value = null; fetchAll()
}
</script>

<template>
    <Head title="Branch Reconciliation" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <Repeat2 class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Branch Reconciliation</h1>
                    <p class="text-xs text-gray-400">Reconcile transactions at branch level</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Company</label>
                        <select v-model="filterCompany" @change="onCompanyChange" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Select company...</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bank</label>
                        <select v-model="filterBank" @change="onBankChange" :disabled="!filterCompany" class="border border-gray-300 rounded px-3 py-2 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <option value="">Select bank...</option>
                            <option v-for="b in bankOptions" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Account No</label>
                        <select v-model="accountNo" :disabled="!filterCompany" class="border border-gray-300 rounded px-3 py-2 text-sm w-52 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <option value="">Select account...</option>
                            <option v-for="a in accountOptions" :key="a.AccountNo" :value="a.AccountNo">{{ a.AccountNo }} — {{ a.AccountName }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                        <input v-model="txnDate" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                    <button @click="fetchAll" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Fetch</button>
                    <button v-if="selectedBank && selectedAcu" @click="bindTransaction" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium">Bind Selected</button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow">
                <div class="flex border-b">
                    <button @click="activeTab = 'bank'" :class="activeTab === 'bank' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'" class="px-6 py-3 text-sm font-medium">
                        Bank Transactions ({{ bankTxns.length }})
                    </button>
                    <button @click="activeTab = 'acumatica'" :class="activeTab === 'acumatica' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'" class="px-6 py-3 text-sm font-medium">
                        Acumatica Bookings ({{ acuTxns.length }})
                    </button>
                </div>
                <div class="p-4">
                    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>
                    <div v-else-if="activeTab === 'bank'">
                        <div v-if="!bankTxns.length" class="text-center py-8 text-gray-400">No bank transactions.</div>
                        <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="t in bankTxns" :key="t.id" @click="selectedBank = selectedBank?.id === t.id ? null : t"
                                    :class="['cursor-pointer', selectedBank?.id === t.id ? 'bg-blue-50 ring-1 ring-blue-400' : t.docref ? 'bg-green-50' : 'bg-yellow-50 hover:bg-yellow-100']">
                                    <td class="px-4 py-3">{{ t.transaction_date }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate">{{ t.additional_info }}</td>
                                    <td class="px-4 py-3 text-green-700 font-medium">{{ fmt(t.amount) }}</td>
                                    <td class="px-4 py-3">{{ t.debit_or_credit }}</td>
                                    <td class="px-4 py-3"><span :class="t.docref ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded text-xs">{{ t.docref ? 'Matched' : 'Pending' }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else>
                        <div v-if="!acuTxns.length" class="text-center py-8 text-gray-400">No Acumatica bookings.</div>
                        <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cash Account</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="t in acuTxns" :key="t.RecID" @click="selectedAcu = selectedAcu?.RecID === t.RecID ? null : t"
                                    :class="['cursor-pointer hover:bg-purple-50', selectedAcu?.RecID === t.RecID ? 'bg-purple-50 ring-1 ring-purple-400' : '']">
                                    <td class="px-4 py-3 font-mono text-xs">{{ t.ReferenceNumber }}</td>
                                    <td class="px-4 py-3">{{ t.CashAccount }}</td>
                                    <td class="px-4 py-3 text-green-700 font-medium">{{ fmt(t.Amount) }}</td>
                                    <td class="px-4 py-3">{{ t.Type }}</td>
                                    <td class="px-4 py-3">{{ t.TransactionDate }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
