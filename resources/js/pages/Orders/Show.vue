<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    order: Object
})

const breadcrumbs = [
    {
        title: 'Pedidos',
        href: '/orders',
    },
    {
        title: `Pedido #${props.order.fact_num}`,
        href: `/orders/${props.order.fact_num}`,
    },
]

const getStatusBadgeClass = (status, anulada) => {
    if (anulada) return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'

    switch (status) {
        case 'P':
        case 'PEN': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
        case 'A':
        case 'APR': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
        case 'R':
        case 'PRO': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        case 'E':
        case 'ENT': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}

const getStatusText = (status, anulada) => {
    if (anulada) return 'Anulado'

    switch (status) {
        case 'P':
        case 'PEN': return 'Pendiente'
        case 'A':
        case 'APR': return 'Aprobado'
        case 'R':
        case 'PRO': return 'Procesado'
        case 'E':
        case 'ENT': return 'Entregado'
        default: return status
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-VE', {
        style: 'currency',
        currency: 'VES'
    }).format(amount)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-VE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const printOrder = () => {
    window.print()
}
</script>

<template>
    <Head :title="`Pedido #${order.fact_num}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Pedido #{{ order.fact_num }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Detalles completos del pedido
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="printOrder"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
                    </button>
                    <Link
                        v-if="!order.anulada"
                        :href="`/orders/${order.fact_num}/edit`"
                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información del pedido -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Estado y fechas -->
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Estado del Pedido
                                </h3>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusBadgeClass(order.status, order.anulada)">
                                    {{ getStatusText(order.status, order.anulada) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Emisión</p>
                                    <p class="text-lg text-gray-900 dark:text-white">{{ formatDate(order.fec_emis) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Vencimiento</p>
                                    <p class="text-lg text-gray-900 dark:text-white">{{ formatDate(order.fec_venc) }}</p>
                                </div>
                            </div>

                            <div v-if="order.descrip" class="mt-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</p>
                                <p class="text-gray-900 dark:text-white">{{ order.descrip }}</p>
                            </div>

                            <div v-if="order.comentario" class="mt-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Comentarios</p>
                                <p class="text-gray-900 dark:text-white">{{ order.comentario }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Artículos -->
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                Artículos del Pedido
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ order.rows.length }} artículo{{ order.rows.length !== 1 ? 's' : '' }} en este pedido
                            </p>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Artículo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Cantidad
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Precio Unit.
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Unidad
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="row in order.rows" :key="row.reng_num">
                                            <td class="px-6 py-4">
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ row.article.art_des }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ row.article.co_art }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ row.total_art }}</td>
                                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ formatCurrency(row.prec_vta) }}</td>
                                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ row.uni_venta }}</td>
                                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(row.reng_neto) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información lateral -->
                <div class="space-y-6">
                    <!-- Cliente -->
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                Información del Cliente
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ order.client.cli_des }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</p>
                                    <p class="text-gray-900 dark:text-white">{{ order.client.co_cli }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">RIF</p>
                                    <p class="text-gray-900 dark:text-white">{{ order.client.rif }}</p>
                                </div>
                                <div v-if="order.client.direc1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</p>
                                    <p class="text-gray-900 dark:text-white">{{ order.client.direc1 }}</p>
                                </div>
                                <div v-if="order.client.telefonos">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</p>
                                    <p class="text-gray-900 dark:text-white">{{ order.client.telefonos }}</p>
                                </div>
                                <div v-if="order.client.email">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-gray-900 dark:text-white">{{ order.client.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendedor -->
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                Vendedor
                            </h3>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ order.seller.ven_des }}</p>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</p>
                                <p class="text-gray-900 dark:text-white">{{ order.seller.co_ven }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <!-- <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                Resumen Financiero
                            </h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-900 dark:text-white">Subtotal:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(order.tot_bruto) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-900 dark:text-white">IVA (16%):</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(order.iva) }}</span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                                    <div class="flex justify-between">
                                        <span class="font-bold text-gray-900 dark:text-white">Total:</span>
                                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{ formatCurrency(order.tot_neto + order.iva) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }
}
</style>
