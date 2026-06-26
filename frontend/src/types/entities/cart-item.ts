import type { BaseEntity } from './base-entity';
import type { Product } from './product';

export interface CartItem extends BaseEntity {
  cart_id: number;
  product_id: number;
  product?: Product;
  quantity: number;
}
