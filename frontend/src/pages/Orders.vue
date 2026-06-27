<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold">Meus Pedidos</h1>
        <router-link
          to="/products"
          class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
        >
          Continuar Comprando
        </router-link>
      </div>

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

      <!-- Empty State -->
      <div
        v-else-if="ordersStore.orders.length === 0"
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
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
        <p class="text-gray-500 text-lg mb-8">Você ainda não tem pedidos</p>
        <router-link
          to="/products"
          class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700"
        >
          Começar a Comprar
        </router-link>
      </div>

      <!-- Orders Table -->
      <div v-else class="space-y-4">
        <div
          v-for="order in ordersStore.orders"
          :key="order.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition"
        >
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-6">
            <!-- Order ID & Date -->
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Pedido</p>
              <p class="text-lg font-bold text-gray-900">#{{ order.id }}</p>
              <p class="text-gray-500 text-sm mt-1">{{ formatDate(order.created_at) }}</p>
            </div>

            <!-- Items Count -->
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Items</p>
              <p class="text-lg font-bold text-gray-900">{{ order.items?.length || 0 }}</p>
            </div>

            <!-- Total -->
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Total</p>
              <p class="text-lg font-bold text-blue-600">R$ {{ formatPrice(order.total) }}</p>
            </div>

            <!-- Status -->
            <div>
              <p class="text-gray-600 text-sm font-medium mb-1">Status</p>
              <span
                :class="getStatusClass(order.status)"
                class="px-3 py-1 rounded-full text-sm font-semibold inline-block"
              >
                {{ translateStatus(order.status) }}
              </span>
            </div>

            <!-- Action -->
            <div class="flex items-end">
              <router-link
                :to="`/orders/${order.id}`"
                class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-center"
              >
                Ver Detalhes
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useOrdersStore } from '@/stores/ordersStore';

const ordersStore = useOrdersStore();

const formatDate = (date: string | Date) => {
  const d = new Date(date);
  return d.toLocaleDateString('pt-BR', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
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

onMounted(() => {
  ordersStore.fetchOrders();
});
</script>
