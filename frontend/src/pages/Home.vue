<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero -->
    <section class="bg-blue-600 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Bem-vindo ao E-Commerce</h1>
        <p class="text-xl opacity-90 mb-8">Encontre os melhores produtos com os melhores preços</p>
        <router-link
          to="/products"
          class="inline-block px-8 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition"
        >
          Ver Produtos
        </router-link>
      </div>
    </section>

    <!-- Produtos em Destaque -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-8">Produtos em Destaque</h2>

        <div v-if="productsStore.isLoading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <div
          v-else-if="products.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
        >
          <router-link
            v-for="product in products"
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
                <span v-else class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                  Fora de estoque
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <div v-else class="text-center py-12">
          <p class="text-gray-500 text-lg">Nenhum produto encontrado</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-gray-100 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Pronto para começar?</h2>
        <p class="text-gray-600 mb-8 text-lg">Crie uma conta para aproveitar todos os benefícios</p>
        <router-link
          v-if="!authStore.isAuthenticated"
          to="/register"
          class="inline-block px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition"
        >
          Registre-se Agora
        </router-link>
        <p v-else class="text-gray-600">
          Você já está conectado!
          <router-link to="/products" class="text-blue-600 hover:underline"
            >Continue comprando</router-link
          >
        </p>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useProductsStore } from '@/stores/productsStore';
import { useAuthStore } from '@/stores/authStore';

const productsStore = useProductsStore();
const authStore = useAuthStore();

const products = computed(() => productsStore.products.slice(0, 8));

onMounted(async () => {
  await productsStore.fetchProducts({ per_page: 8 });
});

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};
</script>
