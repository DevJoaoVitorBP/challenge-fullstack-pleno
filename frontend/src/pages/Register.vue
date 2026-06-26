<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-lg shadow-md w-full max-w-md p-8">
      <h1 class="text-3xl font-bold mb-2 text-center">Registro</h1>
      <p class="text-center text-gray-600 mb-8">Crie sua conta</p>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Seu Nome"
          />
        </div>

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

        <!-- Password Confirmation -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
          <input
            v-model="form.password_confirmation"
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
          <span v-else>Registrar</span>
        </button>
      </form>

      <!-- Login Link -->
      <p class="text-center text-gray-600 mt-6">
        Já tem conta?
        <router-link to="/login" class="text-blue-600 hover:underline font-medium">
          Faça login aqui
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const handleRegister = async () => {
  try {
    await authStore.register(form);
    router.push('/');
  } catch (error) {
    console.error('Erro no registro:', error);
  }
};
</script>
