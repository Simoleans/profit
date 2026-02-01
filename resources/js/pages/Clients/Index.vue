<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShowClient from '@/components/ShowClient.vue';
import CreateClient from '@/components/CreateClient.vue';
import EditClient from '@/components/EditClient.vue';
import DeleteClient from '@/components/DeleteClient.vue';
import { ref, watch } from 'vue';

// Props recibidas del controlador
const props = defineProps({
    clients: Object,
    search: String,
    activeTab: String,
});

// Estado para la búsqueda y tabs
const searchQuery = ref(props.search || '');
const currentTab = ref(props.activeTab || 'processed');

// Función para formatear moneda
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Función para formatear fecha
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-VE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
};

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Clientes',
        href: '/clients',
    },
];

// Función para realizar búsqueda
const performSearch = () => {
    router.get('/clients', {
        search: searchQuery.value,
        tab: currentTab.value
    }, {
        preserveState: true,
        replace: true,
    });
};

// Función para cambiar de tab
const changeTab = (tab: string) => {
    currentTab.value = tab;
    router.get('/clients', {
        search: searchQuery.value,
        tab: tab
    }, {
        preserveState: true,
        replace: true,
    });
};

// Watch para búsqueda en tiempo real (con debounce)
let searchTimeout: NodeJS.Timeout | null = null;
watch(searchQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
});

// Función para ir a una página específica
const goToPage = (url: string) => {
    if (url) {
        router.visit(url);
    }
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con título y botón de agregar -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Clientes
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Administra tus clientes temporales y procesados
                    </p>
                </div>
                <CreateClient />
            </div>

            <!-- Card contenedor de la tabla -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <nav class="-mb-px flex space-x-8 px-6 min-w-max" aria-label="Tabs">
                        <button
                            @click="changeTab('processed')"
                            :class="[
                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                currentTab === 'processed'
                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Clientes Procesados
                            <span v-if="currentTab === 'processed'" class="ml-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                {{ clients.total }}
                            </span>
                        </button>
                        <button
                            @click="changeTab('temp')"
                            :class="[
                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                currentTab === 'temp'
                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Clientes Temporales
                            <span v-if="currentTab === 'temp'" class="ml-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                {{ clients.total }}
                            </span>
                        </button>
                        <button
                            @click="changeTab('balance')"
                            :class="[
                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                currentTab === 'balance'
                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Clientes con Saldo
                            <span v-if="currentTab === 'balance'" class="ml-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                {{ clients.total }}
                            </span>
                        </button>
                        <button
                            @click="changeTab('retenciones')"
                            :class="[
                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                currentTab === 'retenciones'
                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Facturas sin Retenciones
                            <span v-if="currentTab === 'retenciones'" class="ml-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                {{ clients.total }}
                            </span>
                        </button>
                    </nav>
                </div>


                <!-- Header del card con búsqueda -->
                <div class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                {{ currentTab === 'processed' ? 'Clientes Procesados' : currentTab === 'temp' ? 'Clientes Temporales' : currentTab === 'balance' ? 'Clientes con Saldo' : 'Facturas sin Retenciones' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <template v-if="currentTab === 'retenciones'">
                                    {{ clients.total }} factura{{ clients.total !== 1 ? 's' : '' }} sin retención{{ clients.total !== 1 ? 'es' : '' }}
                                </template>
                                <template v-else>
                                    {{ clients.total }} cliente{{ clients.total !== 1 ? 's' : '' }} {{ currentTab === 'processed' ? 'procesado' : currentTab === 'temp' ? 'temporal' : 'con saldo' }}{{ clients.total !== 1 ? 's' : '' }}
                                </template>
                            </p>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="relative w-full sm:w-72">
                            <Label for="search" class="sr-only">Buscar clientes</Label>
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
                                placeholder="Buscar por nombre o código..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Tabla responsive -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Header para tabs processed y temp -->
                        <thead v-if="currentTab !== 'balance' && currentTab !== 'retenciones'" class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Código
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nombre/Razón Social
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:table-cell">
                                    Dirección
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Ciudad
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <!-- Header para tab balance -->
                        <thead v-else-if="currentTab === 'balance'" class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Código
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nombre
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Saldo
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <!-- Header para tab retenciones -->
                        <thead v-else class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nro. Factura
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Fecha Emisión
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Código Cliente
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Cliente
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    IVA
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Neto
                                </th>
                            </tr>
                        </thead>
                        <!-- Tbody para tabs processed y temp -->
                        <tbody v-if="currentTab !== 'balance' && currentTab !== 'retenciones'" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="clients.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron clientes con ese criterio' : (currentTab === 'processed' ? 'No tienes clientes procesados' : 'No tienes clientes temporales') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de clientes -->
                            <tr v-for="client in clients.data" :key="client.co_cli" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ client.co_cli }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ client.cli_des }}</span>
                                        <small class="text-xs text-gray-500">{{ client.rif }}</small>
                                        <!-- En móvil, mostrar la dirección debajo del nombre -->
                                        <div class="mt-1 sm:hidden">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ client.direc1 || 'Sin dirección' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 sm:table-cell">
                                    <div class="max-w-xs truncate">
                                        {{ client.direc1 || 'Sin dirección' }}
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 md:table-cell">
                                    {{ client.ciudad || 'Sin ciudad' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <ShowClient :client="client" :tab="currentTab" />
                                        <!-- <EditClient :client="client" /> -->
                                        <DeleteClient :client="client" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Tbody para tab balance -->
                        <tbody v-else-if="currentTab === 'balance'" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="clients.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron clientes con ese criterio' : 'No hay clientes con saldo' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de clientes con saldo -->
                            <tr v-for="client in clients.data" :key="client.co_cli" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ client.co_cli }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ client.cli_des }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ client.saldo ? new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(client.saldo) : '$0.00' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="router.visit(`/clients/balance-detail/${client.co_cli}`)"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800"
                                            title="Ver detalle de cuentas por cobrar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Tbody para tab retenciones -->
                        <tbody v-else class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="clients.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron facturas con ese criterio' : 'No hay facturas sin retenciones' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de facturas sin retenciones -->
                            <tr v-for="factura in clients.data" :key="factura.fact_num" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ factura.fact_num }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(factura.fec_emis) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ factura.co_cli }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="max-w-xs truncate">
                                        {{ factura.cli_des }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(factura.iva) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(factura.tot_neto) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="clients.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando {{ clients.from || 0 }} a {{ clients.to || 0 }} de {{ clients.total }} resultados
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="page in clients.links"
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
