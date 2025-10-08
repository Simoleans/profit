<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
// CÓDIGO ORIGINAL COMENTADO - Importación de todos los iconos
// import { Monitor, Moon, Sun } from 'lucide-vue-next';
// NUEVO CÓDIGO - Solo icono de sol para tema claro
import { Sun } from 'lucide-vue-next';

const { appearance } = useAppearance();

// CÓDIGO ORIGINAL COMENTADO - Múltiples opciones de tema
// const tabs = [
//     { value: 'light', Icon: Sun, label: 'Light' },
//     { value: 'dark', Icon: Moon, label: 'Dark' },
//     { value: 'system', Icon: Monitor, label: 'System' },
// ] as const;

// NUEVO CÓDIGO - Solo mostrar tema claro (forzado)
const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
] as const;
</script>

<template>
    <!-- CÓDIGO ORIGINAL COMENTADO - Botones interactivos para cambiar tema -->
    <!-- <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div> -->

    <!-- NUEVO CÓDIGO - Solo mostrar tema claro (forzado, sin interacción) -->
    <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            disabled
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors cursor-default',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
