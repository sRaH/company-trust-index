# CompanyTrustIndex Helm chart

This chart deploys the production Docker image with a ClusterIP service and optional Ingress.

## Install

Build and push an image, then provide the image and application secrets:

```sh
helm upgrade --install company-trust-index ./deploy/helm/company-trust-index \
  --set image.repository=registry.example.com/company-trust-index \
  --set image.tag=1.0.0 \
  --set secret.appSecret='replace-me' \
  --set secret.databaseUrl='postgresql://user:password@postgres:5432/app'
```

For GitOps or externally managed secrets, set `secret.create=false` and `existingSecret` to a
Secret containing `APP_SECRET` and `DATABASE_URL` keys.
