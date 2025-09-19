<script setup lang="js">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, watch } from 'vue';

// Props recibidas del controlador
const props = defineProps({
    articles: Object,
    search: String,
});

// Estado para la búsqueda
const searchQuery = ref(props.search || '');

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Artículos',
        href: '/articles',
    },
];

// Función para realizar búsqueda
const performSearch = () => {
    router.get('/articles', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

// Watch para búsqueda en tiempo real (con debounce)
let searchTimeout = null;
watch(searchQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
});

// Función para formatear precio
const formatPrice = (price) => {
    if (!price || price === 0) return 'N/A';
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
};

// Función para ir a una página específica
const goToPage = (url) => {
    if (url) {
        router.visit(url);
    }
};
</script>

<template>
    <Head title="Artículos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con título -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Catálogo de Artículos
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Consulta de productos y precios del inventario
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
                                Lista de Artículos
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ articles.total }} artículo{{ articles.total !== 1 ? 's' : '' }} total{{ articles.total !== 1 ? 'es' : '' }}
                            </p>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="relative w-full sm:w-72">
                            <Label for="search" class="sr-only">Buscar artículos</Label>
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
                                placeholder="Buscar por código o descripción..."
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
                                    Descripción
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Precio Venta
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="articles.data.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron artículos con ese criterio' : 'No hay artículos disponibles' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de artículos -->
                            <tr v-for="article in articles.data" :key="article.co_art" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ article.co_art }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ article.art_des }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <span class="inline-flex items-center rounded-lg bg-green-100 px-3 py-1.5 text-sm font-bold text-green-800 shadow-sm dark:bg-green-900 dark:text-green-300">
                                        {{ formatPrice(article.prec_vta1) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="articles.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando {{ articles.from || 0 }} a {{ articles.to || 0 }} de {{ articles.total }} resultados
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="page in articles.links"
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
