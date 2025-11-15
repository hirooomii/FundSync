<template>
  <Head title="Dashboard" />
  
  <AuthenticatedLayout>
    <div class="p-6">
      <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Company Overall Balance Chart -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <h6 class="text-center text-xs font-extrabold mb-3 text-gray-700">
                COMPANY OVERALL BALANCE
              </h6>
              <canvas ref="overallbalance"></canvas>
            </div>
          </div>

          <!-- Account Total Withdrawal/Deposit Table -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <h6 class="text-center text-xs font-extrabold mb-3 text-gray-700">
                ACCOUNT TOTAL WITHDRAWAL/DEPOSIT
              </h6>
              <div class="overflow-x-auto">
                <EasyDataTable
                  :headers="accountHeaders"
                  :items="accountItems"
                  :rows-per-page="2"
                  :loading="accountLoading"
                  buttons-pagination
                  class="customize-table"
                >
                  <template #item-account_number="{ account_number }">
                    <button
                      @click="openTransactionHistory(account_number)"
                      class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded transition-colors"
                    >
                      {{ formatAccountNumber(account_number) }}
                    </button>
                  </template>
                  
                  <template #item-available_balance="{ available_balance }">
                    <span class="text-xs">₱{{ Number(available_balance).toLocaleString() }}</span>
                  </template>
                  
                  <template #item-total_withdrawal="{ total_withdrawal }">
                    <span class="text-xs">₱{{ Number(total_withdrawal).toLocaleString() }}</span>
                  </template>
                  
                  <template #item-total_deposit="{ total_deposit }">
                    <span class="text-xs">₱{{ Number(total_deposit).toLocaleString() }}</span>
                  </template>
                </EasyDataTable>
              </div>
            </div>
          </div>
        </div>
            <div class="col-span-12">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <div class="p-2">
                <div class="grid grid-cols-3 gap-4">

                  <!-- Pending Check Payment -->
                  <div class="flex justify-center items-center h-65">
                    <canvas ref="checkpending"></canvas>
                  </div>

                  <!-- Pending Online Payment -->
                  <div class="flex justify-center items-center h-65">
                    <canvas ref="onlinepending"></canvas>
                  </div>

                  <!-- Pending Cash Payment -->
                  <div class="flex justify-center items-center h-65">
                    <canvas ref="cashpending"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <div class="p-2">
                <div class="grid grid-cols-2 gap-4">
                  <!-- SOA Pending -->
                  <div class="flex justify-center items-center h-65">
                    <canvas ref="soapending"></canvas>
                  </div>

                  <!-- Multiple Pending -->
                  <div class="flex justify-center items-center h-65">
                    <canvas ref="multiplepending"></canvas>
                  </div>

                </div>
              </div>
            </div>
          </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <!-- Reconciliation Status Chart -->
          <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="reconStatus"></canvas>
            </div>
          </div>

          <!-- Overall Account Balance Chart -->
          <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="overallaccountbal"></canvas>
            </div>
          </div>

          <!-- RM Cash Account Chart -->
          <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="rmcashacc"></canvas>
            </div>
          </div>

          <!-- MBC Cash Account Chart -->
          <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="mbccashacc"></canvas>
            </div>
          </div>

          <!-- HMC Cash Account Chart -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="hmccashacc"></canvas>
            </div>
          </div>

          <!-- MTB Cash Account Chart -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <canvas ref="mtbcashacc"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <pending-checks-modal ref="pendingChecksModal" />
    <pending-online-modal ref="pendingOnlineModal" />
    <pending-cash-modal ref="pendingCashModal" />
    <transaction-history-modal ref="transactionHistoryModal" />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import 'chartjs-adapter-date-fns';
import axios from 'axios';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

import PendingChecksModal from '@/Pages/Dashboard/PendingChecks.vue';
import PendingOnlineModal from '@/Pages/Dashboard/PendingOnline.vue';
import PendingCashModal from '@/Pages/Dashboard/PendingCash.vue';
import TransactionHistoryModal from '@/Pages/Dashboard/TransactionHistory.vue';

Chart.register(...registerables, ChartDataLabels);

// Refs for charts
const overallbalance = ref(null);
const soapending = ref(null);
const multiplepending = ref(null);
const reconStatus = ref(null);
const checkpending = ref(null);
const onlinepending = ref(null);
const cashpending = ref(null);
const overallaccountbal = ref(null);
const rmcashacc = ref(null);
const mbccashacc = ref(null);
const hmccashacc = ref(null);
const mtbcashacc = ref(null);

// Refs for modals
const pendingChecksModal = ref(null);
const pendingOnlineModal = ref(null);
const pendingCashModal = ref(null);
const transactionHistoryModal = ref(null);

// Chart instances storage
const charts = {};

// Table data
const accountHeaders = [
  { text: 'ACCOUNT', value: 'account_number', sortable: true },
  { text: 'BALANCE', value: 'available_balance', sortable: true },
  { text: 'WITHDRAWAL', value: 'total_withdrawal', sortable: true },
  { text: 'DEPOSIT', value: 'total_deposit', sortable: true }
];

const accountItems = ref([]);
const accountLoading = ref(false);

// Lifecycle hooks
onMounted(async () => {
  await initializeCharts();
});

onBeforeUnmount(() => {
  Object.values(charts).forEach(chart => {
    if (chart) chart.destroy();
  });
});

// Methods
const initializeCharts = async () => {
  await fetchAccountSummary();
  await overallBalanceChart();
  await drawSOADonutChart();
  await drawReconDonutChart();
  await reconciliationStatus();
  await pendingCheckPayment();
  await pendingOnlinePayment();
  await pendingCashPayment();
  await bankAccountProgressive();
  await rmAcumaticaBook();
  await mbcAcumaticaBook();
  await hmcAcumaticaBook();
  await mtbAcumaticaBook();
};

const generateRandomColor = () => {
  const hue = Math.floor(Math.random() * 360);
  const saturation = 50 + Math.floor(Math.random() * 30);
  const lightness = 50 + Math.floor(Math.random() * 20);
  return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
};

const formatAccountNumber = (accountNumber) => {
  return accountNumber.replace(/(.{4})(?=.)/g, '$1-');
};

const openTransactionHistory = (accountNumber) => {
  transactionHistoryModal.value.show(accountNumber);
};

const fetchAccountSummary = async () => {
  try {
    accountLoading.value = true;
    const response = await axios.get('/account-summaries');
    accountItems.value = response.data.data;
  } catch (error) {
    console.error('Error fetching account summary:', error);
  } finally {
    accountLoading.value = false;
  }
};

const overallBalanceChart = async () => {
  try {
    const response = await axios.get('/company-balances');
    const data = response.data.data;

    const labels = data.map(item => item.depository_company.replace('CORPORATION', '').trim());
    const balances = data.map(item => item.totalBalance);
    const colors = data.map((_, i) => {
      const hue = (i * 60) % 360;
      return `hsl(${hue}, 40%, 55%)`;
    });

    const ctx = overallbalance.value.getContext('2d');
    charts.overallBalance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total Balance',
          data: balances,
          backgroundColor: colors,
          borderRadius: 6,
          barThickness: 30
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
          datalabels: {
            align: 'end',
            anchor: 'end',
            color: '#000',
            font: { weight: 'bold' },
            formatter: (value) => value == null || isNaN(value) ? '' : Number(value).toLocaleString()
          },
          legend: {
            display: true,
            labels: {
              generateLabels: (chart) => {
                return labels.map((label, i) => ({
                  text: label,
                  fillStyle: colors[i],
                  strokeStyle: colors[i],
                  lineWidth: 1,
                  hidden: false,
                  index: i
                }));
              }
            }
          },
          tooltip: {
            callbacks: {
              label: (context) => ` ₱${context.raw.toLocaleString()}`
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            max: 100000000,
            ticks: {
              stepSize: 5000000,
              callback: (value) => '₱' + (value / 1000000) + 'M'
            }
          }
        }
      },
      plugins: [ChartDataLabels]
    });
  } catch (error) {
    console.error('Error creating overall balance chart:', error);
  }
};

const reconciliationStatus = async () => {
  try {
    const response = await axios.get('/recon-status');
    const data = response.data.data;

    const banks = data.map(d => d.BankName);
    const totalPassbookBal = data.map(d => +d.TotalPassbookBal || 0);
    const reconciliation = data.map(d => +d.Reconciliation || 0);
    const percentages = data.map(d => +d.Percentage || 0);

    const totalRecon = reconciliation.reduce((a, b) => a + b, 0);
    const totalTrans = data.map(d => +d.TotalTransactionAmount || 0).reduce((a, b) => a + b, 0);
    const overallPercentage = totalTrans === 0 ? 0 : ((totalRecon / totalTrans) * 100).toFixed(2);

    const ctx = reconStatus.value.getContext('2d');

    charts.reconStatus = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: banks,
        datasets: [
          {
            label: 'Unreconciled',
            data: totalPassbookBal,
            backgroundColor: generateRandomColor(),
            borderColor: 'rgba(0, 0, 0, 0.05)',
            borderWidth: 1
          },
          {
            label: 'Reconciled',
            data: reconciliation,
            backgroundColor: generateRandomColor(),
            borderColor: 'rgba(0, 0, 0, 0.05)',
            borderWidth: 1
          },
          {
            label: 'Percentage (%)',
            data: percentages,
            backgroundColor: generateRandomColor(),
            borderColor: 'rgba(0, 0, 0, 0.05)',
            borderWidth: 1,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false
        },
        scales: {
          y: {
            type: 'linear',
            position: 'left',
            beginAtZero: true,
            title: {
              display: true,
              text: 'Amount'
            }
          },
          y1: {
            type: 'linear',
            position: 'right',
            beginAtZero: true,
            max: 100,
            title: {
              display: true,
              text: 'Percentage (%)'
            },
            grid: {
              drawOnChartArea: false
            }
          }
        },
        plugins: {
          title: {
            display: true,
            text: `Overall Reconciliation Progress: ${overallPercentage}%`
          },
          tooltip: {
            enabled: true,
            mode: 'index',
            intersect: false,
            callbacks: {
              label: (context) => {
                const label = context.dataset.label || '';
                const value = context.parsed.y;
                if (label.includes('Percentage')) {
                  return `${label}: ${value}%`;
                }
                return `${label}: ${Number(value).toLocaleString()}`;
              }
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error creating reconciliation status chart:', error);
  }
};

const pendingCheckPayment = async () => {
  const today = new Date();
  const futureDate = new Date();
  futureDate.setDate(today.getDate() + 15);

  const start = today.toISOString().split('T')[0];
  const end = futureDate.toISOString().split('T')[0];

  try {
    const response = await axios.post('/pending-checks', { start, end });
    const noFundsData = response.data.data.filter(item => item.FundingStatus === 'No Funds Yet');

    let useDummyData = !noFundsData || noFundsData.length === 0;
    const grouped = {};

    if (useDummyData) {
      ['RC', 'HMC', 'MBC', 'MTB'].forEach(company => {
        grouped[company] = { count: 0, tooltipData: [], checks: [] };
      });
    } else {
      noFundsData.forEach(item => {
        const company = item.PayingCompany || 'Unknown';
        if (!grouped[company]) grouped[company] = { count: 0, tooltipData: [], checks: [] };
        grouped[company].count++;
        grouped[company].tooltipData.push(`${item.checkId} - ${item.EcpfNo} - ₱${parseFloat(item.Amount).toLocaleString()}`);
        grouped[company].checks.push(item);
      });
    }

    const labels = Object.keys(grouped).map(company => `${company} (${grouped[company].count} Checks)`);
    const dataCounts = Object.values(grouped).map(group => group.count === 0 ? 1 : group.count);
    const colors = Array.from({ length: labels.length }, () => generateRandomColor());

    const ctx = checkpending.value.getContext('2d');
    charts.checkPending = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          label: 'No Funds Yet Checks',
          data: dataCounts,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        onClick: (e) => {
          const activePoints = charts.checkPending.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
          if (activePoints.length > 0) {
            const index = activePoints[0].index;
            const label = charts.checkPending.data.labels[index];
            const companyName = label.split(' (')[0];
            const checksData = grouped[companyName].checks;
            pendingChecksModal.value.show(checksData);
          }
        },
        plugins: {
          title: {
            display: true,
            text: `PENDING CHECK PAYMENT FROM ${start.toUpperCase()} TO ${end.toUpperCase()}`,
            font: { size: 12 },
            padding: { top: 20, bottom: 20 }
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.raw} Checks`
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error creating pending check payment chart:', error);
  }
};

const pendingOnlinePayment = async () => {
  const today = new Date();
  const futureDate = new Date();
  futureDate.setDate(today.getDate() + 15);

  const start = today.toISOString().split('T')[0];
  const end = futureDate.toISOString().split('T')[0];

  try {
    const response = await axios.post('/pending-online-payment', { start, end });
    const noFundsData = response.data.data.filter(item => item.FundingStatus === 'No Funds Yet');

    let useDummyData = !noFundsData || noFundsData.length === 0;
    const grouped = {};

    if (useDummyData) {
      ['RC', 'HMC', 'MBC', 'MTB'].forEach(company => {
        grouped[company] = { count: 0, tooltipData: [], checks: [] };
      });
    } else {
      noFundsData.forEach(item => {
        const company = item.PayingCompany || 'Unknown';
        if (!grouped[company]) grouped[company] = { count: 0, tooltipData: [], checks: [] };
        grouped[company].count++;
        grouped[company].checks.push(item);
      });
    }

    const labels = Object.keys(grouped).map(company => `${company} (${grouped[company].count} Online)`);
    const dataCounts = Object.values(grouped).map(group => group.count === 0 ? 1 : group.count);
    const colors = Array.from({ length: labels.length }, () => generateRandomColor());

    const ctx = onlinepending.value.getContext('2d');
    charts.onlinePending = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          label: 'No Funds Yet Online',
          data: dataCounts,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        onClick: (e) => {
          const activePoints = charts.onlinePending.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
          if (activePoints.length > 0) {
            const index = activePoints[0].index;
            const label = charts.onlinePending.data.labels[index];
            const companyName = label.split(' (')[0];
            const checksData = grouped[companyName].checks;
            pendingOnlineModal.value.show(checksData);
          }
        },
        plugins: {
          title: {
            display: true,
            text: `PENDING ONLINE PAYMENT FROM ${start.toUpperCase()} TO ${end.toUpperCase()}`,
            font: { size: 12 },
            padding: { top: 20, bottom: 20 }
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.raw} Online Payment`
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error creating pending online payment chart:', error);
  }
};

const pendingCashPayment = async () => {
  const today = new Date();
  const futureDate = new Date();
  futureDate.setDate(today.getDate() + 15);

  const start = today.toISOString().split('T')[0];
  const end = futureDate.toISOString().split('T')[0];

  try {
    const response = await axios.post('/pending-cash-payment', { start, end });
    const noFundsData = response.data.data.filter(item => item.FundingStatus === 'No Funds Yet');

    let useDummyData = !noFundsData || noFundsData.length === 0;
    const grouped = {};

    if (useDummyData) {
      ['RC', 'HMC', 'MBC', 'MTB'].forEach(company => {
        grouped[company] = { count: 0, tooltipData: [], checks: [] };
      });
    } else {
      noFundsData.forEach(item => {
        const company = item.PayingCompany || 'Unknown';
        if (!grouped[company]) grouped[company] = { count: 0, tooltipData: [], checks: [] };
        grouped[company].count++;
        grouped[company].checks.push(item);
      });
    }

    const labels = Object.keys(grouped).map(company => `${company} (${grouped[company].count} Cash)`);
    const dataCounts = Object.values(grouped).map(group => group.count === 0 ? 1 : group.count);
    const colors = Array.from({ length: labels.length }, () => generateRandomColor());

    const ctx = cashpending.value.getContext('2d');
    charts.cashPending = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          label: 'No Funds Yet Cash',
          data: dataCounts,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        onClick: (e) => {
          const activePoints = charts.cashPending.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
          if (activePoints.length > 0) {
            const index = activePoints[0].index;
            const label = charts.cashPending.data.labels[index];
            const companyName = label.split(' (')[0];
            const checksData = grouped[companyName].checks;
            pendingCashModal.value.show(checksData);
          }
        },
        plugins: {
          title: {
            display: true,
            text: `PENDING CASH PAYMENT FROM ${start.toUpperCase()} TO ${end.toUpperCase()}`,
            font: { size: 12 },
            padding: { top: 20, bottom: 20 }
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.raw} Cash Payment`
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error creating pending cash payment chart:', error);
  }
};

const bankAccountProgressive = async () => {
  try {
    const response = await axios.get('/bank-balances');
    const rawData = response.data.data;

    const grouped = {};
    rawData.forEach(entry => {
      const company = entry.Company;
      const transactionDate = new Date(entry.transaction_date);

      if (!grouped[company]) grouped[company] = { data: [] };
      grouped[company].data.push({
        x: transactionDate,
        y: parseFloat(entry.total_balance)
      });
    });

    const datasets = Object.keys(grouped).map((company, index) => ({
      label: company,
      data: grouped[company].data,
      borderColor: getColor(index),
      backgroundColor: getColor(index, 0.2),
      fill: false,
      tension: 0,
      borderWidth: 2
    }));

    const ctx = overallaccountbal.value.getContext('2d');
    charts.accountBalance = new Chart(ctx, {
      type: 'line',
      data: { datasets },
      options: {
        responsive: true,
        scales: {
          x: {
            type: 'time',
            time: { unit: 'day' },
            title: { display: true, text: 'Date' }
          },
          y: {
            title: { display: true, text: 'Balance' },
            ticks: {
              callback: (value) => {
                if (value >= 1_000_000) return (value / 1_000_000).toFixed(1) + 'M';
                if (value >= 1_000) return (value / 1_000).toFixed(1) + 'K';
                return value;
              }
            }
          }
        },
        plugins: {
          title: {
            display: true,
            text: 'CORPORATION ACCOUNT BALANCES'
          }
        }
      }
    });
  } catch (error) {
    console.error('Error creating bank account progressive chart:', error);
  }
};

const getColor = (index, alpha = 1) => {
  const colors = [
    'rgba(255, 99, 132, ALPHA)',
    'rgba(54, 162, 235, ALPHA)',
    'rgba(255, 206, 86, ALPHA)',
    'rgba(75, 192, 192, ALPHA)',
    'rgba(153, 102, 255, ALPHA)',
    'rgba(255, 159, 64, ALPHA)'
  ];
  return colors[index % colors.length].replace('ALPHA', alpha);
};

const rmAcumaticaBook = async () => {
  await createAcumaticaChart('/cm1-acumatica-summary', rmcashacc, 'ROPALI', '#3B82F6', '#6B7280');
};

const mbcAcumaticaBook = async () => {
  await createAcumaticaChart('/cm2-acumatica-summary', mbccashacc, 'MOTORBELLE', '#D4AF37', '#4B5563');
};

const hmcAcumaticaBook = async () => {
  await createAcumaticaChart('/cm3-acumatica-summary', hmccashacc, 'MOTORALI', '#B91C1C', '#4B5563');
};

const mtbAcumaticaBook = async () => {
  await createAcumaticaChart('/cm4-acumatica-summary', mtbcashacc, 'MOTOROBEE', '#15803D', '#4B5563');
};

const createAcumaticaChart = async (endpoint, canvasRef, companyName, depositColor, withdrawalColor) => {
  try {
    const response = await axios.get(endpoint);
    const cashAccounts = response.data.data;

    const today = new Date();
    const threeDaysAgo = new Date();
    threeDaysAgo.setDate(today.getDate() - 3);

    const formatDate = (date) =>
      date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).toUpperCase();

    const dateRange = `${formatDate(threeDaysAgo)} - ${formatDate(today)}`;

    const labels = cashAccounts.map(acc => acc.CashAccount);
    const deposits = cashAccounts.map(acc => acc.total_deposit || 0);
    const withdrawals = cashAccounts.map(acc => acc.total_withdrawal || 0);

    const formatMoney = (amount) => {
      if (amount >= 1000000) return (amount / 1000000).toFixed(1) + 'M';
      if (amount >= 1000) return (amount / 1000).toFixed(1) + 'K';
      return amount.toString();
    };

    const ctx = canvasRef.value.getContext('2d');
    charts[canvasRef] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Deposits',
            data: deposits,
            backgroundColor: depositColor,
            datalabels: {
              align: 'end',
              formatter: (value) => formatMoney(value)
            }
          },
          {
            label: 'Withdrawals',
            data: withdrawals,
            backgroundColor: withdrawalColor,
            datalabels: {
              align: 'end',
              formatter: (value) => formatMoney(value)
            }
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: `${companyName} CASH ACCOUNT BOOKING AS OF ${dateRange}`
          },
          tooltip: {
            mode: 'index',
            intersect: false
          },
          datalabels: {
            color: 'black',
            font: { weight: 'bold' }
          }
        },
        scales: {
          x: {
            type: 'category',
            stacked: true,
            ticks: {
              callback: (value, index) => labels[index]
            }
          },
          y: {
            type: 'logarithmic',
            stacked: true,
            ticks: {
              callback: (value) => formatMoney(value),
              min: 1000
            }
          }
        }
      }
    });
  } catch (error) {
    console.error(`Error creating ${companyName} acumatica chart:`, error);
  }
};

const drawSOADonutChart = async () => {
  try {
    const response = await axios.get('/pending-soa');
    const data = response.data;

    const labels = data.map(item => `${item.StatusCategory} SOA (${item.Count})`);
    const counts = data.map(item => item.Count);
    const colors = labels.map(() => generateRandomColor());

    const ctx = soapending.value.getContext('2d');

    if (charts.soaDonut) {
      charts.soaDonut.destroy();
    }

    charts.soaDonut = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: counts,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'STATEMENT OF ACCOUNT STATUS',
            font: { size: 12, weight: 'bold' }
          },
          tooltip: { enabled: true }
        }
      }
    });
  } catch (error) {
    console.error('Error creating SOA donut chart:', error);
  }
};

const drawReconDonutChart = async () => {
  try {
    const response = await axios.get('/recon-status-count');
    const data = response.data;

    const labels = data.map(item => `${item.StatusCategory} MRA (${item.Count})`);
    const counts = data.map(item => item.Count);
    const colors = labels.map(() => generateRandomColor());

    const ctx = multiplepending.value.getContext('2d');

    if (charts.reconDonut) {
      charts.reconDonut.destroy();
    }

    charts.reconDonut = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: counts,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'RECONCILE MULTIPLE APPROVAL STATUS',
            font: { size: 12, weight: 'bold' }
          },
          tooltip: { enabled: true }
        }
      }
    });
  } catch (error) {
    console.error('Error creating recon donut chart:', error);
  }
};
</script>

<style>
/* Custom styling for EasyDataTable */
.customize-table {
  --easy-table-header-font-size: 12px;
  --easy-table-header-height: 40px;
  --easy-table-header-background-color: #f3f4f6;
  --easy-table-body-row-font-size: 12px;
  --easy-table-body-row-height: 40px;
  --easy-table-body-row-hover-background-color: #f9fafb;
  --easy-table-border: 1px solid #e5e7eb;
}

.customize-table .vue3-easy-data-table__header th {
  text-align: center;
  font-weight: 600;
  color: #374151;
}

.customize-table .vue3-easy-data-table__body td {
  text-align: center;
  color: #6b7280;
}
</style>