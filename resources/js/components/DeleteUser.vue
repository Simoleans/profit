<script setup lang="ts">
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
const props = defineProps<{
    user: {
        id: number;
        co_ven: string;
        name: string;
        rol: string;
    };
}>();

// Estado para controlar el diálogo
const isOpen = ref(false);

// Función para eliminar usuario
const deleteUser = () => {
    router.delete(`/users/${props.user.id}`, {
        onSuccess: () => {
            isOpen.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="sr-only">Eliminar usuario</span>
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>¿Estás seguro de desactivar este usuario?</DialogTitle>
                <DialogDescription>
                    Esta acción desactivará al usuario <strong>{{ user.name }}</strong> ({{ user.co_ven }}).
                    El usuario no podrá acceder al sistema, pero sus datos se conservarán.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary">
                        Cancelar
                    </Button>
                </DialogClose>

                <Button variant="destructive" @click="deleteUser">
                    Desactivar usuario
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
