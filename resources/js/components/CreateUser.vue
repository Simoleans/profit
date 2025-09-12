<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import { apiRequest } from '@/lib/api';

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
const searchingName = ref(false);
const nameFound = ref(false);

// Computed para verificar si el formulario está completo
const isFormValid = computed(() => {
    return form.value.co_ven.trim() &&
           form.value.name.trim() &&
           form.value.password.trim() &&
           form.value.password_confirmation.trim() &&
           form.value.password === form.value.password_confirmation &&
           nameFound.value;
});

// Función para enviar el formulario
const submitForm = () => {
    processing.value = true;
    errors.value = {};

    router.post('/users', form.value, {
        onSuccess: () => {
            // Resetear formulario y cerrar modal
            form.value = {
                name: '',
                co_ven: '',
                password: '',
                password_confirmation: '',
                rol: '0'
            };
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

// Función para buscar nombre por código de vendedor
const searchSellerName = async (coVen) => {
    if (!coVen.trim()) {
        form.value.name = '';
        nameFound.value = false;
        return;
    }

    searchingName.value = true;
    nameFound.value = false;

    try {
        const response = await apiRequest('GET', '/search-seller', { co_ven: coVen });

        if (response.ok) {
            const data = await response.json();
            form.value.name = data.nombre || '';
            nameFound.value = true;
            // Limpiar error de código si existía
            if (errors.value.co_ven) {
                delete errors.value.co_ven;
            }
        } else {
            const errorData = await response.json();
            form.value.name = '';
            nameFound.value = false;
            errors.value.co_ven = errorData.error || 'Vendedor no encontrado';
        }
    } catch (error) {
        console.error('Error buscando vendedor:', error);
        form.value.name = '';
        nameFound.value = false;
        errors.value.co_ven = 'Error al buscar vendedor';
    } finally {
        searchingName.value = false;
    }
};

// Crear función debounce para la búsqueda
const debouncedSearch = debounce(searchSellerName, 500);

// Watch para buscar automáticamente cuando cambie el código
watch(() => form.value.co_ven, (newValue) => {
    if (newValue.trim()) {
        debouncedSearch(newValue);
    } else {
        form.value.name = '';
        nameFound.value = false;
        // Limpiar errores
        if (errors.value.co_ven) {
            delete errors.value.co_ven;
        }
    }
});

// Función para resetear el formulario
const resetForm = () => {
    form.value = {
        name: '',
        co_ven: '',
        password: '',
        password_confirmation: '',
        rol: '0'
    };
    errors.value = {};
    nameFound.value = false;
    searchingName.value = false;
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button class="inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nuevo Usuario
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <form @submit.prevent="submitForm" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Crear Nuevo Usuario</DialogTitle>
                    <DialogDescription>
                        Completa la información para crear un nuevo usuario en el sistema.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Código de vendedor -->
                    <div class="grid gap-2">
                        <Label for="co_ven">Código de vendedor</Label>
                        <div class="relative">
                            <Input
                                id="co_ven"
                                v-model="form.co_ven"
                                type="text"
                                required
                                placeholder="Ej: VEN001"
                            />
                            <div v-if="searchingName" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <InputError :message="errors.co_ven" />
                        <p v-if="nameFound && form.name" class="text-sm text-green-600">
                            ✓ Vendedor encontrado: {{ form.name }}
                        </p>
                    </div>

                    <!-- Nombre (solo lectura) -->
                    <div class="grid gap-2">
                        <Label for="name">Nombre completo</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            readonly
                            :class="nameFound ? 'bg-green-50 border-green-200' : 'bg-gray-50'"
                            placeholder="Se obtendrá automáticamente del código de vendedor"
                        />
                        <InputError :message="errors.name" />
                        <p class="text-sm text-gray-500">
                            El nombre se obtiene automáticamente de la base de datos de vendedores
                        </p>
                    </div>

                    <!-- Rol -->
                    <div class="grid gap-2">
                        <Label for="rol">Rol</Label>
                        <select
                            id="rol"
                            v-model="form.rol"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="0">Vendedor</option>
                            <option value="1">Administrador</option>
                        </select>
                        <InputError :message="errors.rol" />
                    </div>

                    <!-- Contraseña -->
                    <div class="grid gap-2">
                        <Label for="password">Contraseña</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Confirmar contraseña -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar contraseña</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Repite la contraseña"
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
                            Creando...
                        </span>
                        <span v-else>Crear Usuario</span>
                    </Button>

                    <!-- Mensaje de ayuda para formulario incompleto -->
                   <!--  <p v-if="!isFormValid && !processing" class="text-sm text-gray-500 text-center">
                        Complete todos los campos para habilitar el botón de crear
                    </p> -->
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
