import type { OrderStatus } from '../enums/order-status';

export interface OrderFilters {
  status?: OrderStatus;
  user_id?: number;
  date_from?: string;
  date_to?: string;
  page?: number;
  per_page?: number;
}
