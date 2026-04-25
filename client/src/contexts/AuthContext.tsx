import { createContext, useContext, useState, useEffect, ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { apiRequest } from "@/lib/queryClient";

interface User {
  id: number;
  username: string;
  email: string;
  emailVerified?: number | boolean;
  balance: string;
  apiKey: string | null;
  role: string;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  login: (email: string, password: string, hCaptchaToken?: string, rememberMe?: boolean) => Promise<void>;
  logout: () => Promise<void>;
  register: (username: string, email: string, password: string, hCaptchaToken?: string, acceptedTerms?: boolean) => Promise<void>;
  refreshUser: () => void;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();

  const { data: user, isLoading, refetch } = useQuery<User | null>({
    queryKey: ["/api/auth/me"],
    queryFn: async () => {
      try {
        const res = await fetch("/api/auth/me", { credentials: "include" });
        if (res.status === 401) return null;
        if (!res.ok) return null;
        return res.json();
      } catch {
        return null;
      }
    },
    retry: false,
    staleTime: 30000,
  });

  const login = async (email: string, password: string, hCaptchaToken?: string, rememberMe = false) => {
    const res = await apiRequest("POST", "/api/auth/login", { email, password, hCaptchaToken, rememberMe });
    const userData = await res.json();
    queryClient.setQueryData(["/api/auth/me"], userData);
  };

  const logout = async () => {
    await apiRequest("POST", "/api/auth/logout", {});
    queryClient.setQueryData(["/api/auth/me"], null);
    queryClient.clear();
  };

  const register = async (
    username: string,
    email: string,
    password: string,
    hCaptchaToken?: string,
    acceptedTerms = false
  ) => {
    const res = await apiRequest("POST", "/api/auth/register", {
      username,
      email,
      password,
      hCaptchaToken,
      acceptedTerms,
    });
    const userData = await res.json();
    queryClient.setQueryData(["/api/auth/me"], userData);
  };

  const refreshUser = () => {
    refetch();
    queryClient.invalidateQueries({ queryKey: ["/api/auth/me"] });
  };

  return (
    <AuthContext.Provider value={{ user: user ?? null, isLoading, login, logout, register, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
