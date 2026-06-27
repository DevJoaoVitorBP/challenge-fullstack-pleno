import type { BaseEntity } from './base-entity';
import type { Category } from './category';
import type { Tag } from './tag';

export interface Product extends BaseEntity {
  name: string;
  slug: string;
  description: string;
  price: number;
  cost_price: number;
  quantity: number;
  min_quantity: number;
  active: boolean;
  category_id: number;
  category?: Category | null;
  tags?: Tag[];
}
