# Firebase Push Notifications API Documentation

## Overview

Sky Laini uses Firebase Cloud Messaging (FCM) for push notifications outside the app. This enables real-time notifications to users even when the app is closed or in the background.

## Features

1. **Automatic Push Notifications** - Sent automatically with in-app notifications
2. **FCM Token Management** - Register/remove device tokens
3. **Admin Broadcast** - Send notifications to all users, agents, or customers
4. **Statistics** - Track FCM token coverage

---

## Setup Requirements

### 1. Firebase Project Details

Your Firebase project is already configured:
- **Project ID**: `skyline-c84aa`
- **Service Account**: `firebase-adminsdk-fbsvc@skyline-c84aa.iam.gserviceaccount.com`

### 2. Service Account (Already Configured!)

The service account JSON file has been placed in:
```
storage/app/firebase/service-account.json
```

⚠️ **IMPORTANT**: This file contains sensitive credentials. It's already added to `.gitignore` and should NEVER be committed to version control.

### 3. Optional: Legacy Server Key

If you want a fallback, add the Legacy Server Key to `.env`:
```env
FIREBASE_SERVER_KEY=your-legacy-server-key-here
```

To get the Legacy Server Key:
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select project **skyline-c84aa**
3. Go to **Project Settings** → **Cloud Messaging**
4. Copy the **Server Key** (Legacy)

### 4. Run Migration (if not done)

```bash
php artisan migrate
```

---

## API Endpoints

### FCM Token Management

#### Register FCM Token
Registers device token for push notifications.

```http
POST /api/fcm/token
Authorization: Bearer {token}
Content-Type: application/json

{
    "token": "fcm_device_token_from_app",
    "device_type": "android"  // or "ios"
}
```

**Response:**
```json
{
    "success": true,
    "message": "FCM token registered successfully",
    "data": {
        "device_type": "android",
        "registered_at": "2025-12-12T20:00:00+03:00"
    }
}
```

#### Remove FCM Token
Remove token on logout.

```http
DELETE /api/fcm/token
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "FCM token removed successfully"
}
```

#### Check Token Status
Check if user has registered FCM token.

```http
GET /api/fcm/status
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "has_token": true,
    "device_type": "android",
    "registered_at": "2025-12-12T20:00:00+03:00"
}
```

#### Test Push Notification
Send a test notification (for debugging).

```http
POST /api/fcm/test
Authorization: Bearer {token}
```

---

### Admin Push Notifications

#### Broadcast Notification
Send notification to all users, agents, or customers. **Admin only**.

```http
POST /api/admin/push/broadcast
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Important Announcement 📢",
    "body": "Your message here...",
    "target": "all"  // "all", "agents", or "customers"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Broadcast notification sent",
    "result": {
        "target": "all",
        "push_success": 150,
        "push_failure": 5,
        "in_app_created": 155
    }
}
```

#### Send to Specific User
Send notification to a specific user. **Admin only**.

```http
POST /api/admin/push/user/{userId}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Personal Message",
    "body": "Message for this user..."
}
```

#### Get FCM Statistics
Get statistics about FCM token coverage. **Admin only**.

```http
GET /api/admin/push/stats
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
    "success": true,
    "stats": {
        "total_users": 500,
        "users_with_token": 450,
        "token_coverage": "90%",
        "by_device": {
            "android": 400,
            "ios": 45,
            "unknown": 5
        },
        "by_role": {
            "agents": 50,
            "customers": 400
        },
        "recent_registrations_7d": 25
    }
}
```

#### Get Users with Tokens
Get paginated list of users with FCM tokens. **Admin only**.

```http
GET /api/admin/push/users
Authorization: Bearer {admin_token}
```

#### Get Broadcast History
Get history of admin broadcasts. **Admin only**.

```http
GET /api/admin/push/history
Authorization: Bearer {admin_token}
```

---

## Automatic Notifications

These notifications are sent automatically when events occur:

### 1. New Line Request
**Who receives:** All verified agents
**Trigger:** Customer creates new line request
```
Title: Ombi Jipya la Laini! 📱
Body: Mteja anahitaji laini ya {NetworkName}. Ombi #{RequestNumber}
```

### 2. Order Created
**Who receives:** Customer who made request
**Trigger:** Line request is successfully created
```
Title: Ombi Limesajiliwa! ✅
Body: Ombi lako la laini ya {NetworkName} #{RequestNumber} limesajiliwa.
```

### 3. Agent Accepted
**Who receives:** Customer
**Trigger:** Agent accepts the request
```
Title: Agent Amekubali Ombi! 🎉
Body: {AgentName} amekubali ombi lako #{RequestNumber}. Tafadhali kamilisha malipo.
```

### 4. Payment Received
**Who receives:** Customer AND Agent
**Trigger:** Customer completes USSD payment

Customer:
```
Title: Malipo Yamepokelewa! 💰
Body: Asante! Malipo yako ya TZS {Amount} yamekamilika. Agent anakuja!
```

Agent:
```
Title: Mteja Amelipa! 💰
Body: {CustomerName} amelipa TZS {Amount}. Nenda kumhudumia!
```

### 5. Job Completed
**Who receives:** Customer AND Agent
**Trigger:** Agent marks job as completed

Customer:
```
Title: Kazi Imekamilika! 🎊
Body: Laini yako ya {NetworkName} imesajiliwa kikamilifu na {AgentName}.
```

Agent:
```
Title: Kazi Imekamilika! 🎊
Body: Umekamilisha kazi kwa {CustomerName}. Umepata TZS {Earnings}!
```

---

## Flutter Integration

### 1. Add Firebase to Flutter App

See `docs/FLUTTER_FCM_INTEGRATION.md` for detailed Flutter integration guide.

### 2. Quick Summary

```dart
// 1. Get FCM token
final token = await FirebaseMessaging.instance.getToken();

// 2. Register token with backend
await api.post('/fcm/token', {
  'token': token,
  'device_type': Platform.isAndroid ? 'android' : 'ios',
});

// 3. Listen for token refresh
FirebaseMessaging.instance.onTokenRefresh.listen((token) {
  api.post('/fcm/token', {'token': token});
});

// 4. On logout, remove token
await api.delete('/fcm/token');
```

---

## Notification Data Structure

Each push notification includes:

```json
{
    "notification": {
        "title": "Notification Title",
        "body": "Notification message..."
    },
    "data": {
        "type": "new_request",
        "line_request_id": "123",
        "request_number": "REQ-2025-0001",
        "click_action": "FLUTTER_NOTIFICATION_CLICK"
    }
}
```

Use the `type` field to handle navigation in the app.

---

## Error Handling

The service handles these FCM errors:
- **InvalidRegistration** - Token is invalid, removed from database
- **NotRegistered** - App uninstalled, token removed from database
- **MessageTooBig** - Payload exceeds 4KB limit
- **InvalidDataKey** - Reserved key used in data

---

## Best Practices

1. **Register token on app startup** and on login
2. **Remove token on logout** to prevent notifications to logged-out devices
3. **Handle token refresh** when FCM regenerates tokens
4. **Use data payload** for navigation, not just notification payload
5. **Test on real devices** - FCM doesn't work on iOS simulator

---

## Files Created

| File | Purpose |
|------|---------|
| `config/firebase.php` | Firebase configuration |
| `app/Services/FirebasePushNotificationService.php` | FCM service class |
| `app/Http/Controllers/Api/FcmTokenController.php` | Token management API |
| `app/Http/Controllers/Api/AdminPushNotificationController.php` | Admin broadcast API |
| `database/migrations/2025_12_12_200000_add_fcm_token_to_users_table.php` | FCM token migration |
