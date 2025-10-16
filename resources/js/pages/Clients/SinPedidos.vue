<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, watch } from 'vue';

interface Cliente {
    co_cli: string;
    cli_des: string;
    fecha_reg: string;
    co_ven: string;
    ven_des: string;
    zon_des: string;
}

// Props recibidas del controlador
const props = defineProps<{
    clientes: Cliente[];
    search?: string;
}>();

// Estado para la búsqueda
const searchQuery = ref(props.search || '');

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Clientes Sin Pedidos',
        href: '/clientes-sin-pedidos',
    },
];

// Función para realizar búsqueda
const performSearch = () => {
    router.get('/clientes-sin-pedidos', {
        search: searchQuery.value,
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

// Filtrar clientes localmente
const filteredClientes = ref(props.clientes);
watch(searchQuery, (newValue: string) => {
    if (newValue) {
        filteredClientes.value = props.clientes.filter((cliente: Cliente) =>
            cliente.cli_des.toLowerCase().includes(newValue.toLowerCase()) ||
            cliente.co_cli.toLowerCase().includes(newValue.toLowerCase()) ||
            cliente.ven_des.toLowerCase().includes(newValue.toLowerCase()) ||
            cliente.zon_des.toLowerCase().includes(newValue.toLowerCase())
        );
    } else {
        filteredClientes.value = props.clientes;
    }
});

// Formatear fecha
const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Clientes Sin Pedidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con título -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Clientes Sin Pedidos
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Clientes activos que aún no han realizado pedidos
                    </p>
                </div>
            </div>

            <!-- Card contenedor de la tabla -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <!-- Header del card con búsqueda -->
                <div class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Listado de Clientes
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ filteredClientes.length }} cliente{{ filteredClientes.length !== 1 ? 's' : '' }} sin pedidos
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
                                placeholder="Buscar por nombre, código, vendedor..."
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
                                    Código
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nombre
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:table-cell">
                                    Vendedor
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Zona
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 lg:table-cell">
                                    Fecha Registro
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="filteredClientes.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron clientes con ese criterio' : 'No hay clientes sin pedidos' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de clientes -->
                            <tr v-for="cliente in filteredClientes" :key="cliente.co_cli" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ cliente.co_cli }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ cliente.cli_des }}</span>
                                        <!-- En móvil, mostrar vendedor y zona debajo del nombre -->
                                        <div class="mt-1 sm:hidden">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ cliente.ven_des }} • {{ cliente.zon_des }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 sm:table-cell">
                                    <div class="max-w-xs truncate">
                                        {{ cliente.ven_des }}
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 md:table-cell">
                                    {{ cliente.zon_des }}
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 lg:table-cell">
                                    {{ formatDate(cliente.fecha_reg) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

