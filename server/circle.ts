import { initiateDeveloperControlledWalletsClient } from "@circle-fin/developer-controlled-wallets";
import { v4 as uuidv4 } from "uuid";

const CIRCLE_API_KEY = process.env.CIRCLE_API_KEY || "";
const CIRCLE_ENTITY_SECRET = process.env.CIRCLE_ENTITY_SECRET || "";

let circleClient: ReturnType<typeof initiateDeveloperControlledWalletsClient> | null = null;

function getClient() {
  if (!circleClient) {
    if (!CIRCLE_API_KEY || !CIRCLE_ENTITY_SECRET) {
      throw new Error("Circle API credentials not configured. Set CIRCLE_API_KEY and CIRCLE_ENTITY_SECRET.");
    }
    circleClient = initiateDeveloperControlledWalletsClient({
      apiKey: CIRCLE_API_KEY,
      entitySecret: CIRCLE_ENTITY_SECRET,
    });
  }
  return circleClient;
}

export function isCircleConfigured(): boolean {
  return !!(CIRCLE_API_KEY && CIRCLE_ENTITY_SECRET);
}

export async function createWalletSet(name: string): Promise<{ id: string }> {
  const client = getClient();
  const response = await client.createWalletSet({
    idempotencyKey: uuidv4(),
    name,
  });
  const walletSet = response.data?.walletSet;
  if (!walletSet?.id) {
    throw new Error("Failed to create Circle wallet set");
  }
  return { id: walletSet.id };
}

export async function getOrCreateDefaultWalletSet(): Promise<string> {
  const client = getClient();
  const response = await client.listWalletSets({});
  const existing = response.data?.walletSets;
  if (existing && existing.length > 0) {
    return existing[0].id!;
  }
  const newSet = await createWalletSet("GetOTPs User Wallets");
  return newSet.id;
}

export async function createUserWallet(walletSetId: string, blockchain: string = "ETH"): Promise<{
  walletId: string;
  address: string;
  blockchain: string;
}> {
  const client = getClient();
  const response = await client.createWallets({
    idempotencyKey: uuidv4(),
    blockchains: [blockchain as any],
    count: 1,
    walletSetId,
  });
  const wallets = response.data?.wallets;
  if (!wallets || wallets.length === 0) {
    throw new Error("Failed to create Circle wallet");
  }
  const wallet = wallets[0];
  return {
    walletId: wallet.id!,
    address: wallet.address!,
    blockchain: wallet.blockchain!,
  };
}

export async function getWalletBalance(walletId: string): Promise<{
  balances: Array<{
    amount: string;
    tokenSymbol: string;
    tokenName: string;
    tokenAddress: string;
  }>;
}> {
  const client = getClient();
  const response = await client.getWalletTokenBalance({ id: walletId });
  const tokenBalances = response.data?.tokenBalances || [];
  return {
    balances: tokenBalances.map((tb: any) => ({
      amount: tb.amount || "0",
      tokenSymbol: tb.token?.symbol || "",
      tokenName: tb.token?.name || "",
      tokenAddress: tb.token?.tokenAddress || "",
    })),
  };
}

export async function listWalletTransactions(walletId: string): Promise<Array<{
  id: string;
  type: string;
  state: string;
  amounts: string[];
  tokenSymbol: string;
  sourceAddress: string;
  destinationAddress: string;
  txHash: string;
  createDate: string;
  blockchain: string;
}>> {
  const client = getClient();
  const response = await client.listTransactions({
    walletIds: [walletId],
  });
  const transactions = response.data?.transactions || [];
  return transactions.map((tx: any) => ({
    id: tx.id || "",
    type: tx.transactionType || "",
    state: tx.state || "",
    amounts: tx.amounts || [],
    tokenSymbol: tx.tokenId ? "" : "",
    sourceAddress: tx.sourceAddress || "",
    destinationAddress: tx.destinationAddress || "",
    txHash: tx.txHash || "",
    createDate: tx.createDate || "",
    blockchain: tx.blockchain || "",
  }));
}

export async function getWallet(walletId: string): Promise<{
  id: string;
  address: string;
  blockchain: string;
  state: string;
}> {
  const client = getClient();
  const response = await client.getWallet({ id: walletId });
  const wallet = response.data?.wallet;
  if (!wallet) {
    throw new Error("Wallet not found");
  }
  return {
    id: wallet.id!,
    address: wallet.address!,
    blockchain: wallet.blockchain!,
    state: wallet.state!,
  };
}
