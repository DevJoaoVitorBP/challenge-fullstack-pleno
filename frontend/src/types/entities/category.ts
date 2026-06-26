import type { BaseEntity } from './base-entity';

export interface Category extends BaseEntity {
  name: string;
  slug: string;
  description: string;
  parent_id?: number;
  active: boolean;
  children?: Category[];
}
