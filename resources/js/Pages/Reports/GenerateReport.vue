<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { BarChart2 } from 'lucide-vue-next'

const loading = ref(false)
const company = ref('')
const bankName = ref('')
const accountNo = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const companies = ref([])
const banks = ref([])
const accountNumbers = ref([])
const records = ref([])

const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

async function loadCompanies() {
    try {
        const res = await axios.get('/recon-companies')
        companies.value = res.data ?? []
    } catch {}
}

async function onCompanyChange() {
    bankName.value = ''
    accountNo.value = ''
    banks.value = []
    accountNumbers.value = []
    if (!company.value) return
    try {
        const res = await axios.get('/recon-banks', { params: { company: company.value } })
        banks.value = res.data ?? []
    } catch {}
}

async function onBankChange() {
    accountNo.value = ''
    accountNumbers.value = []
    try {
        const res = await axios.get('/recon-accounts', { params: { company: company.value, bank_name: bankName.value } })
        accountNumbers.value = res.data ?? []
    } catch {}
}

async function generate() {
    if (!accountNo.value || !dateFrom.value || !dateTo.value) {
        alert('Please fill in all required fields.')
        return
    }
    loading.value = true
    try {
        const res = await axios.get('/generate-transaction-report', {
            params: { account: accountNo.value, datefrom: dateFrom.value, dateto: dateTo.value }
        })
        records.value = res.data ?? []
    } catch { records.value = [] }
    finally { loading.value = false }
}

function exportCsv() {
    if (!records.value.length) return
    const headers = ['Bank','Account','Type','Bank Amount','Date','Description','Reference','Running Bal','Acu Account','Acu Reference','Cash Account','Acu Type','Acu Amount','Acu Date','Acu Description','Excess','Note']
    const rows = records.value.map(r => [
        r.bank_code, r.account_no, r.CreditOrDebit, r.BankAmount ?? '', r.transaction_date, r.description, r.reference, r.runningbal ?? '',
        r.AccountNo, r.ReferenceNumber, r.CashAccount, r.TypeDC, r.Amount ?? '', r.TransactionDate, r.TransactionDesc, r.decimal ?? '---',
        [r.comment, r.note].filter(Boolean).join(' - ')
    ])
    const csv = [headers, ...rows].map(r => r.map(v => `"${String(v ?? '').replace(/"/g,'""')}"`).join(',')).join('\n')
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a'); a.href = url; a.download = 'reconciliation_report.csv'; a.click()
    URL.revokeObjectURL(url)
}

onMounted(loadCompanies)
</script>

<template>
    <Head title="Generate Report" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <BarChart2 class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Generate Report</h1>
                    <p class="text-xs text-gray-400">Reconciled transaction export report</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Company <span class="text-red-500">*</span></label>
                        <select v-model="company" @change="onCompanyChange" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select company...</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bank Name <span class="text-red-500">*</span></label>
                        <select v-model="bankName" @change="onBankChange" :disabled="!company" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <option value="">Select bank...</option>
                            <option v-for="b in banks" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Account Number <span class="text-red-500">*</span></label>
                        <select v-model="accountNo" :disabled="!company" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <option value="">Select account...</option>
                            <option v-for="a in accountNumbers" :key="a.AccountNo" :value="a.AccountNo">{{ a.AccountNo }} — {{ a.AccountName }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">From <span class="text-red-500">*</span></label>
                        <input v-model="dateFrom" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">To <span class="text-red-500">*</span></label>
                        <input v-model="dateTo" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="generate" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Generate</button>
                        <button v-if="records.length" @click="exportCsv" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium" title="Export CSV">CSV</button>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

            <div v-else-if="!records.length && (accountNo || dateFrom)" class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
                No data found for the selected criteria.
            </div>

            <div v-else-if="records.length" class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">{{ records.length }} records found</span>
                    <button @click="exportCsv" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-medium">Export CSV</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead>
                            <tr>
                                <th colspan="8" class="px-4 py-2 text-center text-xs font-semibold uppercase bg-green-700 text-white">Bank Information</th>
                                <th colspan="9" class="px-4 py-2 text-center text-xs font-semibold uppercase bg-blue-700 text-white">Acumatica Booking</th>
                            </tr>
                            <tr class="bg-gray-50">
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase border-r-2 border-gray-300">Running Bal</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cash Account</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Excess</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(r, i) in records" :key="i" :class="r.isPosted ? 'bg-green-50' : 'bg-yellow-50'" class="hover:opacity-90">
                                <td class="px-3 py-2">{{ r.bank_code }}</td>
                                <td class="px-3 py-2">{{ r.account_no }}</td>
                                <td class="px-3 py-2">{{ r.CreditOrDebit }}</td>
                                <td class="px-3 py-2 text-green-700 font-medium">{{ r.BankAmount != null ? fmt(r.BankAmount) : '' }}</td>
                                <td class="px-3 py-2">{{ r.transaction_date }}</td>
                                <td class="px-3 py-2 max-w-xs truncate">{{ r.description }}</td>
                                <td class="px-3 py-2 font-mono">{{ r.reference }}</td>
                                <td class="px-3 py-2 font-medium border-r-2 border-gray-300">{{ r.runningbal != null ? fmt(r.runningbal) : '' }}</td>
                                <td class="px-3 py-2">{{ r.AccountNo }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ r.ReferenceNumber }}</td>
                                <td class="px-3 py-2">{{ r.CashAccount }}</td>
                                <td class="px-3 py-2">{{ r.TypeDC }}</td>
                                <td class="px-3 py-2 text-green-700 font-medium">{{ r.Amount != null ? fmt(r.Amount) : '' }}</td>
                                <td class="px-3 py-2">{{ r.TransactionDate }}</td>
                                <td class="px-3 py-2 max-w-xs truncate">{{ r.TransactionDesc }}</td>
                                <td class="px-3 py-2 text-red-600">{{ r.decimal != null ? fmt(r.decimal) : '---' }}</td>
                                <td class="px-3 py-2 text-gray-600 max-w-xs truncate">{{ [r.comment, r.note].filter(Boolean).join(' - ') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
