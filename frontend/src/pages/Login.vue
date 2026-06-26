<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-lg shadow-md w-full max-w-md p-8">
      <h1 class="text-3xl font-bold mb-2 text-center">Login</h1>
      <p class="text-center text-gray-600 mb-8">Acesse sua conta</p>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="seu@email.com"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="••••••••"
          />
        </div>

        <!-- Error Message -->
        <div v-if="authStore.error" class="p-4 bg-red-100 text-red-800 rounded-lg text-sm">
          {{ authStore.error }}
        </div>

        <!-- Submit -->
        <button
          :disabled="authStore.isLoading"
          type="submit"
          class="w-full py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
        >
          <span v-if="authStore.isLoading">Carregando...</span>
          <span v-else>Entrar</span>
        </button>
      </form>

      <!-- Register Link -->
      <p class="text-center text-gray-600 mt-6">
        Não tem conta?
        <router-link to="/register" class="text-blue-600 hover:underline font-medium">
          Registre-se aqui
        </router-link>
      </p>

      <!-- Demo Credentials -->
      <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <p class="text-sm font-medium text-blue-900 mb-2">Credenciais de Teste:</p>
        <p class="text-sm text-blue-800">Email: admin@example.com</p>
        <p class="text-sm text-blue-800">Senha: password</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const handleLogin = async () => {
  try {
    await authStore.login(form.email, form.password);
    const redirect = route.query.redirect as string;
    router.push(redirect || '/');
  } catch (error) {
    console.error('Erro no login:', error);
  }
};
</script>
