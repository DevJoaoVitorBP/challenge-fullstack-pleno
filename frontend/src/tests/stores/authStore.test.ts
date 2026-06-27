import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from '@/stores/authStore';

// Mock axios
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

describe('AuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    localStorage.clear();
    vi.clearAllMocks();
  });

  afterEach(() => {
    localStorage.clear();
  });

  describe('Initial State', () => {
    it('should have default initial state', () => {
      const store = useAuthStore();
      expect(store.user).toBeNull();
      expect(store.token).toBeNull();
      expect(store.isLoading).toBe(false);
      expect(store.error).toBeNull();
    });

    it('should restore token from localStorage on init', () => {
      localStorage.setItem('token', 'test-token-123');
      // Recreate store after localStorage is set
      const store = useAuthStore();
      expect(store.token).toBe('test-token-123');
    });
  });

  describe('Computed Properties', () => {
    it('should compute isAuthenticated correctly', () => {
      const store = useAuthStore();
      expect(store.isAuthenticated).toBe(false);

      store.token = 'test-token';
      expect(store.isAuthenticated).toBe(true);

      store.token = null;
      expect(store.isAuthenticated).toBe(false);
    });

    it('should compute isAdmin correctly', () => {
      const store = useAuthStore();
      expect(store.isAdmin).toBe(false);

      store.user = {
        id: 1,
        name: 'Admin User',
        email: 'admin@test.com',
        is_admin: true,
        created_at: new Date().toISOString(),
      };
      expect(store.isAdmin).toBe(true);

      store.user.is_admin = false;
      expect(store.isAdmin).toBe(false);
    });
  });

  describe('Logout', () => {
    it('should clear user and token on logout', async () => {
      const store = useAuthStore();
      store.user = {
        id: 1,
        name: 'Test User',
        email: 'test@test.com',
        is_admin: false,
        created_at: new Date().toISOString(),
      };
      store.token = 'test-token';
      localStorage.setItem('token', 'test-token');

      await store.logout();

      expect(store.user).toBeNull();
      expect(store.token).toBeNull();
      expect(localStorage.getItem('token')).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });
  });

  describe('Error Handling', () => {
    it('should handle and clear errors', () => {
      const store = useAuthStore();
      store.error = 'Authentication failed';

      expect(store.error).toBe('Authentication failed');

      store.error = null;
      expect(store.error).toBeNull();
    });

    it('should clear error on logout', async () => {
      const store = useAuthStore();
      store.error = 'Some error';
      store.token = 'test-token';

      await store.logout();

      expect(store.error).toBeNull();
    });
  });
});
