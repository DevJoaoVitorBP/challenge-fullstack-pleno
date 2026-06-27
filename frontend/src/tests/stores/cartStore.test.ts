import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useCartStore } from '@/stores/cartStore';

vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
    defaults: {
      headers: {
        common: {},
      },
    },
    interceptors: {
      request: { use: vi.fn(), eject: vi.fn() },
      response: { use: vi.fn(), eject: vi.fn() },
    },
  },
}));

describe('CartStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  describe('Initial State', () => {
    it('should have empty initial state', () => {
      const store = useCartStore();
      expect(store.items).toEqual([]);
      expect(store.isLoading).toBe(false);
      expect(store.error).toBeNull();
    });
  });

  describe('Computed Properties', () => {
    it('should compute itemCount correctly', () => {
      const store = useCartStore();
      expect(store.itemCount).toBe(0);

      store.items = [
        {
          id: 1,
          cart_id: 1,
          product_id: 1,
          quantity: 2,
          product: {
            id: 1,
            name: 'Product 1',
            price: 100,
            quantity: 10,
            slug: 'product-1',
            description: 'Test',
            cost_price: 50,
            min_quantity: 1,
            active: true,
            category_id: 1,
            category: null,
            tags: [],
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      ];

      expect(store.itemCount).toBe(1);
    });

    it('should compute total correctly', () => {
      const store = useCartStore();
      expect(store.total).toBe(0);

      store.items = [
        {
          id: 1,
          cart_id: 1,
          product_id: 1,
          quantity: 2,
          product: {
            id: 1,
            name: 'Product 1',
            price: 100,
            quantity: 10,
            slug: 'product-1',
            description: 'Test',
            cost_price: 50,
            min_quantity: 1,
            active: true,
            category_id: 1,
            category: null,
            tags: [],
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
        {
          id: 2,
          cart_id: 1,
          product_id: 2,
          quantity: 1,
          product: {
            id: 2,
            name: 'Product 2',
            price: 50,
            quantity: 5,
            slug: 'product-2',
            description: 'Test',
            cost_price: 25,
            min_quantity: 1,
            active: true,
            category_id: 1,
            category: null,
            tags: [],
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      ];

      // (2 * 100) + (1 * 50) = 250
      expect(store.total).toBe(250);
    });
  });

  describe('clearCart', () => {
    it('should empty the cart', () => {
      const store = useCartStore();
      store.items = [
        {
          id: 1,
          cart_id: 1,
          product_id: 1,
          quantity: 2,
          product: null,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      ];

      store.clearCart();

      expect(store.items).toEqual([]);
      expect(store.total).toBe(0);
      expect(store.itemCount).toBe(0);
    });
  });

  describe('Error Handling', () => {
    it('should handle and clear errors', () => {
      const store = useCartStore();
      store.error = 'Cart loading failed';

      expect(store.error).toBe('Cart loading failed');

      store.error = null;
      expect(store.error).toBeNull();
    });
  });
});
