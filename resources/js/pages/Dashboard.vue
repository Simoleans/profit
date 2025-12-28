<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ClientStatsCard from '../components/ClientStatsCard.vue';
import RetencionesStatsCard from '../components/RetencionesStatsCard.vue';
import CuentasXCobrarStatsCard from '../components/CuentasXCobrarStatsCard.vue';
import StatsCardSkeleton from '../components/StatsCardSkeleton.vue';
import PromotionArticlesList from '../components/PromotionArticlesList.vue';
import ClientesInactivosList from '../components/ClientesInactivosList.vue';
import axios from 'axios';

interface ClientStats {
    total: number;
    new_this_month: number;
    is_admin: boolean;
}

interface RetencionesStats {
    total: number;
    codigo_vendedor: string;
}

interface CuentasXCobrarStats {
    vencidas: number;
    saldo_vencido: number;
    por_vencer: number;
    saldo_por_vencer: number;
    total: number;
    saldo_total: number;
    codigo_vendedor: string;
    is_admin: boolean;
}

interface Article {
    co_art: string;
    art_des: string;
    prec_vta1: number;
    stock_act: number;
    uni_venta: string;
    line?: {
        lin_des: string;
    };
}

interface ClienteInactivo {
    co_cli: string;
    cli_des: string;
    ult_fec_fac: string;
    meses_ult_fac: number;
    prom_vta_mens: number;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const clientsStats = ref<ClientStats | null>(null);
const retencionesStats = ref<RetencionesStats | null>(null);
const cuentasPorCobrarStats = ref<CuentasXCobrarStats | null>(null);
const promotionArticles = ref<Article[]>([]);
const clientesInactivos = ref<ClienteInactivo[]>([]);
const clientesInactivosPagination = ref<PaginationData | undefined>(undefined);
// const orderStats = ref<OrderStatsData | null>(null);
// const clientesSinPedidos = ref<ClientesSinPedidosData | null>(null);

const loadingClients = ref(true);
const loadingRetenciones = ref(true);
const loadingCuentasPorCobrar = ref(true);
const loadingPromotionArticles = ref(true);
const loadingClientesInactivos = ref(true);
// const loadingOrderStats = ref(true);
// const loadingClientesSinPedidos = ref(true);

// Función para cargar estadísticas de clientes
const loadClientsStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats/clients');
        clientsStats.value = response.data;
    } catch (error) {
        console.error('Error cargando estadísticas de clientes:', error);
    } finally {
        loadingClients.value = false;
    }
};

const loadRetencionesStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats/retenciones');
        retencionesStats.value = response.data;
    } catch (error) {
        console.error('Error cargando estadísticas de retenciones:', error);
    } finally {
        loadingRetenciones.value = false;
    }
};

const loadCuentasPorCobrarStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats/cuentas-por-cobrar');
        cuentasPorCobrarStats.value = response.data;
    } catch (error) {
        console.error('Error cargando estadísticas de cuentas por cobrar:', error);
    } finally {
        loadingCuentasPorCobrar.value = false;
    }
};

// Función para cargar artículos en promoción
const loadPromotionArticles = async () => {
    try {
        const response = await axios.get('/api/dashboard/promotion-articles');
        promotionArticles.value = response.data;
    } catch (error) {
        console.error('Error cargando artículos en promoción:', error);
    } finally {
        loadingPromotionArticles.value = false;
    }
};

// Función para cargar clientes inactivos
const loadClientesInactivos = async (page: number = 1) => {
    try {
        loadingClientesInactivos.value = true;
        const response = await axios.get('/api/dashboard/clientes-inactivos', {
            params: {
                page,
                per_page: 10
            }
        });
        clientesInactivos.value = response.data.data;
        clientesInactivosPagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
        };
    } catch (error) {
        console.error('Error cargando clientes inactivos:', error);
    } finally {
        loadingClientesInactivos.value = false;
    }
};

// Manejar cambio de página en clientes inactivos
const handleClientesInactivosPageChange = (page: number) => {
    loadClientesInactivos(page);
};



// Cargar todas las estadísticas de forma secuencial
const loadAllStats = async () => {
    await loadClientsStats();
    await loadRetencionesStats();
    await loadCuentasPorCobrarStats();
    await loadPromotionArticles();
    await loadClientesInactivos();

};

onMounted(() => {
    loadAllStats();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                <!-- Tarjeta de estadísticas de clientes -->
                <StatsCardSkeleton v-if="loadingClients" />
                <ClientStatsCard v-else-if="clientsStats" :stats="clientsStats" />

                <!-- Tarjeta de estadísticas de retenciones -->
                <StatsCardSkeleton v-if="loadingRetenciones" />
                <RetencionesStatsCard v-else-if="retencionesStats" :stats="retencionesStats" />

                <!-- Tarjeta de Cuentas por Cobrar - Reemplaza a ClientesSinPedidos -->
                <CuentasXCobrarStatsCard
                    v-if="cuentasPorCobrarStats"
                    :stats="cuentasPorCobrarStats"
                    :loading="loadingCuentasPorCobrar"
                />
            </div>

            <!-- Tarjeta de clientes sin pedidos -->
            <!-- <ClientesSinPedidosList
                :clientes="clientesSinPedidos?.clientes || []"
                :total="clientesSinPedidos?.total || 0"
                :loading="loadingClientesSinPedidos"
            /> -->

            <!-- Grid de 2 columnas: Artículos en Promoción y Clientes Inactivos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Artículos en Promoción -->
                <PromotionArticlesList
                    :articles="promotionArticles"
                    :loading="loadingPromotionArticles"
                />

                <!-- Clientes Inactivos -->
                <ClientesInactivosList
                    :clientes="clientesInactivos"
                    :pagination="clientesInactivosPagination"
                    :loading="loadingClientesInactivos"
                    @change-page="handleClientesInactivosPageChange"
                />
            </div>

            <!-- Estadísticas de Pedidos por Status -->
            <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <OrderStatusStats
                    v-if="orderStats"
                    :stats="orderStats.stats"
                    :month="orderStats.month"
                    :loading="loadingOrderStats"
                />
            </div> -->
        </div>
    </AppLayout>
</template>
