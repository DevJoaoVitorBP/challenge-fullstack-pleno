<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-4xl font-bold mb-12">Carrinho de Compras</h1>

      <!-- Loading State -->
      <div v-if="cartStore.isLoading" class="flex justify-center py-16">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>

      <!-- Empty Cart -->
      <div
        v-else-if="cartStore.items.length === 0"
        class="text-center py-16 bg-white rounded-lg shadow"
      >
        <svg
          class="w-24 h-24 mx-auto text-gray-400 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
          />
        </svg>
        <p class="text-gray-500 text-lg mb-8">Seu carrinho está vazio</p>
        <router-link
          to="/products"
          class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700"
        >
          Voltar às Compras
        </router-link>
      </div>

      <!-- Cart Items -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Items List -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div
              v-for="item in cartStore.items"
              :key="item.id"
              class="border-b last:border-b-0 p-6 hover:bg-gray-50 transition"
            >
              <div class="flex gap-6">
                <!-- Product Image -->
                <div
                  class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center"
                >
                  <svg
                    class="w-12 h-12 text-gray-400"
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
                <div class="flex-1">
                  <router-link
                    :to="`/products/${item.product_id}`"
                    class="text-lg font-semibold text-blue-600 hover:text-blue-800"
                  >
                    {{ item.product?.name || 'Produto' }}
                  </router-link>
                  <p class="text-gray-600 text-sm mt-1 line-clamp-2">
                    {{ item.product?.description }}
                  </p>
                  <p class="text-2xl font-bold text-blue-600 mt-3">
                    R$ {{ formatPrice((item.product?.price || 0) * item.quantity) }}
                  </p>
                  <p class="text-gray-600 text-sm">
                    Unitário: R$ {{ formatPrice(item.product?.price || 0) }}
                  </p>
                </div>

                <!-- Quantity & Actions -->
                <div class="flex flex-col items-end justify-between">
                  <button
                    @click="removeItem(item.id)"
                    class="text-red-600 hover:text-red-800 font-semibold transition"
                  >
                    ✕ Remover
                  </button>

                  <div class="flex items-center gap-3">
                    <button
                      @click="decrementQuantity(item)"
                      :disabled="item.quantity <= 1"
                      class="px-3 py-1 bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 rounded transition"
                    >
                      −
                    </button>
                    <input
                      :value="item.quantity"
                      @change="
                        (e) => updateQuantity(item, Number((e.target as HTMLInputElement).value))
                      "
                      type="number"
                      min="1"
                      class="w-12 text-center border border-gray-300 rounded px-2 py-1"
                    />
                    <button
                      @click="incrementQuantity(item)"
                      :disabled="item.quantity >= (item.product?.quantity || 1)"
                      class="px-3 py-1 bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 rounded transition"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6 sticky top-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Resumo do Pedido</h2>

            <div class="space-y-3 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal:</span>
                <span>R$ {{ formatPrice(cartStore.total) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Frete:</span>
                <span>A calcular</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Imposto:</span>
                <span>A calcular</span>
              </div>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between text-xl font-bold text-gray-900">
                <span>Total:</span>
                <span class="text-blue-600">R$ {{ formatPrice(cartStore.total) }}</span>
              </div>
            </div>

            <button
              @click="goToCheckout"
              class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition mb-3"
            >
              Ir para Checkout
            </button>

            <router-link
              to="/products"
              class="block w-full text-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
            >
              Continuar Comprando
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cartStore';
import type { CartItem } from '@/types';

const router = useRouter();
const cartStore = useCartStore();

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const removeItem = async (itemId: number) => {
  if (confirm('Tem certeza que deseja remover este item?')) {
    await cartStore.removeItem(itemId);
  }
};

const updateQuantity = async (item: CartItem, newQuantity: number) => {
  if (newQuantity < 1) return;
  if (newQuantity > (item.product?.quantity || 1)) return;
  await cartStore.updateItem(item.id, newQuantity);
};

const incrementQuantity = async (item: CartItem) => {
  if (item.quantity < (item.product?.quantity || 1)) {
    await cartStore.updateItem(item.id, item.quantity + 1);
  }
};

const decrementQuantity = async (item: CartItem) => {
  if (item.quantity > 1) {
    await cartStore.updateItem(item.id, item.quantity - 1);
  }
};

const goToCheckout = async () => {
  await router.push('/checkout');
};

onMounted(() => {
  cartStore.fetchCart();
});
</script>
