<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import Swal from 'sweetalert2';
import { Search, Sheet, FileText, Link, Landmark, HandCoins, Combine, TableCellsMerge  } from 'lucide-vue-next';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
</script>

<template>
  <Head title="Reconciliation" />
  
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <TableCellsMerge  class="w-6 h-6 text-blue-600" />
            <h2 class="text-2xl font-bold text-gray-800">Reconciliation</h2>
        </div>
        <span class="text-sm text-gray-500">Manage and track you company expenses</span>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center w-1/3 gap-3 mb-3 shadow-md p-4 bg-white rounded-lg">
            <div class="h-6 w-1.5 bg-blue-600 rounded-full"></div>
            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">
                Reconciliation
            </h3>
        </div>
      </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
