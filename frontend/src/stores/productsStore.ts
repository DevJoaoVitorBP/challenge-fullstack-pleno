import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { Product, Category, PaginatedResponse } from '@/types';
import { getErrorMessage } from '@/utils/errorHandler';

export interface ProductFilters {
  category_id?: number | string;
  search?: string;
  min_price?: number;
  max_price?: number;
  sort?: string;
  per_page?: number;
  page?: number;
}

function cleanFilters(filters: ProductFilters): ProductFilters {
  return Object.fromEntries(
    Object.entries(filters).filter(
      ([, value]) => value !== undefined && value !== null && value !== ''
    )
  ) as ProductFilters;
}

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const currentProduct = ref<Product | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
  });

  const hasNextPage = computed(() => pagination.value.current_page < pagination.value.last_page);
  const hasPrevPage = computed(() => pagination.value.current_page > 1);

  async function fetchProducts(filters: ProductFilters = {}) {
    isLoading.value = true;
    error.value = null;
    try {
      const cleanedFilters = cleanFilters(filters);
      const response = await api.get('/products', { params: cleanedFilters });
      const data = response.data as PaginatedResponse<Product>;
      products.value = data.data;
      pagination.value = {
        current_page: data.meta.current_page,
        per_page: data.meta.per_page,
        total: data.meta.total,
        last_page: data.meta.last_page,
      };
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produtos');
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchProductById(id: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.get(`/products/${id}`);
      currentProduct.value = response.data.data;
      return currentProduct.value;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produto');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCategories() {
    error.value = null;
    try {
      const response = await api.get('/categories');
      categories.value = response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar categorias');
    }
  }

  async function fetchCategoryProducts(
    categoryId: number,
    filters: Omit<ProductFilters, 'category_id'> = {}
  ) {
    isLoading.value = true;
    error.value = null;
    try {
      const cleanedFilters = cleanFilters(filters as ProductFilters);
      const response = await api.get(`/categories/${categoryId}/products`, {
        params: cleanedFilters,
      });
      const data = response.data as PaginatedResponse<Product>;
      products.value = data.data;
      pagination.value = {
        current_page: data.meta.current_page,
        per_page: data.meta.per_page,
        total: data.meta.total,
        last_page: data.meta.last_page,
      };
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produtos da categoria');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function reset() {
    products.value = [];
    currentProduct.value = null;
    error.value = null;
    pagination.value = {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    };
  }

  return {
    products,
    categories,
    currentProduct,
    isLoading,
    error,
    pagination,
    hasNextPage,
    hasPrevPage,
    fetchProducts,
    fetchProductById,
    fetchCategories,
    fetchCategoryProducts,
    reset,
  };
});
