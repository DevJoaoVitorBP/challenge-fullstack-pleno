import type { User } from '../entities/user';

export interface AuthResponse {
  token: string;
  user: User;
}
