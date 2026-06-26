<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
      <h1 class="text-3xl font-bold mb-8">Meus Pedidos</h1>
      <p class="text-gray-600">Página em desenvolvimento...</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useOrdersStore } from '@/stores/ordersStore';

const ordersStore = useOrdersStore();

onMounted(() => {
  ordersStore.fetchOrders();
});
</script>
