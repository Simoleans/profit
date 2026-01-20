<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    order: Object
})

const approveOrder = () => {
    if (confirm('¿Estás seguro de que deseas aprobar este pedido? Esta acción no se puede deshacer.')) {
        router.post(`/orders/${props.order.fact_num}/approve`, {}, {
            onSuccess: () => {
                // El controlador redirigirá de vuelta a la vista del pedido
            }
        })
    }
}

const resendEmail = () => {
    if (confirm('¿Deseas reenviar el correo de notificación al cliente?')) {
        router.post(`/orders/${props.order.fact_num}/resend-email`, {}, {
            onSuccess: () => {
                // El controlador redirigirá de vuelta a la vista del pedido
            }
        })
    }
}

// Verificar si el cliente tiene un email válido
const hasValidEmail = () => {
    if (!props.order.client) return false
    const email = props.order.client.email
    if (!email) return false
    const trimmedEmail = email.trim()
    return trimmedEmail.length > 0
}

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
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
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

const shareWhatsApp = () => {
    const message = generateWhatsAppMessage()
    const encodedMessage = encodeURIComponent(message)
    const whatsappUrl = `https://wa.me/?text=${encodedMessage}`
    window.open(whatsappUrl, '_blank')
}

const generateWhatsAppMessage = () => {
    const statusText = getStatusText(props.order.status, props.order.anulada)
    const total = parseFloat(props.order.tot_neto) + parseFloat(props.order.iva)

    let message = `*PEDIDO #${props.order.fact_num}*\n\n`

    // Estado
    message += `*Estado:* ${statusText}\n`

    // Cliente
    message += `*Cliente:* ${props.order.client.cli_des}\n`
    message += `*RIF:* ${props.order.client.rif}\n`
    message += `*Código:* ${props.order.client.co_cli}\n\n`

    // Descripción si existe
    if (props.order.descrip) {
        message += `*Descripción:* ${props.order.descrip}\n\n`
    }

    // Artículos
    message += `*ARTÍCULOS (${props.order.rows.length}):*\n`
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`

    props.order.rows.forEach((row, index) => {
        message += `${index + 1}. *${row.article.art_des}*${row.promotion === 1 ? 'EN PROMOCIÓN' : ''}\n`
        message += `   Código: ${row.article.co_art}\n`
        message += `   Precio: ${formatCurrency(row.prec_vta)}\n`
        message += `   Total: ${formatCurrency(row.reng_neto)}\n\n`
    })

    // Totales
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`
    message += `*RESUMEN FINANCIERO:*\n`
    message += `   Subtotal: ${formatCurrency(props.order.tot_bruto)}\n`
    message += `   IVA (16%): ${formatCurrency(props.order.iva)}\n`
    message += `   *TOTAL: ${formatCurrency(total)}*\n\n`

    // Comentarios si existen
    if (props.order.comentario) {
        message += `*Comentarios:* ${props.order.comentario}\n\n`
    }

    // Dirección de entrega si existe
    if (props.order.dir_ent) {
        message += `*Dirección de Entrega:* ${props.order.dir_ent}\n\n`
    }

    message += `Generado el ${formatDate(new Date())}`

    return message
}
</script>

<template>
    <Head :title="`Pedido #${order.fact_num}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Pedido #{{ order.fact_num }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Detalles completos del pedido
                    </p>
                </div>
                <div class="grid grid-cols-3 lg:grid-cols-4 gap-2 no-print">
                    <button
                        @click="printOrder"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
                    </button>
                    <button
                        @click="shareWhatsApp"
                        class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                        WhatsApp
                    </button>
                    <button
                        v-if="hasValidEmail()"
                        @click="resendEmail"
                        class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500"
                        title="Reenviar correo al cliente"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Reenviar Correo
                    </button>
                    <button
                        v-if="!order.anulada && order.status === 'P'"
                        @click="approveOrder"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Aprobar
                    </button>
                    <Link
                        v-if="!order.anulada && order.status === 'P'"
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
                    <div class="grid grid-cols-2 gap-2">
                        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6 border border-gray-300">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Estado del Pedido
                                </h3>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusBadgeClass(order.status, order.anulada)">
                                    {{ getStatusText(order.status, order.anulada) }}
                                </span>
                            </div>

                            <!-- <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Emisión</p>
                                    <p class="text-lg text-gray-900 dark:text-white">{{ formatDate(order.fec_emis) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Vencimiento</p>
                                    <p class="text-lg text-gray-900 dark:text-white">{{ formatDate(order.fec_venc) }}</p>
                                </div>
                            </div> -->

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
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800 border border-gray-300">
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
                                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{ formatCurrency(parseFloat(order.tot_neto) + parseFloat(order.iva)) }}</span>
                                    </div>
                                </div>
                            </div>
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
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300">
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
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 dark:text-white">{{ row.article.art_des }}</span>

                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ row.article.co_art }} <span v-if="row.promotion" class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                                                            En promoción
                                                        </span></div>
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
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800 border border-gray-300">
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
                    <!-- <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
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
                    </div> -->

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
