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
    client: Object,
    tab: {
        type: String,
        default: 'processed'
    }
});

// Estado del modal
const isOpen = ref(false);
const clientDetails = ref(null);
const loading = ref(false);
const error = ref('');

// Función para cargar detalles del cliente
const loadClientDetails = async () => {
    if (clientDetails.value) return; // Ya está cargado

    loading.value = true;
    error.value = '';

    try {
        const rifEncoded = encodeURIComponent(props.client.rif.trim());
        const url = `/clients/${rifEncoded}?tab=${props.tab}`;
        const response = await apiRequest('GET', url);

        if (response.ok) {
            const data = await response.json();
            clientDetails.value = data;
        } else {
            const errorData = await response.json();
            error.value = errorData.error || errorData.message || 'Error al cargar los detalles del cliente';
        }
    } catch (err) {
        console.error('Error cargando cliente:', err);
        error.value = 'Error al cargar los detalles del cliente';
    } finally {
        loading.value = false;
    }
};

// Función para formatear campos vacíos
const formatField = (value) => {
    return value && value.trim() ? value : 'No especificado';
};

// Función para descargar documento
const downloadDocument = async (mediaId) => {
    try {
        window.location.href = `/clients/media/${mediaId}/download`;
    } catch (err) {
        console.error('Error descargando documento:', err);
    }
};

// Función para obtener el icono según el tipo de archivo
const getFileIcon = (mimeType) => {
    if (mimeType.includes('pdf')) {
        return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
    } else if (mimeType.includes('word') || mimeType.includes('document')) {
        return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
    } else if (mimeType.includes('image')) {
        return 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z';
    }
    return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
};

// Función para obtener el color según el tipo de archivo
const getFileColor = (mimeType) => {
    if (mimeType.includes('pdf')) {
        return 'text-red-600 dark:text-red-400';
    } else if (mimeType.includes('word') || mimeType.includes('document')) {
        return 'text-blue-600 dark:text-blue-400';
    } else if (mimeType.includes('image')) {
        return 'text-green-600 dark:text-green-400';
    }
    return 'text-gray-600 dark:text-gray-400';
};

// Función para formatear el tamaño del archivo
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <button
                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 cursor-pointer"
                @click="loadClientDetails"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="sr-only">Ver detalles del cliente</span>
            </button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader class="space-y-3">
                <DialogTitle>Detalles del Cliente</DialogTitle>
                <DialogDescription>
                    Información completa del cliente {{ client.cli_des }}
                </DialogDescription>
                <!-- Indicador de tabla -->
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                          :class="props.tab === 'temp'
                            ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
                            : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'">
                        {{ props.tab === 'temp' ? 'Cliente Temporal' : 'Cliente Procesado' }}
                    </span>
                </div>
            </DialogHeader>

            <!-- Loading state -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600">Cargando detalles...</span>
                </div>
            </div>

            <!-- Error state -->
            <div v-if="error" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Error
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client details -->
            <div v-if="clientDetails && !loading" class="space-y-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código Cliente</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm font-mono dark:bg-gray-700">
                            {{ clientDetails.co_cli }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">RIF</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.rif) }}
                        </div>
                    </div>
                </div>

                <!-- Nombre/Razón Social -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre/Razón Social</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm font-medium dark:bg-gray-700">
                        {{ clientDetails.cli_des }}
                    </div>
                </div>

                <!-- Direcciones -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección Principal</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.direc1) }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección Secundaria</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.direc2) }}
                        </div>
                    </div>
                </div>

                <!-- Ciudad y Contacto -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.ciudad) }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfonos</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.telefonos) }}
                        </div>
                    </div>
                </div>

                <!-- Email y Responsable -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.email) }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Responsable</Label>
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700">
                            {{ formatField(clientDetails.respons) }}
                        </div>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Comentarios</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-700 min-h-[4rem]">
                        {{ formatField(clientDetails.comentario) }}
                    </div>
                </div>

                <!-- Código Vendedor -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código Vendedor Asignado</Label>
                    <div class="rounded-md bg-blue-50 px-3 py-2 text-sm font-mono text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                        {{ clientDetails.co_ven }}
                    </div>
                </div>

                <!-- Documentos Adjuntos -->
                <div v-if="clientDetails.media && clientDetails.media.length > 0" class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Documentos Adjuntos</Label>
                    <div class="space-y-2">
                        <div
                            v-for="media in clientDetails.media"
                            :key="media.id"
                            class="flex items-center justify-between p-3 rounded-md bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <svg
                                    class="h-8 w-8 flex-shrink-0"
                                    :class="getFileColor(media.mime_type)"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="getFileIcon(media.mime_type)"
                                    />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ media.original_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatFileSize(media.size) }}
                                    </p>
                                </div>
                            </div>
                            <Button
                                type="button"
                                size="sm"
                                @click="downloadDocument(media.id)"
                                class="flex-shrink-0 ml-2"
                            >
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Sin documentos -->
                <div v-else class="space-y-2">
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Documentos Adjuntos</Label>
                    <div class="rounded-md bg-gray-50 px-3 py-4 text-sm text-gray-500 dark:bg-gray-700 dark:text-gray-400 text-center">
                        <svg class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        No hay documentos adjuntos
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
