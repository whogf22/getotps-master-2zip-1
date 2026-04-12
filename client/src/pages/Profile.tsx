import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { User, Key, Lock, Copy, Check, RefreshCw, Eye, EyeOff, Save, AlertTriangle } from "lucide-react";

export default function Profile() {
  const { user, refreshUser } = useAuth();
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [copiedKey, setCopiedKey] = useState(false);
  const [newlyGeneratedKey, setNewlyGeneratedKey] = useState<string | null>(null);
  const [showRegenConfirm, setShowRegenConfirm] = useState(false);
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showCurrentPw, setShowCurrentPw] = useState(false);
  const [showNewPw, setShowNewPw] = useState(false);
  const [editUsername, setEditUsername] = useState("");
  const [editEmail, setEditEmail] = useState("");
  const [isEditing, setIsEditing] = useState(false);

  const generateKeyMutation = useMutation({
    mutationFn: async () => { const res = await apiRequest("POST", "/api/profile/generate-api-key", {}); return res.json(); },
    onSuccess: (data: any) => {
      queryClient.invalidateQueries({ queryKey: ["/api/auth/me"] });
      refreshUser();
      setNewlyGeneratedKey(data.apiKey);
      setShowRegenConfirm(false);
      toast({ title: "New API key generated", description: "Copy it now — it won't be shown again." });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    },
  });

  const changePasswordMutation = useMutation({
    mutationFn: async () => { const res = await apiRequest("POST", "/api/profile/change-password", { currentPassword, newPassword }); return res.json(); },
    onSuccess: () => {
      setCurrentPassword("");
      setNewPassword("");
      setConfirmPassword("");
      toast({ title: "Password updated", description: "Your password has been changed successfully." });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    },
  });

  const updateProfileMutation = useMutation({
    mutationFn: async () => {
      const updates: any = {};
      if (editUsername && editUsername !== user?.username) updates.username = editUsername;
      if (editEmail && editEmail !== user?.email) updates.email = editEmail;
      const res = await apiRequest("PUT", "/api/profile", updates);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/auth/me"] });
      refreshUser();
      setIsEditing(false);
      toast({ title: "Profile updated" });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    },
  });

  const handleCopyKey = async () => {
    const keyToCopy = newlyGeneratedKey;
    if (keyToCopy) {
      await navigator.clipboard.writeText(keyToCopy);
      setCopiedKey(true);
      setTimeout(() => setCopiedKey(false), 2000);
    }
  };

  const handleChangePassword = () => {
    if (!currentPassword || !newPassword || !confirmPassword) {
      toast({ title: "Error", description: "Fill in all password fields", variant: "destructive" });
      return;
    }
    if (newPassword !== confirmPassword) {
      toast({ title: "Error", description: "Passwords don't match", variant: "destructive" });
      return;
    }
    if (newPassword.length < 8) {
      toast({ title: "Error", description: "Password must be at least 8 characters", variant: "destructive" });
      return;
    }
    changePasswordMutation.mutate();
  };

  const handleStartEdit = () => {
    setEditUsername(user?.username || "");
    setEditEmail(user?.email || "");
    setIsEditing(true);
  };

  const apiKeyPrefix = (user as any)?.apiKeyPrefix;
  const displayKey = newlyGeneratedKey
    ? newlyGeneratedKey
    : apiKeyPrefix
      ? `${apiKeyPrefix}...`
      : "No API key generated";

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-2xl">
        <div>
          <h1 className="text-xl font-bold">Profile</h1>
          <p className="text-sm text-muted-foreground mt-0.5">Manage your account settings</p>
        </div>

        <Card className="border-border">
          <CardHeader className="pb-3">
            <div className="flex items-center justify-between">
              <CardTitle className="text-sm font-semibold flex items-center gap-2">
                <User className="w-4 h-4 text-primary" />
                Account Details
              </CardTitle>
              {!isEditing && (
                <Button variant="ghost" size="sm" onClick={handleStartEdit} className="text-xs h-7">
                  Edit
                </Button>
              )}
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-4">
              <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl font-bold">
                {user?.username?.charAt(0).toUpperCase() || "U"}
              </div>
              <div>
                <p className="font-semibold" data-testid="text-username">{user?.username}</p>
                <p className="text-sm text-muted-foreground" data-testid="text-email">{user?.email}</p>
                <div className="flex items-center gap-2 mt-1">
                  <Badge variant="secondary" className="text-xs h-5">
                    {user?.role === "admin" ? "Admin" : "User"}
                  </Badge>
                  <span className="text-xs text-primary font-semibold">Balance: ${user?.balance}</span>
                </div>
              </div>
            </div>

            {isEditing ? (
              <div className="space-y-3 pt-2">
                <div>
                  <Label className="text-xs text-muted-foreground">Username</Label>
                  <Input value={editUsername} onChange={e => setEditUsername(e.target.value)} className="h-8 mt-1 text-sm" />
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Email</Label>
                  <Input value={editEmail} onChange={e => setEditEmail(e.target.value)} className="h-8 mt-1 text-sm" />
                </div>
                <div className="flex gap-2">
                  <Button size="sm" onClick={() => updateProfileMutation.mutate()} disabled={updateProfileMutation.isPending}>
                    <Save className="w-3.5 h-3.5 mr-1.5" />
                    {updateProfileMutation.isPending ? "Saving..." : "Save"}
                  </Button>
                  <Button size="sm" variant="outline" onClick={() => setIsEditing(false)}>Cancel</Button>
                </div>
              </div>
            ) : (
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-xs text-muted-foreground">Username</Label>
                  <Input value={user?.username || ""} disabled className="h-8 mt-1 text-sm" />
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Email</Label>
                  <Input value={user?.email || ""} disabled className="h-8 mt-1 text-sm" />
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        <Card className="border-border">
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-semibold flex items-center gap-2">
              <Key className="w-4 h-4 text-primary" />
              API Key
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <p className="text-xs text-muted-foreground">Use this key to authenticate API requests. Keep it secret.</p>
            <div className="flex items-center gap-2 p-3 rounded-lg bg-muted/50 border border-border">
              <code className="flex-1 font-mono text-xs truncate" data-testid="text-api-key-profile">{displayKey}</code>
              {newlyGeneratedKey && (
                <button
                  onClick={handleCopyKey}
                  className="p-1 rounded text-muted-foreground hover:text-foreground transition-colors shrink-0"
                  data-testid="button-copy-api-key"
                >
                  {copiedKey ? <Check className="w-3.5 h-3.5 text-green-500" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              )}
            </div>
            {newlyGeneratedKey && (
              <div className="flex items-start gap-2 p-2.5 rounded-lg bg-yellow-500/10 border border-yellow-500/20">
                <AlertTriangle className="w-3.5 h-3.5 text-yellow-500 mt-0.5 shrink-0" />
                <p className="text-xs text-yellow-700 dark:text-yellow-400">
                  Copy this key now. It will not be shown again after you leave this page.
                </p>
              </div>
            )}

            {showRegenConfirm ? (
              <div className="flex items-center gap-2">
                <p className="text-xs text-destructive flex-1">This will invalidate your current key. Are you sure?</p>
                <Button size="sm" variant="destructive" onClick={() => generateKeyMutation.mutate()} disabled={generateKeyMutation.isPending}>
                  {generateKeyMutation.isPending ? "Generating..." : "Confirm"}
                </Button>
                <Button size="sm" variant="outline" onClick={() => setShowRegenConfirm(false)}>Cancel</Button>
              </div>
            ) : (
              <Button
                size="sm"
                variant="outline"
                onClick={() => apiKeyPrefix ? setShowRegenConfirm(true) : generateKeyMutation.mutate()}
                disabled={generateKeyMutation.isPending}
                data-testid="button-generate-api-key"
              >
                <RefreshCw className={`w-3.5 h-3.5 mr-1.5 ${generateKeyMutation.isPending ? "animate-spin" : ""}`} />
                {apiKeyPrefix ? "Regenerate API Key" : "Generate API Key"}
              </Button>
            )}
          </CardContent>
        </Card>

        <Card className="border-border">
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-semibold flex items-center gap-2">
              <Lock className="w-4 h-4 text-primary" />
              Change Password
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <Label className="text-xs">Current Password</Label>
              <div className="relative">
                <Input
                  type={showCurrentPw ? "text" : "password"}
                  value={currentPassword}
                  onChange={e => setCurrentPassword(e.target.value)}
                  className="h-9 pr-10 text-sm"
                  placeholder="Current password"
                  data-testid="input-current-password"
                />
                <button
                  type="button"
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                  onClick={() => setShowCurrentPw(!showCurrentPw)}
                >
                  {showCurrentPw ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>
            <div className="space-y-1.5">
              <Label className="text-xs">New Password</Label>
              <div className="relative">
                <Input
                  type={showNewPw ? "text" : "password"}
                  value={newPassword}
                  onChange={e => setNewPassword(e.target.value)}
                  className="h-9 pr-10 text-sm"
                  placeholder="Min. 8 characters"
                  data-testid="input-new-password"
                />
                <button
                  type="button"
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                  onClick={() => setShowNewPw(!showNewPw)}
                >
                  {showNewPw ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>
            <div className="space-y-1.5">
              <Label className="text-xs">Confirm New Password</Label>
              <Input
                type="password"
                value={confirmPassword}
                onChange={e => setConfirmPassword(e.target.value)}
                className="h-9 text-sm"
                placeholder="Repeat new password"
                data-testid="input-confirm-password"
              />
            </div>
            <Button
              onClick={handleChangePassword}
              disabled={changePasswordMutation.isPending}
              size="sm"
              data-testid="button-change-password"
            >
              {changePasswordMutation.isPending ? "Updating..." : "Update Password"}
            </Button>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
