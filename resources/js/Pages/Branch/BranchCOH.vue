<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { Wallet } from 'lucide-vue-next'

const loading = ref(false)
const cashAccount = ref('')
const cashAccounts = ref([])
const report = ref(null)
const denominations = ref([{ denomination: 1000, count: 0 }])
const withdrawals = ref([])
const deposits = ref([])
const payments = ref([])
const withdrawModal = ref(false)
const depositModal = ref(false)
const paymentModal = ref(false)

const denominationOptions = [1000, 500, 200, 100, 50, 20, 10, 5, 1]
const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

const totalDenom = () => denominations.value.reduce((s, r) => s + (r.denomination * r.count), 0)
const totalWith = () => withdrawals.value.reduce((s, r) => s + (+r.amount || 0), 0)
const totalDep = () => deposits.value.reduce((s, r) => s + (+r.amount || 0), 0)
const totalPay = () => payments.value.reduce((s, r) => s + (+r.amount || 0), 0)

async function loadAccounts() {
    try {
        const res = await axios.get('/get-cash-accounts')
        cashAccounts.value = res.data ?? []
    } catch {}
}

async function loadReport() {
    if (!cashAccount.value) return
    loading.value = true
    try {
        const res = await axios.get('/get-branch-coh', { params: { cash_account: cashAccount.value } })
        report.value = res.data
    } catch {} finally { loading.value = false }
}

function addDenom() { denominations.value.push({ denomination: 1000, count: 0 }) }
function removeDenom() { if (denominations.value.length > 1) denominations.value.pop() }
function addWithdraw() { withdrawals.value.push({ bank: '', amount: '' }) }
function removeWithdraw() { if (withdrawals.value.length > 0) withdrawals.value.pop() }
function applyWithdraw() { withdrawModal.value = false }
function addDeposit() { deposits.value.push({ bank: '', amount: '' }) }
function removeDeposit() { if (deposits.value.length > 0) deposits.value.pop() }
function applyDeposit() { depositModal.value = false }
function addPayment() { payments.value.push({ particular: '', reference: '', amount: '' }) }
function removePayment() { if (payments.value.length > 0) payments.value.pop() }
function applyPayment() { paymentModal.value = false }

async function saveCashReport() {
    const total = totalDenom()
    const coh = report.value?.coh_balance ?? 0
    if (Math.abs(total - coh) > 0.01) {
        alert('Total denomination does not match Cash On Hand balance.')
        return
    }
    try {
        await axios.post('/save-branch-coh', {
            cash_account: cashAccount.value,
            collection: report.value?.collection ?? 0,
            withdrawal: totalWith(),
            deposit: totalDep(),
            payment: totalPay(),
            balance: coh,
            denominations: denominations.value,
            withdrawals: withdrawals.value,
            deposits: deposits.value,
            payments: payments.value,
        })
        alert('Cash Report saved successfully.')
        loadReport()
    } catch { alert('Error saving cash report.') }
}

onMounted(loadAccounts)
</script>

<template>
    <Head title="Branch COH Report" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <Wallet class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Branch COH</h1>
                    <p class="text-xs text-gray-400">Branch cash on-hand denomination report</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Account Selector -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cash Account</label>
                        <select v-model="cashAccount" @change="loadReport" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select cash account...</option>
                            <option v-for="a in cashAccounts" :key="a.CashAccount" :value="a.CashAccount">{{ a.CashAccount }}</option>
                        </select>
                    </div>
                    <button @click="loadReport" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Load</button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

            <template v-if="!loading && report && cashAccount">
                <!-- COH Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Cash On-Hand Summary</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <label class="w-56 text-sm text-gray-700">Total Cash Collection for Today</label>
                            <input v-model="report.collection" type="number" class="border border-gray-300 rounded px-3 py-2 text-sm w-44" />
                            <span class="text-xs text-gray-500 italic">{{ fmt(report.collection) }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-56 text-sm text-gray-700">Cash Withdrawal for Today</label>
                            <div class="flex items-center gap-2">
                                <input :value="fmt(totalWith())" readonly class="border border-gray-300 rounded px-3 py-2 text-sm w-44 bg-gray-50" />
                                <button @click="withdrawModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-xs">View</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-56 text-sm text-gray-700">Cash Deposited for Today</label>
                            <div class="flex items-center gap-2">
                                <input :value="fmt(totalDep())" readonly class="border border-gray-300 rounded px-3 py-2 text-sm w-44 bg-gray-50" />
                                <button @click="depositModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-xs">View</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-56 text-sm text-gray-700">Cash Payments for Today</label>
                            <div class="flex items-center gap-2">
                                <input :value="fmt(totalPay())" readonly class="border border-gray-300 rounded px-3 py-2 text-sm w-44 bg-gray-50" />
                                <button @click="paymentModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-xs">View</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-56 text-sm text-gray-700 font-semibold">Cash On Hand for Today</label>
                            <input v-model="report.coh_balance" type="number" class="border border-gray-300 rounded px-3 py-2 text-sm w-44 font-semibold" />
                            <span class="text-xs text-gray-500 italic">{{ fmt(report.coh_balance) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Denomination Breakdown -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Cash On-Hand Breakdown (Denominations)</h3>
                    <table class="min-w-full divide-y divide-gray-200 text-sm mb-3">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Denomination</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cash Value</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(row, i) in denominations" :key="i">
                                <td class="px-4 py-2">
                                    <select v-model="row.denomination" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <option v-for="d in denominationOptions" :key="d" :value="d">{{ d }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input v-model.number="row.count" type="number" min="0" class="border border-gray-300 rounded px-2 py-1 text-sm w-24" />
                                </td>
                                <td class="px-4 py-2 text-green-700 font-medium">{{ fmt(row.denomination * row.count) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex items-center justify-between">
                        <div class="flex gap-2">
                            <button @click="removeDenom" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">- Remove</button>
                            <button @click="addDenom" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">+ Add</button>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-600 mr-3">Total:</span>
                            <span class="font-semibold text-lg text-gray-800">{{ fmt(totalDenom()) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button @click="saveCashReport" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded text-sm font-medium">Save Cash Report</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Withdrawal Modal -->
        <div v-if="withdrawModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold mb-4">Cash Withdrawal</h3>
                <table class="min-w-full text-sm mb-3">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Bank Name</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="(r, i) in withdrawals" :key="i" class="border-t">
                            <td class="px-3 py-1"><input v-model="r.bank" type="text" class="border border-gray-300 rounded px-2 py-1 text-sm w-full" /></td>
                            <td class="px-3 py-1"><input v-model="r.amount" type="number" class="border border-gray-300 rounded px-2 py-1 text-sm w-28" /></td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex gap-2">
                        <button @click="removeWithdraw" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">- Remove</button>
                        <button @click="addWithdraw" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">+ Add</button>
                    </div>
                    <span class="font-semibold text-sm">Total: {{ fmt(totalWith()) }}</span>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="withdrawModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Close</button>
                    <button @click="applyWithdraw" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Apply</button>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="depositModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold mb-4">Cash Deposit</h3>
                <table class="min-w-full text-sm mb-3">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Bank Name</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="(r, i) in deposits" :key="i" class="border-t">
                            <td class="px-3 py-1"><input v-model="r.bank" type="text" class="border border-gray-300 rounded px-2 py-1 text-sm w-full" /></td>
                            <td class="px-3 py-1"><input v-model="r.amount" type="number" class="border border-gray-300 rounded px-2 py-1 text-sm w-28" /></td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex gap-2">
                        <button @click="removeDeposit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">- Remove</button>
                        <button @click="addDeposit" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">+ Add</button>
                    </div>
                    <span class="font-semibold text-sm">Total: {{ fmt(totalDep()) }}</span>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="depositModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Close</button>
                    <button @click="applyDeposit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Apply</button>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl">
                <h3 class="text-lg font-semibold mb-4">Cash Payments</h3>
                <table class="min-w-full text-sm mb-3">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Particulars</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Reference</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="(r, i) in payments" :key="i" class="border-t">
                            <td class="px-3 py-1"><input v-model="r.particular" type="text" class="border border-gray-300 rounded px-2 py-1 text-sm w-full" /></td>
                            <td class="px-3 py-1"><input v-model="r.reference" type="text" class="border border-gray-300 rounded px-2 py-1 text-sm w-28" /></td>
                            <td class="px-3 py-1"><input v-model="r.amount" type="number" class="border border-gray-300 rounded px-2 py-1 text-sm w-24" /></td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex gap-2">
                        <button @click="removePayment" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">- Remove</button>
                        <button @click="addPayment" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">+ Add</button>
                    </div>
                    <span class="font-semibold text-sm">Total: {{ fmt(totalPay()) }}</span>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="paymentModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Close</button>
                    <button @click="applyPayment" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Apply</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
