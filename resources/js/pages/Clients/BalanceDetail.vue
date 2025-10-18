<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// Props recibidas del controlador
const props = defineProps({
    client: Object,
    balanceDetail: Array,
});

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Clientes',
        href: '/clients',
    },
    {
        title: 'Detalle de Cuentas por Cobrar',
        href: '#',
    },
];

// Función para formatear fechas
const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
};

// Función para formatear moneda
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

// Función para calcular el total del saldo
const totalSaldo = () => {
    return props.balanceDetail?.reduce((total: string, item: any) => parseFloat(total) + parseFloat(item.saldo || 0), 0);
};
</script>

<template>
    <Head :title="`Detalle - ${client?.cli_des || 'Cliente'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con información del cliente -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Detalle de Cuentas por Cobrar
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Cliente: {{ client?.cli_des }} ({{ client?.co_cli }})
                        </p>
                    </div>
                    <button
                        @click="router.visit('/clients?tab=balance')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </button>
                </div>
            </div>

            <!-- Resumen del saldo total -->
            <div class="mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">
                                Saldo Total
                            </h3>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ formatCurrency(totalSaldo()) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de detalle -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <!-- Header del card -->
                <div class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Documentos Pendientes
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ balanceDetail.length }} documento{{ balanceDetail.length !== 1 ? 's' : '' }} pendiente{{ balanceDetail.length !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>

                <!-- Tabla responsive -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Vendedor
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tipo Doc
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nro Doc
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 lg:table-cell">
                                    Fec Emis
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 lg:table-cell">
                                    Fec Entrega
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 lg:table-cell">
                                    Fec Venc
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 lg:table-cell">
                                    Observa
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Monto Net
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Saldo
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="balanceDetail.length === 0">
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            No hay documentos pendientes para este cliente
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de documentos -->
                            <tr v-for="item in balanceDetail" :key="`${item.co_ven}-${item.tipo_doc}-${item.nro_doc}`" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ item.co_ven }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ item.tipo_doc }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ item.nro_doc }}
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 lg:table-cell">
                                    {{ formatDate(item.fec_emis) }}
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 lg:table-cell">
                                    {{ item.fec_entrega }}
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 lg:table-cell">
                                    {{ formatDate(item.fec_venc) }}
                                </td>
                                <td class="hidden px-6 py-4 text-sm text-gray-500 dark:text-gray-400 lg:table-cell">
                                    <div class="max-w-xs truncate">
                                        {{ item.observa || 'Sin observaciones' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(item.monto_net) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(item.saldo) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
