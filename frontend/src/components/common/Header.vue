<template>
  <header class="bg-white shadow">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold">EC</span>
        </div>
        <span class="font-bold text-lg">E-Commerce</span>
      </router-link>

      <!-- Menu Center -->
      <div class="hidden md:flex items-center gap-8">
        <router-link to="/products" class="text-gray-700 hover:text-blue-600 transition">
          Produtos
        </router-link>
        <router-link
          v-if="authStore.isAdmin"
          to="/admin"
          class="text-gray-700 hover:text-blue-600 transition"
        >
          Admin
        </router-link>
      </div>

      <!-- Menu Right -->
      <div class="flex items-center gap-6">
        <!-- Cart -->
        <router-link v-if="authStore.isAuthenticated" to="/cart" class="relative">
          <svg
            class="w-6 h-6 text-gray-700 hover:text-blue-600 transition"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.8 5M7 13l-2.293 2.293a1 1 0 00.117 1.497A6 6 0 0012 21c1.657 0 3.157-.671 4.243-1.757a1 1 0 00.117-1.497L17 13M17 13l4-8m-4 8h2.9a2 2 0 002-2V5.9a2 2 0 00-2-2h-.9"
            />
          </svg>
          <span
            v-if="cartStore.itemCount > 0"
            class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartStore.itemCount }}
          </span>
        </router-link>

        <!-- User Menu -->
        <div v-if="authStore.isAuthenticated" class="flex items-center gap-4">
          <div class="text-sm text-gray-700">{{ authStore.user?.name }}</div>
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm"
          >
            Sair
          </button>
        </div>

        <!-- Auth Links -->
        <div v-else class="flex items-center gap-4">
          <router-link to="/login" class="text-gray-700 hover:text-blue-600 transition font-medium">
            Login
          </router-link>
          <router-link
            to="/register"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
          >
            Registrar
          </router-link>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useCartStore } from '@/stores/cartStore';

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push('/');
  } catch (error) {
    console.error('Erro ao fazer logout:', error);
  }
};
</script>
