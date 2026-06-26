<template>
  <footer class="bg-gray-900 text-gray-300 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- About -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">Sobre</h3>
          <p class="text-sm text-gray-400">
            Uma plataforma de e-commerce moderna construída com Laravel e Vue.js.
          </p>
        </div>

        <!-- Links -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">Links</h3>
          <ul class="space-y-2 text-sm">
            <li><router-link to="/" class="hover:text-white transition">Home</router-link></li>
            <li>
              <router-link to="/products" class="hover:text-white transition">Produtos</router-link>
            </li>
            <li>
              <router-link
                v-if="authStore.isAuthenticated"
                to="/orders"
                class="hover:text-white transition"
                >Meus Pedidos</router-link
              >
            </li>
            <li v-if="authStore.isAdmin">
              <router-link to="/admin" class="hover:text-white transition"
                >Painel Admin</router-link
              >
            </li>
          </ul>
        </div>

        <!-- Suporte -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">Suporte</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-white transition">Contato</a></li>
            <li><a href="#" class="hover:text-white transition">FAQ</a></li>
            <li><a href="#" class="hover:text-white transition">Políticas</a></li>
          </ul>
        </div>

        <!-- Redes -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">Redes Sociais</h3>
          <div class="flex gap-4">
            <a
              href="#"
              class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition"
            >
              <span class="text-xs">f</span>
            </a>
            <a
              href="#"
              class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-400 transition"
            >
              <span class="text-xs">𝕏</span>
            </a>
            <a
              href="#"
              class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-pink-600 transition"
            >
              <span class="text-xs">ig</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Divisor -->
      <div class="border-t border-gray-800 pt-8">
        <div class="text-center text-sm text-gray-500">
          <p>&copy; 2026 E-Commerce. Todos os direitos reservados.</p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
</script>
