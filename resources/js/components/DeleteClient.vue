<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

// Props
const props = defineProps({
    client: {
        type: Object,
        required: true
    }
});

const processing = ref(false);
const isOpen = ref(false);

// Función para eliminar cliente (soft delete)
const deleteClient = () => {
    processing.value = true;

    const rifEncoded = encodeURIComponent(props.client.rif.trim());
    console.log('Enviando DELETE a:', `/clients/${rifEncoded}`);
    router.delete(`/clients/${rifEncoded}`, {
        onSuccess: () => {
            isOpen.value = false;
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 hover:bg-red-50" v-show="client.co_cli == '' || client.co_cli == null">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Rechazar
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>¿Estás seguro?</DialogTitle>
                <DialogDescription class="space-y-2">
                    <p>
                        Esta acción rechazará permanentemente el cliente:
                    </p>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="font-medium text-gray-900">{{ client.cli_des }}</p>
                        <p class="text-sm text-gray-600">Código: {{ client.co_cli }}</p>
                        <p class="text-sm text-gray-600">RIF: {{ client.rif }}</p>
                    </div>
                    <p class="text-sm text-gray-600">
                        El cliente no será eliminado físicamente, pero será marcado como rechazado y no aparecerá en las listas.
                    </p>
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button type="button" variant="outline" :disabled="processing">
                        Cancelar
                    </Button>
                </DialogClose>
                <Button
                    :disabled="processing"
                    class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
                    @click="deleteClient"
                >
                    <span v-if="processing" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Eliminando...
                    </span>
                    <span v-else>Sí, desactivar cliente</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
