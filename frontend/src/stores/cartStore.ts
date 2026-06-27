// cartStore.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { CartItem } from '@/types';
import { getErrorMessage } from '@/utils/errorHandler';

export interface Cart {
  id: number;
  user_id: number;
  items: CartItem[];
  created_at: string;
  updated_at: string;
}

export const useCartStore = defineStore('cart', () => {
  const cart = ref<Cart | null>(null);
  const items = ref<CartItem[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const itemCount = computed(() => items.value.length);
  const total = computed(() =>
    items.value.reduce(
      (sum: number, item: CartItem) => sum + (item.product?.price ?? 0) * item.quantity,
      0
    )
  );

  function syncItems() {
    items.value = cart.value?.items ?? [];
  }

  async function fetchCart() {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.get('/cart');
      cart.value = response.data.data;
      syncItems();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar carrinho');
    } finally {
      isLoading.value = false;
    }
  }

  async function addItem(productId: number, quantity: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.post('/cart/items', { product_id: productId, quantity });
      cart.value = response.data.data;
      syncItems();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao adicionar item');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateItem(itemId: number, quantity: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.put(`/cart/items/${itemId}`, { quantity });
      cart.value = response.data.data;
      syncItems();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao atualizar item');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function removeItem(itemId: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.delete(`/cart/items/${itemId}`);
      cart.value = response.data.data;
      syncItems();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao remover item');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function clearCart() {
    // ← now sync, no API call
    cart.value = null;
    items.value = [];
  }

  function reset() {
    cart.value = null;
    items.value = [];
    error.value = null;
  }

  return {
    cart,
    items,
    itemCount,
    total,
    isLoading,
    error,
    fetchCart,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    reset,
  };
});
