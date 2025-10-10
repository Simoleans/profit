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
import OrderStatusStats from '../components/OrderStatusStats.vue';
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
    por_vencer: number;
    total: number;
    codigo_vendedor: string;
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

interface StatusStat {
    total: number;
    monto: number;
    label: string;
}

interface OrderStatsData {
    stats: {
        P: StatusStat;
        A: StatusStat;
        R: StatusStat;
        F: StatusStat;
    };
    month: string;
    codigo_vendedor: string;
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
const orderStats = ref<OrderStatsData | null>(null);

const loadingClients = ref(true);
const loadingRetenciones = ref(true);
const loadingCuentasPorCobrar = ref(true);
const loadingPromotionArticles = ref(true);
const loadingOrderStats = ref(true);

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

// Función para cargar estadísticas de pedidos
const loadOrderStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/order-stats');
        orderStats.value = response.data;
    } catch (error) {
        console.error('Error cargando estadísticas de pedidos:', error);
    } finally {
        loadingOrderStats.value = false;
    }
};

// Cargar todas las estadísticas de forma secuencial
const loadAllStats = async () => {
    // Cargar en orden secuencial, una después de la otra
    await loadClientsStats();
    await loadRetencionesStats();
    await loadCuentasPorCobrarStats();
    await loadPromotionArticles();
    await loadOrderStats();
};

// Cargar todas las estadísticas al montar el componente
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

                <!-- Tarjeta de estadísticas de cuentas por cobrar -->
                <StatsCardSkeleton v-if="loadingCuentasPorCobrar" />
                <CuentasXCobrarStatsCard v-else-if="cuentasPorCobrarStats" :stats="cuentasPorCobrarStats" />
            </div>
            <!-- Sección inferior: Promociones y Estadísticas de Pedidos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Artículos en Promoción -->
                <PromotionArticlesList
                    :articles="promotionArticles"
                    :loading="loadingPromotionArticles"
                />

                <!-- Estadísticas de Pedidos por Status -->
                <OrderStatusStats
                    v-if="orderStats"
                    :stats="orderStats.stats"
                    :month="orderStats.month"
                    :loading="loadingOrderStats"
                />
                <div v-else class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <div class="animate-pulse space-y-3">
                            <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-1/2"></div>
                            <div class="h-8 bg-gray-200 dark:bg-gray-600 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
