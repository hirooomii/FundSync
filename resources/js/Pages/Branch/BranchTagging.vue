<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { Users } from 'lucide-vue-next'

const users = ref([])
const branches = ref([])
const taggings = ref([])
const selectedUser = ref(null)
const selectedBranch = ref(null)
const loading = ref(false)

async function loadAll() {
    loading.value = true
    try {
        const [uRes, bRes, tRes] = await Promise.all([
            axios.get('/users'),
            axios.get('/get-branch-list'),
            axios.get('/get-branch-tagging')
        ])
        users.value = uRes.data ?? []
        branches.value = bRes.data?.data ?? bRes.data ?? []
        taggings.value = tRes.data ?? []
    } catch {} finally { loading.value = false }
}

async function saveTagging() {
    if (!selectedUser.value || !selectedBranch.value) return alert('Select a user and a branch.')
    await axios.post('/save-branch-tagging', { TrainorID: selectedUser.value, BranchCode: selectedBranch.value })
    selectedUser.value = null; selectedBranch.value = null; loadAll()
}

async function deleteTagging(id) {
    if (!confirm('Remove this tagging?')) return
    await axios.delete(`/delete-branch-tagging/${id}`)
    loadAll()
}

onMounted(loadAll)
</script>

<template>
    <Head title="Branch Tagging" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
                    <Users class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Branch Tagging</h1>
                    <p class="text-xs text-gray-400">Assign transactions to specific branches</p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Tagging Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Add Branch Tagging</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User / Trainer</label>
                        <select v-model="selectedUser" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option :value="null">Select user...</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <select v-model="selectedBranch" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option :value="null">Select branch...</option>
                            <option v-for="b in branches" :key="b.branch_code" :value="b.branch_code">{{ b.branch_code }} - {{ b.branch_desc }}</option>
                        </select>
                    </div>
                </div>
                <button @click="saveTagging" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">Save Tagging</button>
            </div>

            <!-- Taggings List -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Current Taggings</h3>
                <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>
                <div v-else-if="!taggings.length" class="text-center py-8 text-gray-400">No taggings found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trainer ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr></thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="t in taggings" :key="t.RecID" class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ t.TrainorID }}</td>
                                <td class="px-4 py-3">{{ t.BranchCode }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ t.CreatedAt }}</td>
                                <td class="px-4 py-3">
                                    <span :class="t.IsDeleted ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2 py-1 text-xs rounded-full font-medium">{{ t.IsDeleted ? 'Inactive' : 'Active' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button v-if="!t.IsDeleted" @click="deleteTagging(t.RecID)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
