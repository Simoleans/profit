<script setup lang="ts">
import { Wallet, AlertCircle, Clock } from 'lucide-vue-next';

interface CuentasXCobrarStats {
    vencidas: number;
    por_vencer: number;
    total: number;
    codigo_vendedor: string;
}

interface Props {
    stats: CuentasXCobrarStats;
}

defineProps<Props>();
</script>

<template>
    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-gray-100 dark:from-slate-950/20 dark:to-gray-950/20 dark:border-sidebar-border max-w-sm w-full">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-600/5"></div>
        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-500/3 rounded-full -translate-y-10 translate-x-10"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 bg-orange-500/3 rounded-full translate-y-8 -translate-x-8"></div>

        <!-- Contenido -->
        <div class="relative p-4 space-y-4">
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

            <!-- Estadística principal -->
            <div class="text-center py-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                    {{ stats.total.toLocaleString() }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Total de facturas
                </p>
            </div>

            <!-- Métricas secundarias -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200/50 dark:border-gray-700/30">
                <!-- Facturas vencidas -->
                <div class="text-center">
                    <div class="flex items-center justify-center mb-1">
                        <AlertCircle class="w-3 h-3 text-red-500 mr-1" />
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ stats.vencidas }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Vencidas
                    </p>
                </div>

                <!-- Facturas por vencer -->
                <div class="text-center">
                    <div class="flex items-center justify-center mb-1">
                        <Clock class="w-3 h-3 text-amber-500 mr-1" />
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                            {{ stats.por_vencer }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Por vencer
                    </p>
                </div>
            </div>

            <!-- Indicador de vendedor -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-200/40 dark:border-gray-700/30">
                <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    Vendedor: {{ stats.codigo_vendedor }}
                </span>
                <div class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></div>
            </div>
        </div>
    </div>
</template>

