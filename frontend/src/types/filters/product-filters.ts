export interface ProductFilters {
  search?: string;
  category_id?: number;
  active?: boolean;
  min_price?: number;
  max_price?: number;
  page?: number;
  per_page?: number;
}
