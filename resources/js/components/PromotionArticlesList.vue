<script setup lang="ts">
import { ShoppingBag, Package, DollarSign } from 'lucide-vue-next';

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

interface Props {
    articles: Article[];
    loading?: boolean;
}

defineProps<Props>();

const formatPrice = (price: number) => {
    if (!price || price === 0) return 'N/A';
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-800">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 dark:from-orange-950/20 dark:to-amber-950/20 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-orange-500/10 rounded-lg dark:bg-orange-500/20">
                    <ShoppingBag class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Artículos en Promoción
                    </h3>
                   <!--  <p class="text-sm text-gray-600 dark:text-gray-400">
                        Top 10 productos promocionales con stock disponible
                    </p> -->
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-6">
            <div class="space-y-3">
                <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles List -->
        <div v-else-if="articles.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="article in articles" :key="article.co_art"
                 class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <!-- Left: Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-900 dark:text-orange-300">
                                PROMOCIÓN
                            </span>
                            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                {{ article.co_art }}
                            </span>
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ article.art_des }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span v-if="article.line" class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                {{ article.line.lin_des }}
                            </span>
                           <!--  <span class="inline-flex items-center gap-1 text-xs"
                                  :class="article.stock_act > 0
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-red-600 dark:text-red-400'">
                                <Package class="w-3 h-3" />
                                Stock: {{ Math.floor(article.stock_act) }}
                            </span> -->
                        </div>
                    </div>

                    <!-- Right: Price -->
                    <div class="text-right shrink-0">
                        <div class="inline-flex items-center gap-1 rounded-lg bg-green-100 px-3 py-1.5 text-sm font-bold text-green-800 shadow-sm dark:bg-green-900 dark:text-green-300">
                            <DollarSign class="w-4 h-4" />
                            {{ formatPrice(article.prec_vta1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
            <ShoppingBag class="w-12 h-12 mx-auto text-gray-400 mb-3" />
            <p class="text-gray-500 dark:text-gray-400">
                No hay artículos en promoción con stock disponible
            </p>
        </div>
    </div>
</template>

