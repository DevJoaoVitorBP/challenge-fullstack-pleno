import type { BaseEntity } from './base-entity';

export interface Tag extends BaseEntity {
  name: string;
  slug: string;
}
