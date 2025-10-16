<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Props {
    total: number;
    codigo_vendedor: string;
}

const props = defineProps<Props>();

const goToClientesSinPedidos = () => {
    router.visit('/clientes-sin-pedidos');
};
</script>

<template>
    <Card class="cursor-pointer hover:shadow-lg transition-shadow" @click="goToClientesSinPedidos">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Clientes Sin Pedidos</CardTitle>
            <Users class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
            <div class="text-2xl font-bold text-orange-600">{{ total }}</div>
            <p class="text-xs text-muted-foreground mt-1">
                Clientes activos sin pedidos
            </p>
            <p class="text-xs text-blue-500 mt-2 hover:underline">
                Click para ver detalles →
            </p>
        </CardContent>
    </Card>
</template>

