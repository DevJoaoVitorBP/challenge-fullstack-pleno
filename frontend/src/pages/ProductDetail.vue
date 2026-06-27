<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Back Button -->
      <router-link
        to="/products"
        class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-8"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        Voltar aos Produtos
      </router-link>

      <!-- Loading State -->
      <div v-if="productsStore.isLoading" class="flex justify-center py-16">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="productsStore.error"
        class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8"
      >
        <p class="text-red-600">{{ productsStore.error }}</p>
      </div>

      <!-- Product Details -->
      <div v-else-if="product" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
          <!-- Product Image -->
          <div class="flex items-center justify-center bg-gray-100 rounded-lg min-h-96">
            <svg
              class="w-32 h-32 text-gray-400"
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

          <!-- Product Info -->
          <div class="flex flex-col justify-between">
            <div>
              <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>

              <!-- Price -->
              <div class="mb-6">
                <p class="text-gray-600 text-sm mb-2">Preço</p>
                <p class="text-4xl font-bold text-blue-600">R$ {{ formatPrice(product.price) }}</p>
              </div>

              <!-- Stock Status -->
              <div class="mb-6">
                <span
                  v-if="product.quantity > 0"
                  class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold"
                >
                  ✓ Em Estoque ({{ product.quantity }} unidades)
                </span>
                <span
                  v-else
                  class="inline-block bg-red-100 text-red-800 px-4 py-2 rounded-lg font-semibold"
                >
                  ✗ Fora de Estoque
                </span>
              </div>

              <!-- Description -->
              <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Descrição</h2>
                <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
              </div>

              <!-- Category -->
              <div v-if="product.category" class="mb-6">
                <p class="text-gray-600 text-sm mb-2">Categoria</p>
                <router-link
                  :to="`/products?category_id=${product.category.id}`"
                  class="text-blue-600 hover:text-blue-800 font-medium"
                >
                  {{ product.category.name }}
                </router-link>
              </div>

              <!-- Tags -->
              <div v-if="product.tags && product.tags.length > 0" class="mb-6">
                <p class="text-gray-600 text-sm mb-2">Tags</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in product.tags"
                    :key="tag.id"
                    class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm"
                  >
                    {{ tag.name }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Add to Cart Form -->
            <div v-if="product.quantity > 0" class="flex gap-4">
              <input
                v-model.number="quantity"
                type="number"
                min="1"
                :max="product.quantity"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                @click="addToCart"
                :disabled="isAddingToCart"
                class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
              >
                {{ isAddingToCart ? 'Adicionando...' : 'Adicionar ao Carrinho' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found -->
      <div v-else class="text-center py-12">
        <p class="text-gray-500 text-lg">Produto não encontrado</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useProductsStore } from '@/stores/productsStore';
import { useCartStore } from '@/stores/cartStore';

const route = useRoute();
const productsStore = useProductsStore();
const cartStore = useCartStore();

const quantity = ref(1);
const isAddingToCart = ref(false);

const product = computed(() => productsStore.currentProduct);

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const addToCart = async () => {
  if (!product.value) return;

  isAddingToCart.value = true;
  try {
    await cartStore.addItem(product.value.id, quantity.value);
    quantity.value = 1;
    // Vou deixar comentado por enquanto, pois não quero redirecionar para o carrinho imediatamente
    // await router.push('/cart')
  } catch (error) {
    console.error('Error adding to cart:', error);
  } finally {
    isAddingToCart.value = false;
  }
};

onMounted(async () => {
  const id = Number(route.params.id);
  if (id) {
    await productsStore.fetchProductById(id);
  }
});
</script>
