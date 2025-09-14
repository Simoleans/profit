<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

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
    client: {
        type: Object,
        required: true
    }
});

// Estado del formulario
const form = ref({
    cli_des: '',
    rif: '',
    direc1: '',
    telefonos: '',
    respons: '',
    email: '',
    ciudad: '',
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

// Computed para verificar si hubo cambios
const hasChanges = computed(() => {
    return form.value.cli_des !== props.client.cli_des ||
           form.value.rif !== props.client.rif ||
           form.value.direc1 !== props.client.direc1 ||
           form.value.telefonos !== props.client.telefonos ||
           form.value.respons !== props.client.respons ||
           form.value.email !== props.client.email ||
           form.value.ciudad !== props.client.ciudad;
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
    if (!hasChanges.value) return 'Sin cambios';
    if (isFormValid.value) return 'Actualizar Cliente';
    if (missingFields.value.length === 1) {
        return `Falta: ${missingFields.value[0]}`;
    }
    return `Faltan ${missingFields.value.length} campos`;
});

// Función para cargar datos del cliente en el formulario
const loadClientData = () => {
    form.value = {
        cli_des: props.client.cli_des || '',
        rif: props.client.rif || '',
        direc1: props.client.direc1 || '',
        telefonos: props.client.telefonos || '',
        respons: props.client.respons || '',
        email: props.client.email || '',
        ciudad: props.client.ciudad || '',
    };
};

// Función para enviar el formulario
const submitForm = () => {
    processing.value = true;
    errors.value = {};

    console.log('Cliente completo:', props.client);
    console.log('client.rif:', props.client.rif);

    const rifEncoded = encodeURIComponent(props.client.rif.trim());
    router.put(`/clients/${rifEncoded}`, form.value, {
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

// Función para resetear el formulario
const resetForm = () => {
    loadClientData();
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

// Watch para cargar datos cuando cambie el cliente
watch(() => props.client, () => {
    loadClientData();
}, { immediate: true });

// Watch para cargar datos cuando se abra el modal
watch(isOpen, (newValue) => {
    if (newValue) {
        loadClientData();
    }
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="inline-flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
            <form @submit.prevent="submitForm" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Cliente</DialogTitle>
                    <DialogDescription>
                        Modifica la información del cliente {{ client.cli_des }}.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Código de cliente (solo lectura) -->
                    <div class="grid gap-2">
                        <Label for="co_cli">Código de Cliente</Label>
                        <Input
                            id="co_cli"
                            :value="client.co_cli"
                            type="text"
                            readonly
                            class="bg-gray-50 border-gray-200"
                        />
                        <p class="text-sm text-gray-500">
                            El código de cliente no se puede modificar
                        </p>
                    </div>

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
                        :disabled="!isFormValid || !hasChanges || processing"
                        :class="[
                            'min-w-36 transition-all duration-200',
                            (isFormValid && hasChanges) ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400'
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
