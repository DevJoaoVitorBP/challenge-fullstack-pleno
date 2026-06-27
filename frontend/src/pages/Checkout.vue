<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-4xl font-bold mb-12">Checkout</h1>

      <!-- Redirect if cart is empty -->
      <div v-if="cartStore.items.length === 0" class="text-center py-16 bg-white rounded-lg shadow">
        <p class="text-gray-500 text-lg mb-8">
          Seu carrinho está vazio. Adicione produtos antes de fazer checkout.
        </p>
        <router-link
          to="/products"
          class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700"
        >
          Voltar às Compras
        </router-link>
      </div>

      <!-- Checkout Form -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submitOrder" class="space-y-8">
            <!-- Shipping Address -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Endereço de Entrega</h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Nome Completo *</label
                  >
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Endereço *</label>
                  <input
                    v-model="form.address"
                    type="text"
                    placeholder="Rua, número"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                  <input
                    v-model="form.complement"
                    type="text"
                    placeholder="Apto, sala, etc"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                  <input
                    v-model="form.city"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                  <input
                    v-model="form.state"
                    type="text"
                    placeholder="SP, RJ, etc"
                    maxlength="2"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">CEP *</label>
                  <input
                    v-model="form.zipcode"
                    type="text"
                    placeholder="00000-000"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Telefone *</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Observações</h2>
              <textarea
                v-model="form.notes"
                placeholder="Adicione observações para o pedido (opcional)"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
            >
              {{ isSubmitting ? 'Processando...' : 'Finalizar Pedido' }}
            </button>
          </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6 sticky top-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Resumo do Pedido</h2>

            <!-- Items -->
            <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
              <div
                v-for="item in cartStore.items"
                :key="item.id"
                class="flex justify-between text-sm"
              >
                <span class="text-gray-600">{{ item.product?.name }} x{{ item.quantity }}</span>
                <span class="font-semibold"
                  >R$ {{ formatPrice((item.product?.price || 0) * item.quantity) }}</span
                >
              </div>
            </div>

            <div class="border-t pt-4 space-y-3 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal:</span>
                <span>R$ {{ formatPrice(cartStore.total) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Frete:</span>
                <span>Grátis</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Imposto:</span>
                <span>Incluído</span>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="flex justify-between text-xl font-bold text-gray-900">
                <span>Total:</span>
                <span class="text-blue-600">R$ {{ formatPrice(cartStore.total) }}</span>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-600 text-sm">{{ errorMessage }}</p>
            </div>

            <!-- Success Message -->
            <div
              v-if="successMessage"
              class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg"
            >
              <p class="text-green-600 text-sm">{{ successMessage }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cartStore';
import { useOrdersStore } from '@/stores/ordersStore';
// import error handling utility if needed
import { getErrorMessage } from '@/utils/errorHandler';

const router = useRouter();
const cartStore = useCartStore();
const ordersStore = useOrdersStore();

const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const form = reactive({
  name: '',
  email: '',
  address: '',
  complement: '',
  city: '',
  state: '',
  zipcode: '',
  phone: '',
  notes: '',
});

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const submitOrder = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const orderData = {
      shipping_address: {
        street: `${form.address}${form.complement ? ` - ${form.complement}` : ''}`,
        city: form.city,
        state: form.state,
        zip: form.zipcode,
      },
      billing_address: {
        street: `${form.address}${form.complement ? ` - ${form.complement}` : ''}`,
        city: form.city,
        state: form.state,
        zip: form.zipcode,
      },
      notes: form.notes || undefined,
    };

    await ordersStore.createOrder(orderData);
    successMessage.value = 'Pedido criado com sucesso!';

    setTimeout(() => {
      cartStore.clearCart();
      router.push('/orders');
    }, 2000);
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'Erro ao criar pedido. Tente novamente.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>
