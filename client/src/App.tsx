import { Switch, Route, Router } from "wouter";
import { queryClient } from "./lib/queryClient";
import { QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { ThemeProvider } from "@/contexts/ThemeContext";
import { AuthProvider, useAuth } from "@/contexts/AuthContext";
import NotFound from "@/pages/not-found";
import Landing from "@/pages/Landing";
import Login from "@/pages/Login";
import Register from "@/pages/Register";
import Dashboard from "@/pages/Dashboard";
import BuyNumber from "@/pages/BuyNumber";
import ActiveNumbers from "@/pages/ActiveNumbers";
import History from "@/pages/History";
import AddFunds from "@/pages/AddFunds";
import ApiDocs from "@/pages/ApiDocs";
import Status from "@/pages/Status";
import Profile from "@/pages/Profile";
import AdminDashboard from "@/pages/AdminDashboard";
import AdminUsers from "@/pages/AdminUsers";
import AdminDeposits from "@/pages/AdminDeposits";
import AdminSettings from "@/pages/AdminSettings";
import { Skeleton } from "@/components/ui/skeleton";

function ProtectedRoute({ component: Component }: { component: React.ComponentType }) {
  const { user, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="space-y-2">
          <Skeleton className="h-4 w-48" />
          <Skeleton className="h-4 w-32" />
        </div>
      </div>
    );
  }

  if (!user) {
    window.location.assign("/login");
    return null;
  }

  return <Component />;
}

function AdminRoute({ component: Component }: { component: React.ComponentType }) {
  const { user, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="space-y-2">
          <Skeleton className="h-4 w-48" />
          <Skeleton className="h-4 w-32" />
        </div>
      </div>
    );
  }

  if (!user) {
    window.location.assign("/login");
    return null;
  }

  if (user.role !== "admin") {
    window.location.assign("/dashboard");
    return null;
  }

  return <Component />;
}

function AppRouter() {
  return (
    <Switch>
      <Route path="/" component={Landing} />
      <Route path="/login" component={Login} />
      <Route path="/register" component={Register} />
      <Route path="/dashboard" component={() => <ProtectedRoute component={Dashboard} />} />
      <Route path="/buy" component={() => <ProtectedRoute component={BuyNumber} />} />
      <Route path="/active" component={() => <ProtectedRoute component={ActiveNumbers} />} />
      <Route path="/history" component={() => <ProtectedRoute component={History} />} />
      <Route path="/funds" component={() => <ProtectedRoute component={AddFunds} />} />
      <Route path="/api-docs" component={() => <ProtectedRoute component={ApiDocs} />} />
      <Route path="/status" component={Status} />
      <Route path="/profile" component={() => <ProtectedRoute component={Profile} />} />
      <Route path="/admin" component={() => <AdminRoute component={AdminDashboard} />} />
      <Route path="/admin/users" component={() => <AdminRoute component={AdminUsers} />} />
      <Route path="/admin/deposits" component={() => <AdminRoute component={AdminDeposits} />} />
      <Route path="/admin/settings" component={() => <AdminRoute component={AdminSettings} />} />
      <Route component={NotFound} />
    </Switch>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <AuthProvider>
          <TooltipProvider>
            <Toaster />
            <Router>
              <AppRouter />
            </Router>
          </TooltipProvider>
        </AuthProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
}

export default App;
