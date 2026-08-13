<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { Receipt } from 'lucide-vue-next'

const loading = ref(false)
const transfers = ref([])
const createModal = ref(false)
const companies = ref([])
const cashAccounts = ref([])

async function loadCompanies() {
  try {
    const res = await axios.get('/recon-companies')
    companies.value = res.data ?? []
  } catch {}
}

const form = ref({
    company: '',
    dateFrom: '',
    dateTo: '',
    source: '',
    sourceName: '',
    sourceDesc: '',
    receipt: '',
    receiptName: '',
    receiptDesc: '',
    particulars: '',
    amount: '',
    amountWords: '',
})

const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

function amountToWords(num) {
    if (!num) return ''
    const dg = ['ZERO','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE']
    const tn = ['TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN']
    const tw = ['TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY']
    const th = ['','THOUSAND','MILLION','BILLION']
    const s = num.toString().replace(/,/g,'')
    if (isNaN(s)) return ''
    const x = s.indexOf('.')
    const intPart = x === -1 ? s : s.slice(0, x)
    const n = intPart.split('')
    let str = '', sk = 0
    for (let i = 0; i < n.length; i++) {
        const pos = n.length - i
        if (pos % 3 === 2) {
            if (n[i] === '1') { str += tn[+n[i+1]] + ' '; i++; sk = 1 }
            else if (n[i] !== '0') { str += tw[+n[i]-2] + ' '; sk = 1 }
        } else if (n[i] !== '0') {
            str += dg[+n[i]] + ' '
            if (pos % 3 === 0) str += 'HUNDRED '
            sk = 1
        }
        if (pos % 3 === 1) { if (sk) str += th[Math.floor((pos-1)/3)] + ' '; sk = 0 }
    }
    return str.trim() + ' PESOS'
}

function onAmountChange() {
    form.value.amountWords = amountToWords(form.value.amount)
}

async function loadCashAccounts() {
    if (!form.value.company) return
    try {
        const res = await axios.get('/get-cash-accounts', { params: { company: form.value.company } })
        cashAccounts.value = res.data ?? []
        form.value.source = ''; form.value.sourceName = ''; form.value.sourceDesc = ''
        form.value.receipt = ''; form.value.receiptName = ''; form.value.receiptDesc = ''
    } catch {}
}

function onSourceChange() {
    const acc = cashAccounts.value.find(a => a.CashAccount === form.value.source)
    if (acc) {
        form.value.sourceName = acc.AccountName ?? ''
        form.value.sourceDesc = acc.Description ?? ''
    }
}

function onReceiptChange() {
    const acc = cashAccounts.value.find(a => a.CashAccount === form.value.receipt)
    if (acc) {
        form.value.receiptName = acc.AccountName ?? ''
        form.value.receiptDesc = acc.Description ?? ''
    }
}

async function fetchTransfers() {
    loading.value = true
    try {
        const res = await axios.get('/get-rtof-list')
        transfers.value = res.data ?? []
    } catch { transfers.value = [] }
    finally { loading.value = false }
}

async function createFundTransfer() {
    if (!form.value.company || !form.value.source || !form.value.receipt || !form.value.amount || !form.value.particulars) {
        alert('Please fill in all required fields.')
        return
    }
    try {
        await axios.post('/create-rtof', {
            company: form.value.company,
            date_from: form.value.dateFrom,
            date_to: form.value.dateTo,
            source: form.value.source,
            source_cash_acc: form.value.source,
            source_acc: form.value.sourceName,
            source_desc: form.value.sourceDesc,
            receipt: form.value.receipt,
            receipt_cash_acc: form.value.receipt,
            receipt_acc: form.value.receiptName,
            receipt_desc: form.value.receiptDesc,
            amount: form.value.amount,
            amount_words: form.value.amountWords,
            particulars: form.value.particulars,
        })
        createModal.value = false
        form.value = { company: '', dateFrom: '', dateTo: '', source: '', sourceName: '', sourceDesc: '', receipt: '', receiptName: '', receiptDesc: '', particulars: '', amount: '', amountWords: '' }
        fetchTransfers()
    } catch { alert('Error creating fund transfer.') }
}

onMounted(() => { loadCompanies(); fetchTransfers() })
</script>

<template>
    <Head title="Create FTAF" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <Receipt class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Create FTAF</h1>
                    <p class="text-xs text-gray-400">Request Transfer of Funds (RTOF)</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Fund Transfer List</h3>
                    <button @click="createModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Create Fund Transfer</button>
                </div>

                <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
                <div v-else-if="!transfers.length" class="text-center py-12 text-gray-400">No fund transfers found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RTOF Series No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Particulars</th>
                        </tr></thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="t in transfers" :key="t.Series" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs">{{ t.Series }}</td>
                                <td class="px-4 py-3">{{ t.Source }} <span class="text-gray-400 text-xs">({{ t.SourceCashacc }})</span></td>
                                <td class="px-4 py-3">{{ t.Receipt }} <span class="text-gray-400 text-xs">({{ t.ReceiptCashacc }})</span></td>
                                <td class="px-4 py-3 text-green-700 font-medium">{{ fmt(t.Amount) }}</td>
                                <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ t.Particulars }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Fund Transfer Modal -->
        <div v-if="createModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Fund Transfer Account</h3>
                    <button @click="createModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company <span class="text-red-500">*</span></label>
                        <select v-model="form.company" @change="loadCashAccounts" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select company...</option>
                            <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Period From <span class="text-red-500">*</span></label>
                        <input v-model="form.dateFrom" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Period To <span class="text-red-500">*</span></label>
                        <input v-model="form.dateTo" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Source Account <span class="text-red-500">*</span></label>
                        <select v-model="form.source" @change="onSourceChange" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select account...</option>
                            <option v-for="a in cashAccounts" :key="a.CashAccount" :value="a.CashAccount">{{ a.CashAccount }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Account Name</label>
                        <input v-model="form.sourceName" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Account Description</label>
                        <input v-model="form.sourceDesc" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Recipient Account <span class="text-red-500">*</span></label>
                        <select v-model="form.receipt" @change="onReceiptChange" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select account...</option>
                            <option v-for="a in cashAccounts" :key="a.CashAccount" :value="a.CashAccount">{{ a.CashAccount }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Account Name</label>
                        <input v-model="form.receiptName" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Account Description</label>
                        <input v-model="form.receiptDesc" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Particulars <span class="text-red-500">*</span></label>
                    <textarea v-model="form.particulars" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Enter particulars..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Amount of Payment <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-l">₱</span>
                            <input v-model="form.amount" @input="onAmountChange" type="number" class="flex-1 border border-gray-300 rounded-r px-3 py-2 text-sm" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Amount in Words</label>
                        <input v-model="form.amountWords" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 text-xs" />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button @click="createModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Close</button>
                    <button @click="createFundTransfer" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded font-medium">Create Fund Transfer</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
