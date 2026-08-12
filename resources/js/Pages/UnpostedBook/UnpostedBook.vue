<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { BookMarked } from 'lucide-vue-next'

const loading = ref(false)
const records = ref([])
const pagination = ref({})
const filters = ref({ company: '', bank_name: '', account_no: '', date_from: '', date_to: '' })
const noteModal = ref(false)
const selectedTxn = ref(null)
const note = ref('')
const comments = ref([])
const commentsLoading = ref(false)

const companies = ['ROPALI CORPORATION', 'MOTORBELLE CORPORATION', 'MOTORALI CORPORATION', 'MOTOROBEE CORPORATION']
const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

async function fetchEntries() {
    loading.value = true
    try {
        const res = await axios.get('/get-unposted-entries', { params: filters.value })
        records.value = res.data.data ?? []
        pagination.value = res.data
    } catch { records.value = [] }
    finally { loading.value = false }
}

async function openNote(txn) {
    selectedTxn.value = txn; note.value = txn.Note ?? ''; noteModal.value = true
    commentsLoading.value = true; comments.value = []
    try {
        const res = await axios.get('/get-comments', { params: { transaction_id: txn.RecID } })
        comments.value = res.data ?? []
    } catch {} finally { commentsLoading.value = false }
}

async function saveNote() {
    await axios.post('/save-note', { transaction_id: selectedTxn.value.RecID, note: note.value })
    noteModal.value = false; fetchEntries()
}

function copyRef(ref) {
    navigator.clipboard.writeText(ref)
}
</script>

<template>
    <Head title="Unposted Book" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <BookMarked class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Unposted Book</h1>
                    <p class="text-xs text-gray-400">Review Acumatica entries pending posting</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-wrap gap-3 mb-4">
                    <select v-model="filters.company" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="">All Companies</option>
                        <option v-for="c in companies" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <input v-model="filters.bank_name" type="text" placeholder="Bank Name" class="border border-gray-300 rounded px-3 py-2 text-sm w-36" />
                    <input v-model="filters.account_no" type="text" placeholder="Account No" class="border border-gray-300 rounded px-3 py-2 text-sm w-36" />
                    <input v-model="filters.date_from" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm" />
                    <input v-model="filters.date_to" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm" />
                    <button @click="fetchEntries" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Fetch</button>
                </div>

                <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
                <div v-else-if="!records.length" class="text-center py-12 text-gray-400">Use filters above and click Fetch.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cash Account</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="row in records" :key="row.RecID" class="hover:bg-gray-50">
                                <td class="px-3 py-3 text-gray-900">{{ row.Company }}</td>
                                <td class="px-3 py-3 text-green-700 font-medium">{{ fmt(row.Amount) }}</td>
                                <td class="px-3 py-3 text-gray-900">{{ row.Type }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1">
                                        <span class="font-mono text-xs">{{ row.ReferenceNumber }}</span>
                                        <button @click="copyRef(row.ReferenceNumber)" class="text-gray-400 hover:text-gray-600 text-xs" title="Copy">⎘</button>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-gray-900">{{ row.CashAccount }}</td>
                                <td class="px-3 py-3 text-gray-700 max-w-xs truncate">{{ row.TransactionDesc }}</td>
                                <td class="px-3 py-3 text-gray-500">{{ row.TransactionDate }}</td>
                                <td class="px-3 py-3 text-gray-700 max-w-xs truncate">{{ row.Note }}</td>
                                <td class="px-3 py-3">
                                    <button @click="openNote(row)" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">Note</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Note Modal -->
        <div v-if="noteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Add/Edit Note</h3>
                    <button @click="noteModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <textarea v-model="note" rows="4" placeholder="Enter note..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500"></textarea>
                <div v-if="comments.length" class="mb-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Previous Notes</p>
                    <div v-for="c in comments" :key="c.RecID" class="bg-gray-50 rounded p-2 mb-2 text-sm">
                        <p class="text-gray-800">{{ c.Note }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ c.EditDate }}</p>
                    </div>
                </div>
                <div v-if="commentsLoading" class="text-xs text-gray-400 mb-4">Loading previous notes...</div>
                <div class="flex justify-end gap-2">
                    <button @click="noteModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                    <button @click="saveNote" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Save Note</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
