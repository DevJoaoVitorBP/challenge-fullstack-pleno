<template>
  <div id="app" class="flex flex-col min-h-screen">
    <Header />

    <main class="flex-1">
      <router-view />
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import Header from '@/components/common/Header.vue';
import Footer from '@/components/common/Footer.vue';

const authStore = useAuthStore();

onMounted(() => {
  authStore.initializeAuth();
});
</script>

<style scoped></style>
