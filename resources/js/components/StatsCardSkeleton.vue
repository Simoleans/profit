<script setup lang="ts">
// Componente Skeleton para las tarjetas de estadísticas
</script>

<template>
    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-gray-100 dark:from-slate-950/20 dark:to-gray-950/20 dark:border-sidebar-border max-w-sm w-full animate-pulse">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-500/5 to-gray-600/5"></div>
        <div class="absolute top-0 right-0 w-20 h-20 bg-gray-500/3 rounded-full -translate-y-10 translate-x-10"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 bg-gray-500/3 rounded-full translate-y-8 -translate-x-8"></div>

        <!-- Contenido -->
        <div class="relative p-4 space-y-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gray-200/50 dark:bg-gray-700/50 rounded-lg shrink-0">
                    <div class="w-5 h-5 bg-gray-300/50 dark:bg-gray-600/50 rounded"></div>
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                </div>
            </div>

            <!-- Estadística principal -->
            <div class="text-center py-2 space-y-2">
                <div class="h-9 bg-gray-200 dark:bg-gray-700 rounded w-24 mx-auto"></div>
                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-32 mx-auto"></div>
            </div>

            <!-- Métricas secundarias -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200/50 dark:border-gray-700/30">
                <!-- Métrica 1 -->
                <div class="text-center space-y-2">
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded-full w-16 mx-auto"></div>
                </div>

                <!-- Métrica 2 -->
                <div class="text-center space-y-2">
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-12 mx-auto"></div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-16 mx-auto"></div>
                </div>
            </div>

            <!-- Indicador de vendedor -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-200/40 dark:border-gray-700/30">
                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-28"></div>
                <div class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 shrink-0"></div>
            </div>
        </div>
    </div>
</template>

