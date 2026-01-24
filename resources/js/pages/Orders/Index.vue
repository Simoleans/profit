<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Props recibidas del controlador
const props = defineProps({
    orders: Object,
    search: String,
})

// Estado para la búsqueda
const searchQuery = ref(props.search || '')

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Pedidos',
        href: '/orders',
    },
]

// Función para realizar búsqueda
const performSearch = () => {
    router.get('/orders', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    })
}

// Watch para búsqueda en tiempo real (con debounce)
let searchTimeout = null
watch(searchQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        performSearch()
    }, 300)
})

// Función para obtener clase del badge según estado
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

// Función para obtener texto del estado
const getStatusText = (status, anulada) => {
    if (anulada) return 'Anulado'

    switch (status) {
        case 'P':
        case 'PEN': return 'Pendiente'
        case 'A':
        case 'APR': return 'Aprobado'
        case 'F':
        case 'FACT': return 'Facturado'
        case 'C':
        case 'CAN': return 'Cancelado'
        case 'R':
        case 'ANU': return 'Anulado'
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
    return new Date(date).toLocaleDateString('es-VE')
}

// Función para ir a una página específica
const goToPage = (url) => {
    if (url) {
        router.visit(url)
    }
}

// Función para eliminar pedido
const deleteOrder = (order) => {
    if (confirm(`¿Estás seguro de que deseas anular el pedido #${order.fact_num}?`)) {
        router.delete(`/orders/${order.fact_num}`)
    }
}
</script>

<template>
    <Head title="Pedidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con título y botón de agregar -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Mis Pedidos
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Gestiona y administra tus pedidos de ventas
                    </p>
                </div>
                <Link
                    href="/orders/create"
                    class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                >
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Nuevo Pedido
                </Link>
            </div>

            <!-- Card contenedor de la tabla -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <!-- Header del card con búsqueda -->
                <div class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Lista de Pedidos
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ orders.total }} pedido{{ orders.total !== 1 ? 's' : '' }} total{{ orders.total !== 1 ? 'es' : '' }}
                            </p>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="relative w-full sm:w-72">
                            <Label for="search" class="sr-only">Buscar pedidos</Label>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <Input
                                id="search"
                                v-model="searchQuery"
                                type="text"
                                class="pl-10"
                                placeholder="Buscar por número de pedido o cliente..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Tabla responsive -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nº Pedido
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Cliente
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:table-cell">
                                    Fecha Emisión
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="orders.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron pedidos con ese criterio' : 'No hay pedidos registrados' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de pedidos -->
                            <tr v-for="order in orders.data" :key="order.fact_num" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-gray-900 dark:text-white">
                                    #{{ order.fact_num }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ order.client?.cli_des }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ order.client?.rif }}</span>
                                        <!-- En móvil, mostrar información adicional debajo del cliente -->
                                        <div class="mt-1 sm:hidden">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(order.fec_emis) }} • {{ order.rows.length }} item{{ order.rows.length !== 1 ? 's' : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 sm:table-cell">
                                    {{ formatDate(order.fec_emis) }}
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white md:table-cell">
                                    {{ formatCurrency(parseFloat(order.tot_neto) + parseFloat(order.iva)) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusBadgeClass(order.status, order.anulada)">
                                        {{ getStatusText(order.status, order.anulada) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/orders/${order.fact_num}`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Ver detalles"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span class="sr-only">Ver pedido</span>
                                        </Link>
                                        <Link
                                            v-if="!order.anulada && order.status === 'P'"
                                            :href="`/orders/${order.fact_num}/edit`"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="sr-only">Editar pedido</span>
                                        </Link>
                                        <button
                                            v-if="!order.anulada"
                                            @click="deleteOrder(order)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Anular"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="sr-only">Anular pedido</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="orders.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando {{ orders.from || 0 }} a {{ orders.to || 0 }} de {{ orders.total }} resultados
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="page in orders.links"
                                :key="page.label"
                                :disabled="!page.url"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                    page.active
                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-600 dark:text-blue-300'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                    !page.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                @click="goToPage(page.url)"
                                v-html="page.label"
                            >
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
