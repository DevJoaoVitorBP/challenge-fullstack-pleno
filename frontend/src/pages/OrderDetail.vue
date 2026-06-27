<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Back Button -->
      <router-link
        to="/orders"
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
        Voltar aos Pedidos
      </router-link>

      <!-- Loading State -->
      <div v-if="ordersStore.isLoading" class="flex justify-center py-16">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="ordersStore.error"
        class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8"
      >
        <p class="text-red-600">{{ ordersStore.error }}</p>
      </div>

      <!-- Order Details -->
      <div v-else-if="order" class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Número do Pedido</p>
              <p class="text-2xl font-bold text-gray-900">#{{ order.id }}</p>
            </div>
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Data</p>
              <p class="text-xl font-semibold text-gray-900">{{ formatDate(order.created_at) }}</p>
            </div>
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Status</p>
              <span
                :class="getStatusClass(order.status)"
                class="px-4 py-2 rounded-full text-sm font-semibold inline-block"
              >
                {{ translateStatus(order.status) }}
              </span>
            </div>
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Total</p>
              <p class="text-2xl font-bold text-blue-600">R$ {{ formatPrice(order.total) }}</p>
            </div>
          </div>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Itens do Pedido</h2>
          <div v-if="order.items && order.items.length > 0" class="space-y-4">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex gap-4 border-b pb-4 last:border-b-0 last:pb-0"
            >
              <!-- Product Image -->
              <div
                class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center"
              >
                <svg
                  class="w-10 h-10 text-gray-400"
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

              <!-- Item Details -->
              <div class="flex-1">
                <p class="text-lg font-semibold text-gray-900">
                  {{ item.product?.name || 'Produto' }}
                </p>
                <p class="text-gray-600 text-sm mt-1">{{ item.product?.description }}</p>
                <div class="flex justify-between mt-3">
                  <span class="text-gray-600"
                    >Qtd: <span class="font-semibold">{{ item.quantity }}</span></span
                  >
                  <span class="text-gray-600"
                    >Unitário:
                    <span class="font-semibold">R$ {{ formatPrice(item.unit_price) }}</span></span
                  >
                  <span class="text-blue-600 font-bold"
                    >R$ {{ formatPrice(item.total_price) }}</span
                  >
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500">Nenhum item neste pedido</p>
        </div>

        <!-- Shipping Address -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Endereço de Entrega</h2>
          <div v-if="order.shipping_address" class="text-gray-700 leading-relaxed space-y-2">
            <p>
              <span class="font-semibold">Rua:</span>
              {{ order.shipping_address.street }}
            </p>
            <p>
              <span class="font-semibold">Cidade:</span>
              {{ order.shipping_address.city }} - {{ order.shipping_address.state }}
            </p>
            <p>
              <span class="font-semibold">CEP:</span>
              {{ formatZip(order.shipping_address.zip) }}
            </p>
          </div>
          <p v-else class="text-gray-500">Não informado</p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Resumo</h2>
          <div class="space-y-3 max-w-md ml-auto">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal:</span>
              <span>R$ {{ formatPrice(order.total) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Frete:</span>
              <span>Grátis</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Imposto:</span>
              <span>Incluído</span>
            </div>
            <div class="border-t pt-3 flex justify-between text-xl font-bold text-gray-900">
              <span>Total:</span>
              <span class="text-blue-600">R$ {{ formatPrice(order.total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found -->
      <div v-else class="text-center py-12 bg-white rounded-lg shadow">
        <p class="text-gray-500 text-lg">Pedido não encontrado</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useOrdersStore } from '@/stores/ordersStore';

const route = useRoute();
const ordersStore = useOrdersStore();

const order = computed(() => ordersStore.currentOrder);

const formatDate = (date: string | Date) => {
  const d = new Date(date);
  return d.toLocaleDateString('pt-BR', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const formatZip = (zip: string) => {
  if (!zip) return '';
  // Formato: 12345-678 (5 dígitos, hífen, 3 dígitos)
  return zip.replace(/^(\d{5})(\d{3})$/, '$1-$2');
};

const translateStatus = (status: string): string => {
  const statusMap: Record<string, string> = {
    pending: 'Pendente',
    processing: 'Processando',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
  };
  return statusMap[status] || status;
};

const getStatusClass = (status: string): string => {
  const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
  const id = Number(route.params.id);
  if (id) {
    await ordersStore.fetchOrderById(id);
  }
});
</script>
