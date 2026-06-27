import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import { getErrorMessage } from '@/utils/errorHandler';

export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  created_at: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token'));
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.is_admin ?? false);

  async function register(data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.post('/auth/register', data);
      const newToken = response.data.data.token as string;
      token.value = newToken;
      localStorage.setItem('token', newToken);
      api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
      await fetchUser();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro no registro');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function login(email: string, password: string) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.post('/auth/login', { email, password });
      const newToken = response.data.data.token as string;
      token.value = newToken;
      localStorage.setItem('token', newToken);
      api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
      await fetchUser();
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao fazer login');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/auth/me');
      user.value = response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao buscar usuário');
      throw err;
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout');
    } finally {
      user.value = null;
      token.value = null;
      error.value = null;
      localStorage.removeItem('token');
      delete api.defaults.headers.common['Authorization'];
    }
  }

  function initializeAuth() {
    if (token.value) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }
  }

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    isAdmin,
    register,
    login,
    logout,
    fetchUser,
    initializeAuth,
  };
});
