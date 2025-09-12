<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CreateUser from '@/components/CreateUser.vue';
import EditUser from '@/components/EditUser.vue';
import { ref, watch } from 'vue';

// Props recibidas del controlador
const props = defineProps({
    users: Object,
    search: String,
});

// Estado para la búsqueda
const searchQuery = ref(props.search || '');

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Usuarios',
        href: '/users',
    },
];

// Función para realizar búsqueda
const performSearch = () => {
    router.get('/users', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

// Watch para búsqueda en tiempo real (con debounce)
let searchTimeout = null;
watch(searchQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
});

// Función para obtener clase del badge según rol
const getRoleBadgeClass = (rol) => {
    return rol === '0'
        ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
};

// Función para obtener texto del rol
const getRoleText = (rol) => {
    return rol === '0' ? 'Vendedor' : 'Admin';
};

// Función para ir a una página específica
const goToPage = (url) => {
    if (url) {
        router.visit(url);
    }
};
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con título y botón de agregar -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Usuarios
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Administra los usuarios del sistema y sus permisos
                    </p>
                </div>
                <CreateUser />
            </div>

            <!-- Card contenedor de la tabla -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <!-- Header del card con búsqueda -->
                <div class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Lista de Usuarios
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ users.total }} usuario{{ users.total !== 1 ? 's' : '' }} total{{ users.total !== 1 ? 'es' : '' }}
                            </p>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="relative w-full sm:w-72">
                            <Label for="search" class="sr-only">Buscar usuarios</Label>
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
                                placeholder="Buscar por nombre o código..."
                            />
                        </div>
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
                                    Nombre
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:table-cell">
                                    Rol
                                </th>
                                <th scope="col" class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 md:table-cell">
                                    Creado
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <!-- Estado vacío -->
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No se encontraron usuarios con ese criterio' : 'No hay usuarios registrados' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de usuarios -->
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ user.co_ven }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ user.name }}</span>
                                        <!-- En móvil, mostrar el rol debajo del nombre -->
                                        <div class="mt-1 sm:hidden">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getRoleBadgeClass(user.rol)">
                                                {{ getRoleText(user.rol) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 sm:table-cell">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getRoleBadgeClass(user.rol)">
                                        {{ getRoleText(user.rol) }}
                                    </span>
                                </td>
                                <td class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 md:table-cell">
                                    {{ new Date(user.created_at).toLocaleDateString('es-ES') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <EditUser :user="user" />
                                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="sr-only">Eliminar usuario</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="users.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando {{ users.from || 0 }} a {{ users.to || 0 }} de {{ users.total }} resultados
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="page in users.links"
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
