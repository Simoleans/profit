<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Users } from 'lucide-vue-next';

interface Cliente {
    co_cli: string;
    cli_des: string;
    fecha_reg: string;
    co_ven: string;
    ven_des: string;
    zon_des: string;
}

interface Props {
    clientes: Cliente[];
    total: number;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false
});

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Users class="h-5 w-5 text-orange-500" />
                    <CardTitle class="text-lg">Clientes Sin Pedidos</CardTitle>
                </div>
                <Badge variant="secondary">{{ total }} clientes</Badge>
            </div>
            <CardDescription>Clientes activos que aún no han realizado pedidos</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="animate-pulse space-y-3">
                <div v-for="i in 5" :key="i" class="h-16 bg-gray-200 dark:bg-gray-600 rounded"></div>
            </div>
            <div v-else class="max-h-[400px] overflow-y-auto pr-2">
                <div v-if="clientes.length === 0" class="flex items-center justify-center h-32 text-muted-foreground">
                    <p>No hay clientes sin pedidos</p>
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="cliente in clientes"
                        :key="cliente.co_cli"
                        class="flex flex-col gap-1 rounded-lg border border-sidebar-border/70 bg-gray-50 dark:bg-gray-800/50 p-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                    {{ cliente.cli_des }}
                                </h4>
                                <p class="text-xs text-muted-foreground">
                                    Código: {{ cliente.co_cli }}
                                </p>
                            </div>
                            <Badge variant="outline" class="ml-2 text-xs">
                                {{ cliente.zon_des }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between text-xs text-muted-foreground mt-1">
                            <span>Vendedor: {{ cliente.ven_des }}</span>
                            <span>Reg: {{ formatDate(cliente.fecha_reg) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

