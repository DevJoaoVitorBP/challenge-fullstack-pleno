import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api';
import type { Order, PaginatedResponse } from '@/types';
import { getErrorMessage } from '@/utils/errorHandler';

export interface CreateOrderData {
  shipping_address: {
    street: string;
    city: string;
    state: string;
    zip: string;
  };
  billing_address: {
    street: string;
    city: string;
    state: string;
    zip: string;
  };
  notes?: string;
}

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<Order[]>([]);
  const currentOrder = ref<Order | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });

  async function fetchOrders(page: number = 1, per_page: number = 10) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.get('/orders', { params: { page, per_page } });
      const data = response.data as PaginatedResponse<Order>;
      orders.value = data.data;
      pagination.value = data.meta;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar pedidos');
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchOrderById(id: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.get(`/orders/${id}`);
      currentOrder.value = response.data.data;
      return currentOrder.value;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar pedido');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createOrder(data: CreateOrderData) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.post('/orders', data);
      currentOrder.value = response.data.data;
      return currentOrder.value;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao criar pedido');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateOrderStatus(id: number, status: string) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.put(`/orders/${id}/status`, { status });
      const updatedOrder = response.data.data;
      const index = orders.value.findIndex((o: Order) => o.id === id);
      if (index !== -1) {
        orders.value[index] = updatedOrder;
      }
      if (currentOrder.value?.id === id) {
        currentOrder.value = updatedOrder;
      }
      return updatedOrder;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao atualizar status do pedido');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function reset() {
    orders.value = [];
    currentOrder.value = null;
    error.value = null;
    pagination.value = {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1,
    };
  }

  return {
    orders,
    currentOrder,
    isLoading,
    error,
    pagination,
    fetchOrders,
    fetchOrderById,
    createOrder,
    updateOrderStatus,
    reset,
  };
});
