<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold mb-8">Produtos</h1>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Filtros (Sidebar) -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="font-bold text-lg mb-4">Filtros</h2>

            <!-- Search -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Nome do produto"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              />
            </div>

            <!-- Categoria -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
              <select
                v-model="filters.category_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              >
                <option value="">Todas</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Preço -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Preço Mínimo</label>
              <input
                v-model.number="filters.min_price"
                type="number"
                placeholder="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              />
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Preço Máximo</label>
              <input
                v-model.number="filters.max_price"
                type="number"
                placeholder="999999"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              />
            </div>

            <button
              @click="applyFilters"
              class="w-full py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition"
            >
              Aplicar Filtros
            </button>
          </div>
        </div>

        <!-- Produtos -->
        <div class="lg:col-span-3">
          <div v-if="productsStore.isLoading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          </div>

          <div
            v-else-if="productsStore.products.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <router-link
              v-for="product in productsStore.products"
              :key="product.id"
              :to="`/products/${product.id}`"
              class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden"
            >
              <div class="h-48 bg-gray-200 flex items-center justify-center">
                <svg
                  class="w-16 h-16 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div class="p-4">
                <h3 class="font-bold text-lg mb-2 text-gray-800">{{ product.name }}</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ product.description }}</p>
                <div class="flex justify-between items-center">
                  <span class="text-2xl font-bold text-blue-600"
                    >R$ {{ formatPrice(product.price) }}</span
                  >
                  <span
                    v-if="product.quantity > 0"
                    class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded"
                  >
                    Em estoque
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <div v-else class="text-center py-12">
            <p class="text-gray-500 text-lg">Nenhum produto encontrado</p>
          </div>

          <!-- Paginação -->
          <div v-if="productsStore.products.length > 0" class="flex justify-center gap-4 mt-8">
            <button
              :disabled="!productsStore.hasPrevPage"
              @click="previousPage"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50 transition"
            >
              Anterior
            </button>
            <span class="px-4 py-2 text-gray-700">
              Página {{ productsStore.pagination?.current_page ?? 1 }} de
              {{ productsStore.pagination?.last_page ?? 1 }}
            </span>
            <button
              :disabled="!productsStore.hasNextPage"
              @click="nextPage"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50 transition"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, onMounted } from 'vue';
import { useProductsStore } from '@/stores/productsStore';

const productsStore = useProductsStore();

const filters = reactive({
  search: '',
  category_id: '' as string | number,
  min_price: undefined as number | undefined,
  max_price: undefined as number | undefined,
  per_page: 12,
  page: 1,
});

const categories = computed(() => productsStore.categories);

const applyFilters = async () => {
  filters.page = 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

const nextPage = async () => {
  filters.page = productsStore.pagination.current_page + 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

const previousPage = async () => {
  filters.page = productsStore.pagination.current_page - 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

onMounted(async () => {
  await productsStore.fetchCategories();
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
});

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};
</script>
