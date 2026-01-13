<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShowArticle from '@/components/ShowArticle.vue';
import { ref, watch, computed } from 'vue';

// Props recibidas del controlador
const props = defineProps({
    articles: Object,
    search: String,
    categoryFilter: String,
    lineFilter: String,
    sublFilter: String,
    filterOptions: Object,
});

// Estado para la búsqueda y filtros
const searchQuery = ref(props.search || '');
const selectedCategory = ref(props.categoryFilter || '');
const selectedLine = ref(props.lineFilter || '');
const selectedSubl = ref(props.sublFilter || '');

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Artículos',
        href: '/articles',
    },
];

// Función para realizar búsqueda con filtros
const performSearch = () => {
    const params = {
        search: searchQuery.value,
        category: selectedCategory.value,
        line: selectedLine.value,
        subl: selectedSubl.value,
    };

    // Remover parámetros vacíos
    Object.keys(params).forEach(key => {
        if (!params[key]) {
            delete params[key];
        }
    });

    router.get('/articles', params, {
        preserveState: true,
        replace: true,
    });
};

// Función para limpiar filtros
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedLine.value = '';
    selectedSubl.value = '';
    performSearch();
};

// Watch para búsqueda en tiempo real (con debounce)
let searchTimeout = null;
watch(searchQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
});

// Watch para filtros (sin debounce)
watch([selectedCategory, selectedLine, selectedSubl], () => {
    performSearch();
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

// Función para construir la URL del PDF con los filtros actuales
const pdfUrl = computed(() => {
    const params = new URLSearchParams();

    if (searchQuery.value) params.append('search', searchQuery.value);
    if (selectedCategory.value) params.append('category', selectedCategory.value);
    if (selectedLine.value) params.append('line', selectedLine.value);
    if (selectedSubl.value) params.append('subl', selectedSubl.value);

    const queryString = params.toString();
    return `/articles/pdf${queryString ? '?' + queryString : ''}`;
});
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
                <a
                    :href="pdfUrl"
                    target="_blank"
                    class="inline-flex items-center px-4 py-2 border border-red-600 rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 dark:border-red-700"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Descargar PDF
                </a>
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

                <!-- Filtros -->
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900 sm:px-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtros:</h4>

                            <!-- Filtro Categoría -->
                            <div class="flex items-center gap-2">
                                <Label for="category" class="text-xs text-gray-600 dark:text-gray-400">Categoría:</Label>
                                <select
                                    id="category"
                                    v-model="selectedCategory"
                                    class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in filterOptions.categories" :key="category.co_cat" :value="category.co_cat">
                                        {{ category.cat_des }}
                                    </option>
                                </select>
                            </div>

                            <!-- Filtro Línea -->
                            <div class="flex items-center gap-2">
                                <Label for="line" class="text-xs text-gray-600 dark:text-gray-400">Línea:</Label>
                                <select
                                    id="line"
                                    v-model="selectedLine"
                                    class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="">Todas las líneas</option>
                                    <option v-for="line in filterOptions.lines" :key="line.co_lin" :value="line.co_lin">
                                        {{ line.lin_des }}
                                    </option>
                                </select>
                            </div>

                            <!-- Filtro Sublínea -->
                            <div class="flex items-center gap-2">
                                <Label for="subl" class="text-xs text-gray-600 dark:text-gray-400">Sublínea:</Label>
                                <select
                                    id="subl"
                                    v-model="selectedSubl"
                                    class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="">Todas las sublíneas</option>
                                    <option v-for="subl in filterOptions.subls" :key="subl.co_subl" :value="subl.co_subl">
                                        {{ subl.subl_des }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Botón limpiar filtros -->
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar filtros
                        </button>
                    </div>

                    <!-- Indicadores de filtros activos -->
                    <div v-if="selectedCategory || selectedLine || selectedSubl" class="mt-3 flex flex-wrap gap-2">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Filtros activos:</span>
                        <span v-if="selectedCategory" class="inline-flex items-center rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                            Categoría: {{ filterOptions.categories.find(c => c.co_cat === selectedCategory)?.cat_des }}
                        </span>
                        <span v-if="selectedLine" class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            Línea: {{ filterOptions.lines.find(l => l.co_lin === selectedLine)?.lin_des }}
                        </span>
                        <span v-if="selectedSubl" class="inline-flex items-center rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                            Sublínea: {{ filterOptions.subls.find(s => s.co_subl === selectedSubl)?.subl_des }}
                        </span>
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
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Línea
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Precio Venta
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="articles.data.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center md:hidden">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron artículos con ese criterio' : 'No hay artículos disponibles' }}
                                        </span>
                                    </div>
                                </td>
                                <td colspan="5" class="hidden px-6 py-12 text-center md:table-cell">
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
                                    <div class="flex flex-col gap-1">
                                        <span>{{ article.art_des }}</span>

                                        <!-- Indicadores: Stock y Promoción -->
                                        <div class="flex flex-wrap items-center gap-1">
                                            <!-- Indicador de stock -->
                                            <!-- <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-[10px] font-medium"
                                                  :class="article.stock_act > 0
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'">
                                                {{ article.stock_act > 0 ? `Stock: ${Math.floor(article.stock_act)}` : 'SIN STOCK' }}
                                            </span> -->

                                            <!-- Etiqueta de promoción si co_cat = 9 -->
                                            <span v-if="article.co_cat?.toString().trim() === '9'" class="inline-flex w-fit items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-medium text-orange-700 dark:bg-orange-900 dark:text-orange-300">
                                                En promoción
                                            </span>
                                        </div>

                                        <!-- Precio en móvil -->
                                        <div class="mt-1 md:hidden">
                                            <span class="inline-flex items-center rounded-lg bg-green-100 px-3 py-1.5 text-sm font-bold text-green-800 shadow-sm dark:bg-green-900 dark:text-green-300">
                                                {{ formatPrice(article.prec_vta1) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 md:table-cell">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ article.line?.lin_des || 'Sin línea' }}
                                    </span>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-right text-sm font-medium md:table-cell">
                                    <span class="inline-flex items-center rounded-lg bg-green-100 px-3 py-1.5 text-sm font-bold text-green-800 shadow-sm dark:bg-green-900 dark:text-green-300">
                                        {{ formatPrice(article.prec_vta1) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <ShowArticle :article="article" />
                                    </div>
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
