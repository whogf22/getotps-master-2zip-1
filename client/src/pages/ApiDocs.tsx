import { useMemo } from "react";
import { useAuth } from "@/contexts/AuthContext";
import { DashboardLayout } from "@/components/DashboardLayout";
import SwaggerUI from "swagger-ui-react";
import "swagger-ui-react/swagger-ui.css";

function injectApiKey(spec: any, apiKey?: string | null) {
  if (!apiKey) return spec;
  const cloned = structuredClone(spec);
  if (!cloned.components) cloned.components = {};
  if (!cloned.components.securitySchemes) cloned.components.securitySchemes = {};
  cloned.components.securitySchemes.ApiKeyAuth = {
    type: "apiKey",
    in: "header",
    name: "X-API-Key",
  };
  return cloned;
}

export default function ApiDocs() {
  const { user } = useAuth();
  const specUrl = "/openapi.yaml";
  const requestInterceptor = useMemo(() => {
    return (request: any) => {
      if (user?.apiKey) {
        request.headers = request.headers || {};
        request.headers["X-API-Key"] = user.apiKey;
      }
      return request;
    };
  }, [user?.apiKey]);

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-xl font-bold">API Documentation</h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            Interactive OpenAPI reference for purchase, balance, activations, and webhooks.
          </p>
        </div>
        <div className="rounded-xl border border-border bg-background overflow-hidden">
          <SwaggerUI
            url={specUrl}
            requestInterceptor={requestInterceptor}
            docExpansion="list"
            defaultModelsExpandDepth={1}
            tryItOutEnabled
          />
        </div>
      </div>
    </DashboardLayout>
  );
}
