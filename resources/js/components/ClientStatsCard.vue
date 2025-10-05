<script setup lang="ts">
import { Users, TrendingUp, UserPlus } from 'lucide-vue-next';

interface ClientStats {
    total: number;
    new_this_month: number;
    is_admin: boolean;
}

interface Props {
    stats: ClientStats;
}

defineProps<Props>();
</script>

<template>
    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-gray-100 dark:from-slate-950/20 dark:to-gray-950/20 dark:border-sidebar-border max-w-sm w-full">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5"></div>
        <div class="absolute top-0 right-0 w-16 h-16 bg-blue-500/3 rounded-full -translate-y-8 translate-x-8"></div>
        <div class="absolute bottom-0 left-0 w-12 h-12 bg-indigo-500/3 rounded-full translate-y-6 -translate-x-6"></div>

        <!-- Contenido -->
        <div class="relative p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-500/8 rounded-lg dark:bg-blue-500/15 shrink-0">
                    <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                        Clientes
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                        {{ stats.is_admin ? 'Total del sistema' : 'Mis clientes' }}
                    </p>
                </div>
            </div>

            <!-- Estadística principal -->
            <div class="text-center py-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                    {{ stats.total.toLocaleString() }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Total {{ stats.is_admin ? 'en el sistema' : 'asignados' }}
                </p>
            </div>

            <!-- Métricas secundarias -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200/50 dark:border-gray-700/30">
                <!-- Estado activo -->
                <div class="text-center">
                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 mb-1">
                        <TrendingUp class="w-3 h-3 mr-1" />
                        Activos
                    </div>
                </div>

                <!-- Clientes nuevos -->
                <div class="text-center">
                    <div class="flex items-center justify-center mb-1">
                        <UserPlus class="w-3 h-3 text-blue-500 mr-1" />
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            +{{ stats.new_this_month }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Recientes
                    </p>
                </div>
            </div>

            <!-- Indicador de rol -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-200/40 dark:border-gray-700/30">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Vista {{ stats.is_admin ? 'Administrador' : 'Vendedor' }}
                </span>
                <div class="w-2 h-2 rounded-full" :class="stats.is_admin ? 'bg-purple-400' : 'bg-blue-400'"></div>
            </div>
        </div>
    </div>
</template>
