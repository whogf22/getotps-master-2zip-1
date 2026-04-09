export interface AdminStats {
  totalUsers: number;
  activeUsers: number;
  totalOrders: number;
  pendingOrders: number;
  totalRentals: number;
  completedOrders: number;
  revenue: string;
  todayRevenue: string;
  totalBalances: string;
  proxnumBalance: string;
}

export interface AdminUser {
  id: number;
  username: string;
  email: string;
  balance: string;
  apiKey: string | null;
  role: string;
  orderCount?: number;
  lastOrderAt?: string | null;
}

export interface AdminTransaction {
  id: number;
  userId: number;
  type: string;
  amount: string;
  description: string;
  orderId: number | null;
  stripeSessionId: string | null;
  createdAt: string;
  username: string;
  email: string;
}

export interface AdminDeposit {
  id: number;
  userId: number;
  amount: string;
  currency: string;
  cryptoAmount: string | null;
  walletAddress: string;
  txHash: string | null;
  status: string;
  createdAt: string;
  expiresAt: string;
  completedAt: string | null;
  username: string;
  email: string;
}

export interface AdminSettings {
  price_multiplier: string;
  default_country: string;
  service_multipliers: Record<string, string>;
}

export interface ServiceItem {
  id: number;
  name: string;
  slug: string;
  category: string;
  price: string;
  icon: string | null;
}
