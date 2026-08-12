<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ArrowRightLeft } from 'lucide-vue-next'

const loading = ref(false)
const accountNo = ref('')
const txnDate = ref(new Date().toISOString().split('T')[0])
const bankTxns = ref([])
const acuTxns = ref([])
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
    if (!selectedBank.value || !selectedAcu.value) return alert('Select one bank row and one Acumatica row.')
    await axios.post('/bind-transaction', { bank_transaction_id: selectedBank.value.id, acumatica_ref: selectedAcu.value.ReferenceNumber })
    selectedBank.value = null; selectedAcu.value = null; fetchAll()
}

const rowBg = (row) => row.docref ? 'bg-green-50' : 'bg-yellow-50'
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

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Account No</label>
                        <input v-model="accountNo" type="text" placeholder="e.g. 001-234-5678" class="border border-gray-300 rounded px-3 py-2 text-sm w-52" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                        <input v-model="txnDate" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                    <button @click="fetchAll" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Fetch</button>
                    <button v-if="selectedBank || selectedAcu" @click="bindTransaction" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium">Bind Selected</button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Bank Transactions -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                        Bank Transactions ({{ bankTxns.length }})
                    </h3>
                    <div v-if="!bankTxns.length" class="text-center py-8 text-gray-400 text-sm">No bank transactions.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Status</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="t in bankTxns" :key="t.id" @click="selectedBank = selectedBank?.id === t.id ? null : t"
                                    :class="[selectedBank?.id === t.id ? 'ring-2 ring-blue-500' : rowBg(t), 'cursor-pointer hover:opacity-90']">
                                    <td class="px-2 py-2">{{ t.debit_or_credit }}</td>
                                    <td class="px-2 py-2 text-green-700 font-medium">{{ fmt(t.amount) }}</td>
                                    <td class="px-2 py-2">{{ t.transaction_date }}</td>
                                    <td class="px-2 py-2 max-w-xs truncate">{{ t.additional_info }}</td>
                                    <td class="px-2 py-2">
                                        <span :class="t.docref ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-1 py-0.5 rounded text-xs">{{ t.docref ? 'Matched' : 'Pending' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Acumatica Entries -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span>
                        Acumatica Bookings ({{ acuTxns.length }})
                    </h3>
                    <div v-if="!acuTxns.length" class="text-center py-8 text-gray-400 text-sm">No Acumatica entries.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Type</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="t in acuTxns" :key="t.RecID" @click="selectedAcu = selectedAcu?.RecID === t.RecID ? null : t"
                                    :class="['cursor-pointer hover:bg-purple-50', selectedAcu?.RecID === t.RecID ? 'ring-2 ring-purple-500 bg-purple-50' : '']">
                                    <td class="px-2 py-2 font-mono text-xs">{{ t.ReferenceNumber }}</td>
                                    <td class="px-2 py-2 text-green-700 font-medium">{{ fmt(t.Amount) }}</td>
                                    <td class="px-2 py-2">{{ t.TransactionDate }}</td>
                                    <td class="px-2 py-2">{{ t.Type }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p v-if="!loading && (bankTxns.length || acuTxns.length)" class="text-xs text-gray-500 mt-2">Click a bank row and an Acumatica row to select, then click "Bind Selected".</p>
        </div>
    </AuthenticatedLayout>
</template>
