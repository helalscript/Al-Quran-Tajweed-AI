# RevenueCat Integration Guide for Flutter

This document provides the necessary API endpoints and instructions for integrating RevenueCat with the backend.

## 1. Webhook URL (For RevenueCat Dashboard)

You need to configure this Webhook URL in your RevenueCat Dashboard (Project Settings -> Webhooks) so the backend can receive real-time subscription events (e.g., renewals, cancellations).

- **Method**: `POST`
- **URL**: `https://<your-domain>/api/webhook/revenuecat`

*Note: Replace `<your-domain>` with the actual production URL of your backend.*

---

## 2. Subscription APIs (For Flutter App)

These APIs are protected and require the user to be authenticated via the App (passing the Bearer Token).

### A. Sync Subscription
Use this endpoint to manually sync the user's subscription status from RevenueCat to the backend. You should call this after a successful purchase or restore in the Flutter app.

- **Method**: `POST`
- **URL**: `https://<your-domain>/api/subscriptions/sync`
- **Headers**: 
  - `Authorization: Bearer <user_token>`
  - `Content-Type: application/json`
- **Body**: (Empty body is fine, the backend will fetch the user's RevenueCat ID automatically, or you can send specific data if your backend sync requires it. By default, `noopstudios/laravel-revenue-cat` will check the authenticated user.)
- **Response**:
  ```json
  {
      "success": true,
      "message": "Subscription synced successfully."
  }
  ```

### B. Check Subscription Status
Use this endpoint if you want the backend to tell you if the user is currently subscribed. (Though you can also rely on RevenueCat's SDK locally in Flutter).

- **Method**: `GET`
- **URL**: `https://<your-domain>/api/subscriptions/status`
- **Headers**: 
  - `Authorization: Bearer <user_token>`
  - `Content-Type: application/json`
- **Response**:
  ```json
  {
      "is_subscribed": true,
      "active_entitlements": ["premium"],
      "status": "active"
  }
  ```

---

## 3. Flutter Implementation Tips
- Use the official [purchases_flutter](https://pub.dev/packages/purchases_flutter) package.
- When configuring the RevenueCat SDK in Flutter, make sure to set the `appUserID` to the user's backend ID (or whatever unique identifier you use) so the backend can match the user during webhook events.
- Example initialization:
  ```dart
  await Purchases.logIn(backendUserId); 
  ```
- After a successful purchase in Flutter, call the **Sync Subscription** (`/api/subscriptions/sync`) API so the backend database is immediately updated without waiting for the webhook.
