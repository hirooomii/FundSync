<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bell } from 'lucide-vue-next';

const activeTab = ref('all');
const notifications = ref([]);
const loading = ref(false);
const markingId = ref(null);

const fetchNotifications = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/get-notifications');
    notifications.value = res.data.data ?? res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const displayedNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notifications.value.filter((n) => !n.read_at && !n.is_read);
  }
  return notifications.value;
});

const markAsRead = async (notification) => {
  markingId.value = notification.id;
  try {
    await axios.post('/mark-notification-read', { notification_id: notification.id });
    notification.read_at = new Date().toISOString();
    notification.is_read = true;
  } catch (err) {
    console.error(err);
  } finally {
    markingId.value = null;
  }
};

const isUnread = (n) => !n.read_at && !n.is_read;

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
};

onMounted(fetchNotifications);
</script>

<template>
  <Head title="Notifications" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-200">
          <Bell class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-[15px] font-bold text-gray-800 leading-tight">Notifications</h1>
          <p class="text-xs text-gray-400">Stay updated with system alerts</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-md p-6">

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
          <button
            @click="activeTab = 'all'"
            :class="[
              'px-5 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'all'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            All
            <span class="ml-1.5 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">{{ notifications.length }}</span>
          </button>
          <button
            @click="activeTab = 'unread'"
            :class="[
              'px-5 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'unread'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            Unread
            <span class="ml-1.5 bg-blue-100 text-blue-600 text-xs px-1.5 py-0.5 rounded-full">
              {{ notifications.filter(n => isUnread(n)).length }}
            </span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <!-- Notification List -->
        <div v-else>
          <div v-if="displayedNotifications.length === 0" class="text-center py-12 text-gray-400">
            <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="text-sm">No notifications</p>
          </div>
          <ul v-else class="space-y-3">
            <li
              v-for="notification in displayedNotifications"
              :key="notification.id"
              :class="[
                'flex items-start gap-4 p-4 rounded-xl border transition-colors',
                isUnread(notification)
                  ? 'bg-blue-50 border-blue-100'
                  : 'bg-white border-gray-100'
              ]"
            >
              <!-- Unread dot -->
              <div class="mt-1 flex-shrink-0">
                <span
                  v-if="isUnread(notification)"
                  class="inline-block w-2.5 h-2.5 bg-blue-500 rounded-full"
                ></span>
                <span v-else class="inline-block w-2.5 h-2.5 bg-gray-200 rounded-full"></span>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-700">
                    Multiple ID: {{ notification.multiple_id ?? notification.data?.multiple_id ?? '—' }}
                  </span>
                </div>
                <p class="text-sm text-gray-800 font-medium">Reconciliation requires approval</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
              </div>

              <!-- Mark as Read button -->
              <div class="flex-shrink-0">
                <button
                  v-if="isUnread(notification)"
                  @click="markAsRead(notification)"
                  :disabled="markingId === notification.id"
                  class="text-xs text-blue-600 hover:text-blue-800 font-medium border border-blue-200 px-3 py-1 rounded-lg hover:bg-blue-50 disabled:opacity-50 transition-colors"
                >
                  {{ markingId === notification.id ? 'Marking...' : 'Mark as Read' }}
                </button>
                <span v-else class="text-xs text-gray-400">Read</span>
              </div>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
