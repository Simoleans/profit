<script setup lang="ts">
import { Wallet, AlertCircle, Clock } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface CuentasXCobrarStats {
    vencidas: number;
    saldo_vencido: number;
    por_vencer: number;
    saldo_por_vencer: number;
    total: number;
    saldo_total: number;
    codigo_vendedor: string;
}

interface Props {
    stats: CuentasXCobrarStats;
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

const redirectCuentasPorCobrar = () => {
    router.visit('/clients?tab=balance');
};
</script>

<template>
    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-gray-100 dark:from-slate-950/20 dark:to-gray-950/20 dark:border-sidebar-border max-w-sm w-full cursor-pointer" @click="redirectCuentasPorCobrar">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-600/5"></div>
        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-500/3 rounded-full -translate-y-10 translate-x-10"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 bg-orange-500/3 rounded-full translate-y-8 -translate-x-8"></div>

        <!-- Contenido -->
        <div v-if="loading" class="relative p-4 space-y-3">
            <div class="animate-pulse space-y-3">
                <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-32"></div>
                <div class="h-8 bg-gray-200 dark:bg-gray-600 rounded w-20"></div>
                <div class="space-y-2">
                    <div class="h-12 bg-gray-200 dark:bg-gray-600 rounded"></div>
                    <div class="h-12 bg-gray-200 dark:bg-gray-600 rounded"></div>
                </div>
            </div>
        </div>

        <div v-else class="relative p-4 space-y-3">
            <!-- Header -->
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-amber-500/8 rounded-lg dark:bg-amber-500/15 shrink-0">
                    <Wallet class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                        Cuentas por Cobrar
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                        Facturas pendientes
                    </p>
                </div>
            </div>

            <!-- Estadística principal - Total -->
            <div class="text-center py-2 bg-blue-50/50 dark:bg-blue-950/10 rounded-lg border border-blue-200/30 dark:border-blue-800/30">
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 leading-none">
                    {{ formatCurrency(stats.saldo_total) }}
                </p>
                <!-- <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                    Total: {{ stats.total }} facturas

                </p> -->
            </div>

            <!-- Métricas secundarias -->
            <div class="space-y-2">
                <!-- Facturas por vencer -->
                <div class="flex items-center justify-between p-2 bg-amber-50/50 dark:bg-amber-950/10 rounded-lg border border-amber-200/30 dark:border-amber-800/30">
                    <div class="flex items-center gap-2">
                        <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Por vencer</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">
                                {{ stats.por_vencer }} facturas
                            </p>
                        </div>
                    </div>
                    <span class="text-lg font-bold text-amber-900 dark:text-amber-100">
                        {{ formatCurrency(stats.saldo_por_vencer) }}
                    </span>
                </div>

                <!-- Facturas vencidas -->
                <div class="flex items-center justify-between p-2 bg-red-50/50 dark:bg-red-950/10 rounded-lg border border-red-200/30 dark:border-red-800/30">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 text-red-600 dark:text-red-400" />
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Vencidas</p>
                            <p class="text-xs text-red-700 dark:text-red-300 font-medium">
                                {{ stats.vencidas }} facturas
                            </p>
                        </div>
                    </div>
                    <span class="text-lg font-bold text-red-900 dark:text-red-100">
                        {{ formatCurrency(stats.saldo_vencido) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

