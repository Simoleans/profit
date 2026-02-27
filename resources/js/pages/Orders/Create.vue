<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const breadcrumbs = [
    {
        title: 'Pedidos',
        href: '/orders',
    },
    {
        title: 'Crear Pedido',
        href: '/orders/create',
    },
]

// Generar fecha actual en formato YYYY-MM-DD
const today = new Date()
const currentDate = today.getFullYear() + '-' +
    String(today.getMonth() + 1).padStart(2, '0') + '-' +
    String(today.getDate()).padStart(2, '0')

const form = useForm({
    co_cli: '',
    fec_emis: currentDate, // Fecha actual del sistema
    fec_venc: currentDate, // Fecha actual del sistema
    descrip: '',
    comentario: '',
    dir_ent: '',
    rows: []
})

const clients = ref([])
const articles = ref([])
const clientSearch = ref('')
const articleSearch = ref('')
const selectedClient = ref(null)
const loadingClients = ref(false)
const loadingArticles = ref(false)
const showClientDropdown = ref(false)
const showArticleDropdown = ref(false)

// Buscar clientes
const searchClients = async () => {
    if (!clientSearch.value.trim()) {
        clients.value = []
        return
    }

    loadingClients.value = true
    try {
        const response = await fetch(`/search-clients?q=${encodeURIComponent(clientSearch.value)}`)
        clients.value = await response.json()
    } catch (error) {
        console.error('Error searching clients:', error)
    }
    loadingClients.value = false
}

// Buscar artículos
const searchArticles = async () => {
    if (!articleSearch.value.trim()) {
        articles.value = []
        return
    }

    loadingArticles.value = true
    try {
        const response = await fetch(`/search-articles?q=${encodeURIComponent(articleSearch.value)}`)
        articles.value = await response.json()
    } catch (error) {
        console.error('Error searching articles:', error)
    }
    loadingArticles.value = false
}

// Seleccionar cliente
const selectClient = (client) => {
    selectedClient.value = client
    form.co_cli = client.co_cli
    clientSearch.value = `${client.cli_des} (${client.co_cli})`
    showClientDropdown.value = false
}

// Agregar artículo
const addArticle = (article) => {
    const existingIndex = form.rows.findIndex(row => row.co_art === article.co_art)

    // venta_minima ya viene como entero desde el modelo
    const defaultQuantity = article.venta_minima || 1

    if (existingIndex >= 0) {
        // Si ya existe, incrementar cantidad con venta_minima
        form.rows[existingIndex].total_art = Math.floor(form.rows[existingIndex].total_art) + defaultQuantity
        calculateRowTotal(existingIndex)
    } else {
        // Agregar nuevo al inicio de la lista
        // prec_vta1 ya viene con 2 decimales desde el modelo
        form.rows.unshift({
            co_art: article.co_art,
            art_des: article.art_des,
            total_art: defaultQuantity,
            prec_vta: article.prec_vta1,
            uni_venta: article.uni_venta,
            reng_neto: article.prec_vta1 * defaultQuantity
        })
    }

    articleSearch.value = ''
    showArticleDropdown.value = false
}

// Calcular total de línea
const calculateRowTotal = (index) => {
    const row = form.rows[index]
    // Asegurar que cantidad sea entero
    row.total_art = Math.floor(parseFloat(row.total_art) || 1)
    const quantity = row.total_art
    const price = parseFloat(row.prec_vta) || 0
    row.reng_neto = quantity * price
}

// Eliminar línea
const removeRow = (index) => {
    form.rows.splice(index, 1)
}

// Totales calculados (comentado porque está comentado en el template)
// const totals = computed(() => {
//     const subtotal = form.rows.reduce((sum, row) => {
//         const lineTotal = parseFloat(row.reng_neto) || 0
//         return sum + lineTotal
//     }, 0)
//     const iva = subtotal * 0.16
//     const total = subtotal + iva

//     return { subtotal, iva, total }
// })

const formatCurrency = (amount) => {
    // Verificar que amount es un número válido
    if (isNaN(amount) || amount === null || amount === undefined) {
        return '$0.00'
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const submit = () => {
    form.post('/orders')
}
</script>

<template>
    <Head title="Crear Pedido" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Crear Nuevo Pedido
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Completa la información para crear un nuevo pedido
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Información del encabezado -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Información General
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Cliente -->
                            <div class="space-y-2">
                                <Label for="client">Cliente *</Label>
                                <div class="relative">
                                    <Input
                                        v-model="clientSearch"
                                        @input="searchClients"
                                        @focus="showClientDropdown = true"
                                        placeholder="Buscar cliente..."
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.co_cli }"
                                    />
                                    <!-- Dropdown de clientes -->
                                    <div v-if="showClientDropdown && clients.length > 0"
                                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                        <div v-for="client in clients"
                                             :key="client.co_cli"
                                             @click="selectClient(client)"
                                             class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">
                                            <div class="font-medium">{{ client.cli_des }}</div>
                                            <div class="text-sm text-gray-500">{{ client.co_cli }} - {{ client.rif }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.co_cli" class="text-sm text-red-600">
                                    {{ form.errors.co_cli }}
                                </div>
                            </div>

                            <!-- Fecha de emisión (solo lectura) -->
                            <div class="space-y-2">
                                <Label for="fec_emis">Fecha de Emisión *</Label>
                                <Input
                                    id="fec_emis"
                                    type="date"
                                    v-model="form.fec_emis"
                                    disabled
                                    class="bg-gray-100 cursor-not-allowed"
                                />
                                <p class="text-xs text-gray-500">La fecha de emisión se establece automáticamente</p>
                            </div>

                            <!-- Descripción -->
                            <div class="space-y-2">
                                <Label for="descrip">Descripción *</Label>
                                <Input
                                    id="descrip"
                                    v-model="form.descrip"
                                    placeholder="Descripción del pedido (máx. 60 caracteres)"
                                    maxlength="60"
                                    :class="{ 'border-red-500': form.errors.descrip }"
                                />
                                <div v-if="form.errors.descrip" class="text-sm text-red-600">
                                    {{ form.errors.descrip }}
                                </div>
                            </div>

                            <!-- Dirección de Entrega -->
                            <!-- <div class="space-y-2">
                                <Label for="dir_ent">Dirección de Entrega</Label>
                                <textarea
                                    id="dir_ent"
                                    v-model="form.dir_ent"
                                    placeholder="Dirección donde se entregará el pedido"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="{ 'border-red-500': form.errors.dir_ent }"
                                />
                                <div v-if="form.errors.dir_ent" class="text-sm text-red-600">
                                    {{ form.errors.dir_ent }}
                                </div>
                            </div> -->
                        </div>

                        <!-- Comentarios -->
                        <div class="mt-4 space-y-2">
                            <Label for="comentario">Comentarios</Label>
                            <textarea
                                id="comentario"
                                v-model="form.comentario"
                                placeholder="Comentarios adicionales..."
                                rows="3"
                                class="block w-full rounded-md border-0 py-2 placeholder:p-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Líneas del pedido -->
                <div class="rounded-lg bg-white shadow dark:bg-gray-800 overflow-visible">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="mb-6">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Artículos del Pedido
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Agrega los artículos que componen el pedido
                                </p>
                            </div>

                            <!-- Buscar artículos - Campo de entrada con dropdown -->
                            <div class="relative w-full">
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <Input
                                        v-model="articleSearch"
                                        @input="searchArticles"
                                        @focus="showArticleDropdown = true"
                                        placeholder="🔍 Buscar artículo por código o descripción..."
                                        class="w-full pl-12 pr-4 py-4 text-lg font-medium border-2 border-blue-300 rounded-xl shadow-lg focus:border-blue-500 focus:ring-4 focus:ring-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-600 dark:focus:border-blue-400 dark:focus:ring-blue-800/30 transition-all duration-200"
                                    />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                        <div class="bg-blue-100 dark:bg-blue-800 px-3 py-1 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-300">
                                            Agregar
                                        </div>
                                    </div>
                                </div>

                                <!-- Dropdown de artículos - Arriba del campo -->
                                <div v-if="showArticleDropdown && articles.length > 0"
                                     class="absolute z-[20000] left-0 right-0 bottom-full mb-2 bg-white dark:bg-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-xl shadow-2xl max-h-80 overflow-auto">
                                    <div class="p-2">
                                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                            {{ articles.length }} artículo{{ articles.length !== 1 ? 's' : '' }} encontrado{{ articles.length !== 1 ? 's' : '' }}
                                        </div>
                                        <div v-for="article in articles"
                                             :key="article.co_art"
                                             @click="addArticle(article)"
                                             class="p-4 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0 rounded-lg mx-1 my-1 transition-colors duration-150">
                                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-semibold text-gray-900 dark:text-white text-base">
                                                        {{ article.art_des }}
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                            {{ article.co_art }}
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300">
                                                            Stock: {{ Math.floor(article.stock_act) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 shadow-sm">
                                                        {{ formatCurrency(article.prec_vta1) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.rows.length === 0" class="text-center py-8 text-gray-500">
                            No hay artículos agregados. Busca artículos arriba para agregarlos.
                        </div>

                        <div v-else class="space-y-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Artículo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Cantidad
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Precio Unit.
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Unidad
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Total
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="(row, index) in form.rows" :key="row.co_art">
                                            <td class="px-6 py-4">
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ row.art_des }}</div>
                                                    <div class="text-sm text-gray-500">{{ row.co_art }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                    v-model.number="row.total_art"
                                                    @input="calculateRowTotal(index)"
                                                    class="w-24"
                                                />
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="w-32 px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md">
                                                    {{ Number(row.prec_vta).toFixed(2) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <Input
                                                    v-model="row.uni_venta"
                                                    class="w-20 bg-gray-100"
                                                    readonly
                                                />
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                {{ formatCurrency(row.reng_neto) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <button
                                                    type="button"
                                                    @click="removeRow(index)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totales -->
                           <!--  <div class="flex justify-end">
                                <div class="w-64 space-y-2 border-t pt-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-900">Subtotal:</span>
                                        <span class="font-medium text-gray-900">{{ formatCurrency(totals.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-900">IVA (16%):</span>
                                        <span class="font-medium text-gray-900">{{ formatCurrency(totals.iva) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2">
                                        <span class="font-bold text-gray-900">Total:</span>
                                        <span class="font-bold text-lg text-gray-900">{{ formatCurrency(totals.total) }}</span>
                                    </div>
                                </div>
                            </div> -->
                        </div>

                        <div v-if="form.errors.rows" class="text-sm text-red-600 mt-2">
                            {{ form.errors.rows }}
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-4">
                    <Link
                        href="/orders"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || form.rows.length === 0"
                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Guardando...' : 'Crear Pedido' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
