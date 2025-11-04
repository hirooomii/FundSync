<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import Swal from 'sweetalert2';
import { Search, Sheet, FileText, Link, Landmark, HandCoins, Combine, BanknoteArrowDown } from 'lucide-vue-next';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const company = ref('RC');
const currentDate = ref(new Date());
const calendarEvents = ref({});
const dueDates = ref({ 5: [], 15: [], 30: [] });
const loading = ref(false);
const modalVisible = ref(false);
const modalData = ref({ events: [], title: '' });
const fundingChanges = ref({});

const companyOptions = [
  { value: 'RC', label: 'ROPALI' },
  { value: 'MBC', label: 'MOTORBELLE' },
  { value: 'HMC', label: 'MOTORALI' },
  { value: 'MTB', label: 'MOTOROBEE' }
];

const fetchCalendarEvents = async () => {
  const start = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
  const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
  
  try {
    const response = await axios.post('/payment-schedule-pending/', {
      start: start.toISOString(),
      end: end.toISOString(),
      company: company.value
    });
    
    calendarEvents.value = response.data;
  } catch (error) {
    console.error('Error fetching calendar events:', error);
  }
};

const fetchDueDates = async () => {
  loading.value = true;
  
  try {
    const response = await axios.post('/due-dates', {
      company: company.value
    });
    
    dueDates.value = {
      5: response.data['5'] || [],
      15: response.data['15'] || [],
      30: response.data['30'] || []
    };
  } catch (error) {
    console.error('Error fetching due dates:', error);
  } finally {
    loading.value = false;
  }
};

const formatMoney = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

const calculateTotal = (events) => {
  return events.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);
};

const calculateFundedTotal = (events, funded) => {
  return events
    .filter(e => funded ? e.FundingStatus === 'Funded' : e.FundingStatus === 'No Funds Yet')
    .reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);
};

const calendarInfo = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const daysInMonth = lastDay.getDate();
  const startingDayOfWeek = firstDay.getDay();
  
  return { daysInMonth, startingDayOfWeek, year, month };
});

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const isToday = (day) => {
  const today = new Date();
  const checkDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day);
  return checkDate.toDateString() === today.toDateString();
};

const getDayColor = (day) => {
  const today = new Date();
  const checkDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day);
  const diffTime = checkDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'rgba(255, 180, 180, 0.2)';
  if (diffDays > 0 && diffDays <= 5) return 'rgba(180, 210, 255, 0.2)';
  if (diffDays > 5 && diffDays <= 15) return 'rgba(180, 255, 180, 0.2)';
  if (diffDays > 15 && diffDays <= 30) return 'rgba(255, 255, 180, 0.2)';
  return 'transparent';
};

const getEventsForDate = (day) => {
  const dateStr = `${calendarInfo.value.year}-${String(calendarInfo.value.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  return calendarEvents.value[dateStr] || [];
};

const showEventTable = (events, date) => {
  const companyName = events[0]?.PayingCompany || company.value;
  const formattedDate = new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  modalData.value = {
    events: events.map(e => ({ ...e, originalFunding: e.FundingStatus })),
    title: `${companyName} Transactions on ${formattedDate}`
  };
  fundingChanges.value = {};
  modalVisible.value = true;
};

const showDueModal = (events, dueKey) => {
  const companyName = companyOptions.find(c => c.value === company.value)?.label || company.value;
  
  modalData.value = {
    events: events.map(e => ({ ...e, originalFunding: e.FundingStatus })),
    title: `${companyName} Transactions Due within ${dueKey} Days`
  };
  fundingChanges.value = {};
  modalVisible.value = true;
};


const toggleFunding = (event) => {
  const newStatus = event.FundingStatus === 'Funded' ? 'No Funds Yet' : 'Funded';
  event.FundingStatus = newStatus;
  
  const eventId = event.CheckNumber || event.ID;
  fundingChanges.value[eventId] = {
    id: eventId,
    method: event.PaymentType,
    funding: newStatus
  };
};

const saveFundingChanges = async () => {
  const changesArray = Object.values(fundingChanges.value);
  
  if (changesArray.length === 0) {
    modalVisible.value = false;
    return;
  }

  try {
    const response = await axios.post('/update-funding', {
      funds: changesArray
    });
    
    alert(`Total of ${response.data.data} funding status successfully updated`);
    
    await fetchCalendarEvents();
    await fetchDueDates();
    
    modalVisible.value = false;
  } catch (error) {
    console.error('Error updating funding status:', error);
    alert('Error updating funding status. Please try again.');
  }
};

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const goToToday = () => {
  currentDate.value = new Date();
};

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  });
});

watch(company, () => {
  fetchCalendarEvents();
  fetchDueDates();
});

watch(currentDate, () => {
  fetchCalendarEvents();
});

onMounted(() => {
  fetchCalendarEvents();
  fetchDueDates();
});
</script>


<template>
  <Head title="Payment Schedule" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <HandCoins class="w-6 h-6 text-blue-600" />
            <h2 class="text-2xl font-bold text-gray-800">Payment Schedule</h2>
        </div>
        <span class="text-sm text-gray-500">Monitor your pending and upcoming payments</span>
      </div>
    </template>

     <div class="min-h-screen bg-gray-50 p-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header Card -->
      <div class="bg-white rounded-lg shadow-sm mb-4">
        <div class="flex justify-between items-center w-3/3 gap-3 mb-3 shadow-md p-4 bg-white rounded-lg">
          <div class="flex items-center w-1/3 gap-3 mb-3 shadow-md p-4 bg-white rounded-lg">
              <div class="h-6 w-1.5 bg-blue-600 rounded-full"></div>
              <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">
                  Payment Schedule
              </h3>
          </div>
          <!-- Company Select -->
          <div class="mb-6">
              <label class="block text-xs font-bold text-gray-700 mb-2">Company</label>
              <select
                v-model="company"
                class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent text-xs"
              >
                <option v-for="option in companyOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
        </div>

        <div class="p-6">

          <!-- Due Date Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div
              v-for="days in [5, 15, 30]"
              :key="days"
              class="bg-white rounded-lg shadow p-4"
            >
              <div class="flex justify-between items-center mb-3 text-xs text-gray-600">
                <span>Due within {{ days }} Days</span>
                <div class="flex items-center gap-3">
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Funded
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Not Funded
                  </span>
                </div>
              </div>

              <div v-if="loading" class="text-center py-4">
                <div class="inline-block animate-bounce text-2xl">💰</div>
                <p class="text-sm text-gray-600 mt-2">Loading due dates...</p>
              </div>

              <div v-else>
                <button
                  @click="showDueModal(dueDates[days], days)"
                  :class="{
                    'bg-blue-600': days === 5,
                    'bg-green-600': days === 15,
                    'bg-yellow-600': days === 30
                  }"
                  class="w-full text-white py-2 px-4 rounded-md hover:opacity-90 transition-opacity text-sm font-medium mb-2"
                >
                  {{
                    calculateTotal(dueDates[days]) > 0
                      ? `₱${formatMoney(calculateTotal(dueDates[days]))}`
                      : 'No due for the moment'
                  }}
                </button>

                <div class="flex gap-2">
                  <button class="flex-1 bg-gray-800 text-white py-1 px-2 rounded text-xs flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    ₱{{ formatMoney(calculateFundedTotal(dueDates[days], true)) }}
                  </button>
                  <button class="flex-1 bg-gray-800 text-white py-1 px-2 rounded text-xs flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    ₱{{ formatMoney(calculateFundedTotal(dueDates[days], false)) }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Calendar -->
          <div class="bg-white rounded-lg shadow-sm">
            <div class="bg-red-600 text-white p-3 rounded-t-lg flex items-center justify-between">
              <button
                @click="previousMonth"
                class="px-3 py-1 hover:bg-red-700 rounded transition-colors"
              >
                Prev
              </button>

              <div class="flex items-center gap-4">
                <h3 class="text-lg font-bold">{{ currentMonthYear }}</h3>
                <button
                  @click="goToToday"
                  class="px-3 py-1 bg-red-700 hover:bg-red-800 rounded transition-colors text-sm"
                >
                  Today
                </button>
              </div>

              <button
                @click="nextMonth"
                class="px-3 py-1 hover:bg-red-700 rounded transition-colors"
              >
                Next
              </button>
            </div>

            <div class="p-4">
              <div class="grid grid-cols-7 gap-1">
                <div
                  v-for="day in weekDays"
                  :key="day"
                  class="text-center font-semibold text-gray-700 py-2 text-sm"
                >
                  {{ day }}
                </div>

                <div
                  v-for="i in calendarInfo.startingDayOfWeek"
                  :key="`empty-${i}`"
                  class="aspect-square"
                />

                <div
                  v-for="day in calendarInfo.daysInMonth"
                  :key="day"
                  class="aspect-square border border-gray-200 p-1 relative"
                  :style="{ backgroundColor: getDayColor(day) }"
                >
                  <div
                    :class="[
                      'text-sm',
                      isToday(day) ? 'font-bold text-red-600' : 'text-gray-700'
                    ]"
                  >
                    {{ day }}
                  </div>

                  <button
                    v-if="getEventsForDate(day).length > 0"
                    @click="showEventTable(getEventsForDate(day), `${calendarInfo.year}-${String(calendarInfo.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`)"
                    class="absolute bottom-1 left-1 right-1 bg-blue-600 text-white text-xs py-1 px-1 rounded hover:bg-blue-700 transition-colors"
                  >
                    ₱{{ formatMoney(calculateTotal(getEventsForDate(day))) }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="modalVisible"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
          @click.self="modalVisible = false"
        >
          <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
              <h5 class="text-lg font-semibold bg-gray-500 text-white px-3 py-1 rounded-full">
                {{ modalData.title }}
              </h5>
              <button
                @click="modalVisible = false"
                class="text-gray-500 hover:text-gray-700"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="overflow-auto flex-1 p-4">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">ECPF No</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">DPEAF No</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Paying Company</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Payee</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Purpose</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Payment Method</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Account Number</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Cash Account</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Check Number</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Amount</th>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Funded</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                      v-for="(event, idx) in modalData.events"
                      :key="idx"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-3 py-2">{{ event.EcpfNo || '' }}</td>
                      <td class="px-3 py-2">{{ event.DFEAF || '' }}</td>
                      <td class="px-3 py-2">{{ event.PayingCompany }}</td>
                      <td class="px-3 py-2">{{ event.Payee || '' }}</td>
                      <td class="px-3 py-2">{{ event.Purpose || '' }}</td>
                      <td class="px-3 py-2">{{ event.PaymentMethod || '' }}</td>
                      <td class="px-3 py-2">{{ event.AccountNumber || '' }}</td>
                      <td class="px-3 py-2">{{ event.CashAccount || '' }}</td>
                      <td class="px-3 py-2">{{ event.CheckNumber || '' }}</td>
                      <td class="px-3 py-2">₱{{ event.title || event.amount }}</td>
                      <td class="px-3 py-2 text-center">
                        <button @click="toggleFunding(event)" class="cursor-pointer">
                          <svg
                            v-if="event.FundingStatus === 'Funded'"
                            class="w-5 h-5 text-green-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                          </svg>
                          <svg
                            v-else
                            class="w-5 h-5 text-red-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t">
              <button
                @click="saveFundingChanges"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              >
                Save
              </button>
              <button
                @click="modalVisible = false"
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
  </AuthenticatedLayout>
</template>
<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
