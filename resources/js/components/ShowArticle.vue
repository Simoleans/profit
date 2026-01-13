<script setup>
import { ref } from 'vue';
import { apiRequest } from '@/lib/api';

// Components
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

// Props
const props = defineProps({
    article: Object,
});

// Estado del modal
const isOpen = ref(false);
const articleDetails = ref(null);
const loading = ref(false);
const error = ref('');

// Función para cargar detalles del artículo
const loadArticleDetails = async () => {
    if (articleDetails.value) return; // Ya está cargado

    loading.value = true;
    error.value = '';

    try {
        const coArtEncoded = encodeURIComponent(props.article.co_art.trim());
        const url = `/articles/${coArtEncoded}`;
        const response = await apiRequest('GET', url);

        if (response.ok) {
            const data = await response.json();
            // Aplicar trim a todos los campos
            articleDetails.value = Object.keys(data).reduce((acc, key) => {
                if (typeof data[key] === 'string') {
                    acc[key] = data[key].trim();
                } else {
                    acc[key] = data[key];
                }
                return acc;
            }, {});
        } else {
            const errorData = await response.json();
            error.value = errorData.error || errorData.message || 'Error al cargar los detalles del artículo';
        }
    } catch (err) {
        console.error('Error cargando artículo:', err);
        error.value = 'Error al cargar los detalles del artículo';
    } finally {
        loading.value = false;
    }
};

// Función para formatear campos vacíos
const formatField = (value) => {
    if (value === null || value === undefined) return 'N/A';
    if (typeof value === 'string') {
        const trimmed = value.trim();
        return trimmed ? trimmed : 'N/A';
    }
    return value;
};

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
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <button
                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 cursor-pointer"
                @click="loadArticleDetails"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="sr-only">Ver detalles del artículo</span>
            </button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader class="space-y-3">
                <DialogTitle>Detalles del Artículo</DialogTitle>
                <DialogDescription>
                    Información completa del artículo {{ article.co_art }}
                </DialogDescription>
            </DialogHeader>

            <!-- Loading state -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600 dark:text-gray-400">Cargando detalles...</span>
                </div>
            </div>

            <!-- Error state -->
            <div v-if="error" class="rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                            Error
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Article details -->
            <div v-if="articleDetails && !loading" class="space-y-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código Artículo</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm font-mono dark:bg-gray-700 dark:text-white">
                            {{ formatField(articleDetails.co_art) }}
                        </div>
                    </div>

                   <!--  <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Stock Actual</Label>
                        <div class="rounded-md px-3 py-2 text-sm font-semibold"
                             :class="articleDetails.stock_act > 0
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'">
                            {{ articleDetails.stock_act > 0 ? Math.floor(articleDetails.stock_act) : 'SIN STOCK' }}
                        </div>
                    </div> -->
                </div>

                <!-- Nombre/Descripción -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Artículo</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm font-medium dark:bg-gray-700 dark:text-white">
                        {{ formatField(articleDetails.art_des) }}
                    </div>
                </div>

                <!-- Descripción Adicional -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción Adicional</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700 dark:text-white min-h-[4rem]">
                        {{ formatField(articleDetails.descrip_adi) }}
                    </div>
                </div>

                <!-- Medidas -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Medidas</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700 dark:text-white">
                        {{ formatField(articleDetails.medidas) }}
                    </div>
                </div>

                <!-- Clasificación -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</Label>
                        <div class="rounded-md bg-orange-50 px-3 py-2 text-sm dark:bg-orange-900/20 dark:text-orange-300">
                            {{ formatField(articleDetails.category?.cat_des) || formatField(articleDetails.co_cat) }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Línea</Label>
                        <div class="rounded-md bg-blue-50 px-3 py-2 text-sm dark:bg-blue-900/20 dark:text-blue-300">
                            {{ formatField(articleDetails.line?.lin_des) || formatField(articleDetails.co_lin) }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sublínea</Label>
                        <div class="rounded-md bg-purple-50 px-3 py-2 text-sm dark:bg-purple-900/20 dark:text-purple-300">
                            {{ formatField(articleDetails.subl?.subl_des) || formatField(articleDetails.co_subl) }}
                        </div>
                    </div>
                </div>

                <!-- Unidad de Venta, Venta Mínima y Precios -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Venta Mínima</Label>
                        <div class="rounded-md bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                            {{ articleDetails.venta_minima ? Math.floor(articleDetails.venta_minima) : 'N/A' }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio de Venta</Label>
                        <div class="rounded-md bg-green-50 px-3 py-2 text-sm font-bold text-green-800 dark:bg-green-900/20 dark:text-green-300">
                            {{ formatPrice(articleDetails.prec_vta1) }}
                        </div>
                    </div>
                </div>

                <!-- Indicador de Promoción -->
                <div v-if="articleDetails.co_cat?.toString().trim() === '9'" class="rounded-md bg-orange-100 border border-orange-200 px-4 py-3 dark:bg-orange-900/30 dark:border-orange-800">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        <span class="text-sm font-medium text-orange-800 dark:text-orange-300">
                            Este artículo está en promoción
                        </span>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="secondary">
                        Cerrar
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

