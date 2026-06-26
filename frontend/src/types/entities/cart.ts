import type { BaseEntity } from './base-entity';
import type { CartItem } from './cart-item';

export interface Cart extends BaseEntity {
  user_id: number;
  session_id?: string;
  items: CartItem[];
}
