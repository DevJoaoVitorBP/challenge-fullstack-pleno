import type { BaseEntity } from './base-entity';
import type { Product } from './product';

export interface OrderItem extends BaseEntity {
  order_id: number;
  product_id: number;
  product?: Product;
  quantity: number;
  unit_price: number;
  total_price: number;
}
