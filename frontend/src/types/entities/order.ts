import type { BaseEntity } from './base-entity';
import type { OrderItem } from './order-item';
import type { OrderStatus } from '../enums/order-status';

export interface Order extends BaseEntity {
  user_id: number;
  status: OrderStatus;
  subtotal: number;
  tax: number;
  shipping_cost: number;
  total: number;
  shipping_address: string;
  billing_address: string;
  notes?: string;
  items?: OrderItem[];
}
