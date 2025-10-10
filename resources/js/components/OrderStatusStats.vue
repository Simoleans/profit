<script setup lang="ts">
import { FileText, CheckCircle, XCircle, Clock, Receipt } from 'lucide-vue-next';

interface StatusStat {
    total: number;
    monto: number;
    label: string;
}

interface OrderStats {
    P: StatusStat;
    A: StatusStat;
    R: StatusStat;
    F: StatusStat;
}

interface Props {
    stats: OrderStats;
    month: string;
    loading?: boolean;
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const getStatusIcon = (status: string) => {
    switch(status) {
        case 'P': return Clock;
        case 'A': return CheckCircle;
        case 'R': return XCircle;
        case 'F': return Receipt;
        default: return FileText;
    }
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'P': return {
            bg: 'bg-yellow-50 dark:bg-yellow-950/20',
            border: 'border-yellow-200 dark:border-yellow-800',
            icon: 'text-yellow-600 dark:text-yellow-400',
            text: 'text-yellow-900 dark:text-yellow-100'
        };
        case 'A': return {
            bg: 'bg-green-50 dark:bg-green-950/20',
            border: 'border-green-200 dark:border-green-800',
            icon: 'text-green-600 dark:text-green-400',
            text: 'text-green-900 dark:text-green-100'
        };
        case 'R': return {
            bg: 'bg-red-50 dark:bg-red-950/20',
            border: 'border-red-200 dark:border-red-800',
            icon: 'text-red-600 dark:text-red-400',
            text: 'text-red-900 dark:text-red-100'
        };
        case 'F': return {
            bg: 'bg-blue-50 dark:bg-blue-950/20',
            border: 'border-blue-200 dark:border-blue-800',
            icon: 'text-blue-600 dark:text-blue-400',
            text: 'text-blue-900 dark:text-blue-100'
        };
        default: return {
            bg: 'bg-gray-50 dark:bg-gray-950/20',
            border: 'border-gray-200 dark:border-gray-800',
            icon: 'text-gray-600 dark:text-gray-400',
            text: 'text-gray-900 dark:text-gray-100'
        };
    }
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-800">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 dark:from-blue-950/20 dark:to-indigo-950/20 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-500/10 rounded-lg dark:bg-blue-500/20">
                        <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Pedidos por Estatus
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ month }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-6">
            <div class="space-y-4">
                <div v-for="i in 4" :key="i" class="animate-pulse p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded"></div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-24"></div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-20"></div>
                            </div>
                        </div>
                        <div class="h-8 bg-gray-200 dark:bg-gray-600 rounded w-12"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats List - Vertical/Linear -->
        <div v-else class="p-6">
            <div class="space-y-4">
                <!-- Pendientes -->
                <div :class="['relative overflow-hidden rounded-lg border-2 p-4', getStatusColor('P').bg, getStatusColor('P').border]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="getStatusColor('P').bg">
                                <component :is="getStatusIcon('P')" :class="['w-6 h-6', getStatusColor('P').icon]" />
                            </div>
                            <div>
                                <span :class="['text-sm font-medium', getStatusColor('P').text]">
                                    {{ stats.P.label }}
                                </span>

                            </div>
                        </div>
                        <p :class="['text-3xl font-bold', getStatusColor('P').text]">
                            {{ stats.P.total }}
                        </p>
                    </div>
                </div>

                <!-- Aprobados -->
                <div :class="['relative overflow-hidden rounded-lg border-2 p-4', getStatusColor('A').bg, getStatusColor('A').border]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="getStatusColor('A').bg">
                                <component :is="getStatusIcon('A')" :class="['w-6 h-6', getStatusColor('A').icon]" />
                            </div>
                            <div>
                                <span :class="['text-sm font-medium', getStatusColor('A').text]">
                                    {{ stats.A.label }}
                                </span>

                            </div>
                        </div>
                        <p :class="['text-3xl font-bold', getStatusColor('A').text]">
                            {{ stats.A.total }}
                        </p>
                    </div>
                </div>

                <!-- Rechazados -->
                <div :class="['relative overflow-hidden rounded-lg border-2 p-4', getStatusColor('R').bg, getStatusColor('R').border]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="getStatusColor('R').bg">
                                <component :is="getStatusIcon('R')" :class="['w-6 h-6', getStatusColor('R').icon]" />
                            </div>
                            <div>
                                <span :class="['text-sm font-medium', getStatusColor('R').text]">
                                    {{ stats.R.label }}
                                </span>

                            </div>
                        </div>
                        <p :class="['text-3xl font-bold', getStatusColor('R').text]">
                            {{ stats.R.total }}
                        </p>
                    </div>
                </div>

                <!-- Facturados -->
                <div :class="['relative overflow-hidden rounded-lg border-2 p-4', getStatusColor('F').bg, getStatusColor('F').border]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="getStatusColor('F').bg">
                                <component :is="getStatusIcon('F')" :class="['w-6 h-6', getStatusColor('F').icon]" />
                            </div>
                            <div>
                                <span :class="['text-sm font-medium', getStatusColor('F').text]">
                                    {{ stats.F.label }}
                                </span>

                            </div>
                        </div>
                        <p :class="['text-3xl font-bold', getStatusColor('F').text]">
                            {{ stats.F.total }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

