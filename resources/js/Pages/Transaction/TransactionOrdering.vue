<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { ArrowUpDown } from 'lucide-vue-next'

const loading = ref(false)
const groups = ref([])
const previewModal = ref(false)
const previewLoading = ref(false)
const previewItems = ref([])
const selectedAccount = ref(null)

const fmt = v => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v ?? 0)

async function fetchGroups() {
    loading.value = true
    try {
        const res = await axios.get('/get-transaction-groups')
        groups.value = res.data ?? []
    } catch { groups.value = [] }
    finally { loading.value = false }
}

async function openReorder(accountNo) {
    selectedAccount.value = accountNo; previewModal.value = true; previewLoading.value = true
    try {
        const res = await axios.get('/preview-transaction-order', { params: { account_no: accountNo } })
        previewItems.value = res.data ?? []
    } catch {} finally { previewLoading.value = false }
}

function moveUp(idx) {
    if (idx === 0) return
    const arr = [...previewItems.value]
    ;[arr[idx - 1], arr[idx]] = [arr[idx], arr[idx - 1]]
    previewItems.value = arr
}

function moveDown(idx) {
    if (idx === previewItems.value.length - 1) return
    const arr = [...previewItems.value]
    ;[arr[idx], arr[idx + 1]] = [arr[idx + 1], arr[idx]]
    previewItems.value = arr
}

async function saveOrder() {
    const ordered_ids = previewItems.value.map(t => t.id)
    await axios.post('/save-transaction-order', { account_no: selectedAccount.value, ordered_ids })
    previewModal.value = false
}

onMounted(fetchGroups)
</script>

<template>
    <Head title="Transaction Ordering" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <ArrowUpDown class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Transaction Ordering</h1>
                    <p class="text-xs text-gray-400">Arrange and prioritize pending transactions</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
                <div v-else-if="!groups.length" class="text-center py-12 text-gray-400">No transaction groups found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction Count</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="g in groups" :key="g.account_no" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-blue-600">{{ g.account_no }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ g.count }}</td>
                                <td class="px-4 py-3 text-sm text-green-700 font-medium">{{ fmt(g.total_amount) }}</td>
                                <td class="px-4 py-3">
                                    <button @click="openReorder(g.account_no)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">Reorder</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reorder Modal -->
        <div v-if="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Reorder: {{ selectedAccount }}</h3>
                    <button @click="previewModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <p class="text-xs text-gray-500 mb-3">Use the arrows to reorder transactions, then click Save.</p>
                <div v-if="previewLoading" class="text-center py-8 text-gray-500">Loading...</div>
                <div v-else>
                    <div v-for="(t, idx) in previewItems" :key="t.id" class="flex items-center gap-3 border border-gray-200 rounded p-2 mb-2 hover:bg-gray-50">
                        <span class="text-sm font-bold text-gray-400 w-6 text-center">{{ idx + 1 }}</span>
                        <div class="flex-1 text-sm">
                            <span class="font-medium text-gray-800">{{ t.transaction_date }}</span>
                            <span class="text-gray-500 ml-3">{{ t.additional_info?.slice(0, 50) }}</span>
                            <span class="text-green-700 font-medium ml-3">{{ fmt(t.amount) }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <button @click="moveUp(idx)" :disabled="idx === 0" class="text-gray-400 hover:text-gray-700 disabled:opacity-30 text-xs leading-none">▲</button>
                            <button @click="moveDown(idx)" :disabled="idx === previewItems.length - 1" class="text-gray-400 hover:text-gray-700 disabled:opacity-30 text-xs leading-none">▼</button>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button @click="previewModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                        <button @click="saveOrder" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Save Order</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
