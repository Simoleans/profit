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
    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-gray-100 dark:from-slate-950/20 dark:to-gray-950/20 dark:border-sidebar-border">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5"></div>
        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-500/3 rounded-full -translate-y-10 translate-x-10"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 bg-indigo-500/3 rounded-full translate-y-8 -translate-x-8"></div>

        <!-- Contenido -->
        <div class="relative p-4 h-full flex flex-col justify-between">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <div class="p-1.5 bg-blue-500/8 rounded-lg dark:bg-blue-500/15">
                        <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Clientes
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ stats.is_admin ? 'Total del sistema' : 'Mis clientes' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas principales -->
            <div class="space-y-3">
                <!-- Total de clientes -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.total.toLocaleString() }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Total {{ stats.is_admin ? 'en el sistema' : 'asignados' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                            <TrendingUp class="w-3 h-3 mr-1" />
                            Activos
                        </div>
                    </div>
                </div>

                <!-- Clientes nuevos este mes -->
                <div class="flex items-center justify-between pt-2 border-t border-gray-200/50 dark:border-gray-700/30">
                    <div class="flex items-center ">
                        <UserPlus class="w-3.5 h-3.5 text-blue-500" />
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Recientes
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            +{{ stats.new_this_month }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Indicador de rol -->
            <div class="mt-2 pt-2 border-t border-gray-200/40 dark:border-gray-700/30">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Vista {{ stats.is_admin ? 'Administrador' : 'Vendedor' }}
                    </span>
                    <div class="w-1.5 h-1.5 rounded-full" :class="stats.is_admin ? 'bg-purple-400' : 'bg-blue-400'"></div>
                </div>
            </div>
        </div>
    </div>
</template>
