<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted,watch } from 'vue';

// Components
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Props
const props = defineProps({
    user: Object
});

// Estado del formulario
const form = ref({
    name: '',
    co_ven: '',
    password: '',
    password_confirmation: '',
    rol: '0'
});

const errors = ref({});
const processing = ref(false);
const isOpen = ref(false);

// Computed para verificar si el formulario está completo
const isFormValid = computed(() => {
    const passwordsMatch = !form.value.password || form.value.password === form.value.password_confirmation;
    return passwordsMatch; // Solo validar que las contraseñas coincidan
});

// Función para enviar el formulario
const submitForm = () => {
    processing.value = true;
    errors.value = {};

    // Preparar datos para envío (solo rol y contraseña si se proporciona)
    const updateData = {
        rol: form.value.rol
    };

    // Solo incluir contraseña si se proporcionó una nueva
    if (form.value.password.trim()) {
        updateData.password = form.value.password;
        updateData.password_confirmation = form.value.password_confirmation;
    }

    console.log('Update data:', updateData);

    router.put(`/users/${props.user.co_ven}`, updateData, {
        onSuccess: () => {
            isOpen.value = false;
            processing.value = false;
        },
        onError: (error) => {
            errors.value = error;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

// No necesitamos funciones de búsqueda ya que código y nombre no se editan

// Función para resetear el formulario a los valores originales
const resetForm = () => {
    form.value = {
        name: props.user.name,
        co_ven: props.user.co_ven,
        password: '',
        password_confirmation: '',
        rol: props.user.rol
    };
    errors.value = {};
};

// Inicializar formulario cuando se monta el componente
onMounted(() => {
    resetForm();
});

// Watch para resetear cuando cambie el usuario
watch(() => props.user, () => {
    if (props.user) {
        resetForm();
    }
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="sr-only">Editar usuario</span>
            </button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <form @submit.prevent="submitForm" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Usuario</DialogTitle>
                    <DialogDescription>
                        Modifica la información del usuario {{ props.user.name }}.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Código de vendedor (solo lectura) -->
                    <div class="grid gap-2">
                        <Label for="edit_co_ven">Código de vendedor</Label>
                        <Input
                            id="edit_co_ven"
                            v-model="form.co_ven"
                            type="text"
                            readonly
                            class="bg-gray-50 cursor-not-allowed"
                        />
                        <p class="text-sm text-gray-500">
                            El código de vendedor no se puede modificar
                        </p>
                    </div>

                    <!-- Nombre (solo lectura) -->
                    <div class="grid gap-2">
                        <Label for="edit_name">Nombre completo</Label>
                        <Input
                            id="edit_name"
                            v-model="form.name"
                            type="text"
                            readonly
                            class="bg-gray-50 cursor-not-allowed"
                        />
                        <p class="text-sm text-gray-500">
                            El nombre no se puede modificar
                        </p>
                    </div>

                    <!-- Rol -->
                    <div class="grid gap-2">
                        <Label for="edit_rol">Rol</Label>
                        <select
                            id="edit_rol"
                            v-model="form.rol"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="0">Vendedor</option>
                            <option value="1">Administrador</option>
                        </select>
                        <InputError :message="errors.rol" />
                    </div>

                    <!-- Contraseña (opcional) -->
                    <div class="grid gap-2">
                        <Label for="edit_password">Nueva contraseña (opcional)</Label>
                        <Input
                            id="edit_password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Dejar vacío para mantener la actual"
                        />
                        <InputError :message="errors.password" />
                        <p class="text-sm text-gray-500">
                            Solo completa si quieres cambiar la contraseña
                        </p>
                    </div>

                    <!-- Confirmar contraseña (solo si se proporciona nueva contraseña) -->
                    <div v-if="form.password.trim()" class="grid gap-2">
                        <Label for="edit_password_confirmation">Confirmar nueva contraseña</Label>
                        <Input
                            id="edit_password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Repite la nueva contraseña"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="secondary"
                            @click="resetForm"
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing || !isFormValid"
                        class="min-w-[100px]"
                        :class="!isFormValid && !processing ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        <span v-if="processing" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Actualizando...
                        </span>
                        <span v-else>Actualizar Usuario</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
