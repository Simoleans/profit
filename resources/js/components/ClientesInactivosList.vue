<script setup lang="ts">
import { Users, Calendar, TrendingDown, DollarSign } from 'lucide-vue-next';

interface Cliente {
    co_cli: string;
    cli_des: string;
    ult_fec_fac: string;
    meses_ult_fac: number;
    prom_vta_mens: number;
}

interface Props {
    clientes: Cliente[];
    loading?: boolean;
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
    if (!amount || amount === 0) return 'N/A';
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getMesesColor = (meses: number) => {
    if (meses >= 6) return 'text-red-600 dark:text-red-400';
    if (meses >= 4) return 'text-orange-600 dark:text-orange-400';
    return 'text-yellow-600 dark:text-yellow-400';
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-800">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-red-50 to-orange-50 px-6 py-4 dark:from-red-950/20 dark:to-orange-950/20 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-red-500/10 rounded-lg dark:bg-red-500/20">
                    <TrendingDown class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Clientes Inactivos
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Sin compras hace más de 3 meses
                    </p>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-6">
            <div class="space-y-3">
                <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes List -->
        <div v-else-if="clientes.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="cliente in clientes" :key="cliente.co_cli"
                 class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <!-- Left: Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900 dark:text-red-300">
                                INACTIVO
                            </span>
                            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                {{ cliente.co_cli }}
                            </span>
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ cliente.cli_des }}
                        </h4>
                        <div class="flex items-center gap-3 mt-2 text-xs">
                            <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                <Calendar class="w-3 h-3" />
                                Última compra: {{ formatDate(cliente.ult_fec_fac) }}
                            </span>
                            <span class="inline-flex items-center gap-1" :class="getMesesColor(cliente.meses_ult_fac)">
                                <Users class="w-3 h-3" />
                                {{ cliente.meses_ult_fac }} meses
                            </span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Prom. Mensual
                        </p>
                        <div class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-bold text-blue-800 shadow-sm dark:bg-blue-900 dark:text-blue-300">
                            <DollarSign class="w-4 h-4" />
                            {{ formatCurrency(cliente.prom_vta_mens) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="p-12 text-center">
            <Users class="w-12 h-12 mx-auto text-gray-400 mb-3" />
            <p class="text-gray-500 dark:text-gray-400">
                No hay clientes inactivos
            </p>
        </div>
    </div>
</template>

