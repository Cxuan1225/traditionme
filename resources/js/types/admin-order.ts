import type { Order } from '@/types/order';

export type AdminOrderUser = {
    id: number;
    name: string;
    email: string;
};

export interface AdminOrder extends Order {
    user: AdminOrderUser | null;
    paidAt: string | null;
    shippedAt: string | null;
    deliveredAt: string | null;
}
