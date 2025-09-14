<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    cli_des: '',        // Nombre/Razón Social
    rif: '',            // RIF
    direc1: '',         // Dirección
    telefonos: '',      // Teléfonos
    respons: '',        // Responsable
    email: '',          // Email
    ciudad: '',         // Ciudad
    // co_ven se asigna automáticamente del usuario logueado
    // co_cli se deja vacío para autoincremento
});

const errors = ref({});
const processing = ref(false);
const isOpen = ref(false);

// Función para validar formato de RIF/Cédula
const isValidRifFormat = computed(() => {
    const rif = form.value.rif.trim();
    if (!rif) return false;

    // Debe tener formato V-números o J-números
    const rifPattern = /^[VJ]-[0-9]+$/;
    return rifPattern.test(rif);
});

// Función para validar email
const isValidEmail = computed(() => {
    const email = form.value.email.trim();
    if (!email) return false;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
});

// Computed para verificar si el formulario está completo y válido
const isFormValid = computed(() => {
    return form.value.cli_des.trim() &&
           isValidRifFormat.value &&
           form.value.direc1.trim() &&
           form.value.telefonos.trim() &&
           form.value.respons.trim() &&
           isValidEmail.value &&
           form.value.ciudad.trim();
});

// Computed para mostrar qué campos faltan
const missingFields = computed(() => {
    const missing = [];
    if (!form.value.cli_des.trim()) missing.push('Nombre/Razón Social');
    if (!isValidRifFormat.value) missing.push('RIF/Cédula válido');
    if (!form.value.direc1.trim()) missing.push('Dirección');
    if (!form.value.telefonos.trim()) missing.push('Teléfonos');
    if (!form.value.respons.trim()) missing.push('Responsable');
    if (!isValidEmail.value) missing.push('Email válido');
    if (!form.value.ciudad.trim()) missing.push('Ciudad');
    return missing;
});

// Texto del botón
const buttonText = computed(() => {
    if (processing.value) return 'Guardando...';
    if (isFormValid.value) return 'Registrar Cliente';
    if (missingFields.value.length === 1) {
        return `Falta: ${missingFields.value[0]}`;
    }
    return `Faltan ${missingFields.value.length} campos`;
});

// Función para enviar el formulario
const submitForm = () => {
    processing.value = true;
    errors.value = {};

    router.post('/clients', form.value, {
        onSuccess: () => {
            // Resetear formulario y cerrar modal
            resetForm();
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

// Función para resetear el formulario
const resetForm = () => {
    form.value = {
        cli_des: '',
        rif: '',
        direc1: '',
        telefonos: '',
        respons: '',
        email: '',
        ciudad: '',
    };
    errors.value = {};
};

// Función para formatear RIF/Cédula
const formatRif = () => {
    let rif = form.value.rif.replace(/[^0-9VJvj-]/g, '').toUpperCase();

    // Si no empieza con V o J, añadir J por defecto
    if (rif.length > 0 && !['V', 'J'].includes(rif[0])) {
        rif = 'J' + rif;
    }

    // Limitar longitud máxima (V-12345678 o J-12345678)
    if (rif.length > 10) {
        rif = rif.substring(0, 10);
    }

    // Agregar guión después de la letra si no existe
    if (rif.length > 1 && rif[1] !== '-') {
        rif = rif[0] + '-' + rif.substring(1);
    }

    form.value.rif = rif;
};

// Función para formatear email
const formatEmail = () => {
    form.value.email = form.value.email.toLowerCase().trim();
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button class="inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nuevo Cliente
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
            <form @submit.prevent="submitForm" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Registrar Nuevo Cliente</DialogTitle>
                    <DialogDescription>
                        Completa la información para registrar un nuevo cliente en el sistema.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Nombre/Razón Social -->
                    <div class="grid gap-2">
                        <Label for="cli_des">Nombre / Razón Social *</Label>
                        <Input
                            id="cli_des"
                            v-model="form.cli_des"
                            type="text"
                            required
                            placeholder="Ingresa el nombre o razón social"
                            maxlength="60"
                        />
                        <InputError :message="errors.cli_des" />
                    </div>

                    <!-- RIF/Cédula -->
                    <div class="grid gap-2">
                        <Label for="rif">RIF / Cédula *</Label>
                        <Input
                            id="rif"
                            v-model="form.rif"
                            type="text"
                            required
                            placeholder="J-12345678 o V-12345678"
                            maxlength="10"
                            :class="form.rif.trim() ? (isValidRifFormat ? 'border-green-500 focus:border-green-500' : 'border-red-500 focus:border-red-500') : ''"
                            @input="formatRif"
                        />
                        <InputError :message="errors.rif" />
                        <div v-if="form.rif.trim() && !isValidRifFormat" class="text-sm text-red-500">
                            ❌ Formato inválido. Debe ser V-12345678 o J-12345678
                        </div>
                        <div v-else-if="form.rif.trim() && isValidRifFormat" class="text-sm text-green-600">
                            ✅ Formato válido
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            Formato: J-12345678 (RIF) o V-12345678 (Cédula)
                        </p>
                    </div>

                    <!-- Dirección -->
                    <div class="grid gap-2">
                        <Label for="direc1">Dirección *</Label>
                        <Input
                            id="direc1"
                            v-model="form.direc1"
                            type="text"
                            required
                            placeholder="Ingresa la dirección completa"
                            maxlength="120"
                        />
                        <InputError :message="errors.direc1" />
                    </div>

                    <!-- Teléfonos -->
                    <div class="grid gap-2">
                        <Label for="telefonos">Teléfonos *</Label>
                        <Input
                            id="telefonos"
                            v-model="form.telefonos"
                            type="text"
                            required
                            placeholder="0414-1234567, 0212-1234567"
                            maxlength="30"
                        />
                        <InputError :message="errors.telefonos" />
                        <p class="text-sm text-gray-500">
                            Puedes incluir varios números separados por comas
                        </p>
                    </div>

                    <!-- Responsable -->
                    <div class="grid gap-2">
                        <Label for="respons">Responsable / Contacto *</Label>
                        <Input
                            id="respons"
                            v-model="form.respons"
                            type="text"
                            required
                            placeholder="Nombre del responsable o contacto principal"
                            maxlength="60"
                        />
                        <InputError :message="errors.respons" />
                    </div>

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email">Email *</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="cliente@ejemplo.com"
                            maxlength="120"
                            :class="form.email.trim() ? (isValidEmail ? 'border-green-500 focus:border-green-500' : 'border-red-500 focus:border-red-500') : ''"
                            @blur="formatEmail"
                        />
                        <InputError :message="errors.email" />
                        <div v-if="form.email.trim() && !isValidEmail" class="text-sm text-red-500">
                            ❌ Email inválido
                        </div>
                        <div v-else-if="form.email.trim() && isValidEmail" class="text-sm text-green-600">
                            ✅ Email válido
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div class="grid gap-2">
                        <Label for="ciudad">Ciudad *</Label>
                        <Input
                            id="ciudad"
                            v-model="form.ciudad"
                            type="text"
                            required
                            placeholder="Ciudad donde se encuentra el cliente"
                            maxlength="30"
                        />
                        <InputError :message="errors.ciudad" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" @click="resetForm">
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="!isFormValid || processing"
                        :class="[
                            'min-w-36 transition-all duration-200',
                            isFormValid ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400'
                        ]"
                    >
                        <span v-if="processing" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                        <span v-else>{{ buttonText }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
