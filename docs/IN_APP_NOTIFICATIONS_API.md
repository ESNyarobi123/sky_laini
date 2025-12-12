# In-App Notifications API Documentation

## Overview
The Sky Laini application now has a comprehensive in-app notification system that automatically notifies users about important events in real-time.

## API Endpoints

All endpoints require authentication via Bearer token (Sanctum).

### Base URL
```
/api/in-app-notifications
```

### 1. Get All Notifications
```http
GET /api/in-app-notifications
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| per_page | integer | 20 | Number of notifications per page |
| type | string | null | Filter by notification type |
| unread_only | boolean | false | Only return unread notifications |

**Response:**
```json
{
  "notifications": [
    {
      "id": 1,
      "type": "payment_received",
      "title": "Malipo Yamepokelewa! 💰",
      "message": "Asante! Malipo yako ya TZS 1,000 yamekamilika kwa ombi #REQ-ABC123.",
      "icon": "payments",
      "color": "#22C55E",
      "data": {
        "amount": 1000,
        "confirmation_code": "XYZ789"
      },
      "line_request_id": 5,
      "is_read": false,
      "read_at": null,
      "time_ago": "2 minutes ago",
      "created_at": "2025-12-12T14:30:00+03:00"
    }
  ],
  "unread_count": 5,
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

### 2. Get Unread Count
```http
GET /api/in-app-notifications/unread-count
```

**Response:**
```json
{
  "unread_count": 5
}
```

### 3. Get Notification Summary
```http
GET /api/in-app-notifications/summary
```

**Response:**
```json
{
  "summary": [
    {"type": "new_request", "total": 10, "unread": 3},
    {"type": "payment_received", "total": 8, "unread": 1},
    {"type": "job_completed", "total": 15, "unread": 0}
  ],
  "total_notifications": 33,
  "total_unread": 4
}
```

### 4. Get Single Notification
```http
GET /api/in-app-notifications/{id}
```

**Response:**
```json
{
  "notification": {
    "id": 1,
    "type": "payment_received",
    "title": "Malipo Yamepokelewa! 💰",
    "message": "...",
    "icon": "payments",
    "color": "#22C55E",
    "data": {...},
    "line_request_id": 5,
    "is_read": false,
    "read_at": null,
    "time_ago": "2 minutes ago",
    "created_at": "2025-12-12T14:30:00+03:00"
  }
}
```

### 5. Mark Notification as Read
```http
POST /api/in-app-notifications/{id}/read
```

**Response:**
```json
{
  "message": "Notification marked as read",
  "notification": {...},
  "unread_count": 4
}
```

### 6. Mark All Notifications as Read
```http
POST /api/in-app-notifications/read-all
```

**Response:**
```json
{
  "message": "All notifications marked as read",
  "marked_count": 5,
  "unread_count": 0
}
```

### 7. Delete Notification
```http
DELETE /api/in-app-notifications/{id}
```

**Response:**
```json
{
  "message": "Notification deleted",
  "unread_count": 4
}
```

### 8. Clear Read Notifications
```http
POST /api/in-app-notifications/clear-read
```

**Response:**
```json
{
  "message": "Read notifications cleared",
  "deleted_count": 15
}
```

---

## Notification Types

| Type | Description | Recipients | Icon | Color |
|------|-------------|------------|------|-------|
| `new_request` | New line request available | All Agents | bell_ring | #4F46E5 (Indigo) |
| `order_created` | Customer's order was created | Customer | check_circle | #10B981 (Green) |
| `agent_accepted` | Agent accepted the request | Customer | person_check | #3B82F6 (Blue) |
| `payment_pending` | Payment USSD was sent | Customer | hourglass | #F59E0B (Amber) |
| `payment_received` | Payment completed | Customer & Agent | payments | #22C55E (Emerald) |
| `job_completed` | Job was completed | Customer & Agent | task_alt | #10B981 (Green) |
| `job_cancelled` | Request was cancelled | Customer | cancel | #EF4444 (Red) |
| `request_released` | Request available again | Online Agents | refresh | #8B5CF6 (Purple) |
| `rating_received` | Agent received a rating | Agent | star | #F59E0B (Amber) |

---

## Automatic Notification Triggers

### When Customer Creates a Request
1. ✅ Customer receives "Order Created" notification
2. ✅ All verified agents receive "New Request" notification

### When Agent Accepts Request
1. ✅ Customer receives "Agent Accepted" notification
2. ✅ Customer receives "Payment Pending" notification (after USSD sent)

### When Customer Pays
1. ✅ Customer receives "Payment Received" notification (with confirmation code)
2. ✅ Agent receives "Payment Received" notification (with customer location)

### When Job is Completed
1. ✅ Customer receives "Job Completed" notification
2. ✅ Agent receives "Job Completed" notification (with earnings)

### When Customer Cancels Request
1. ✅ Customer receives "Job Cancelled" notification

### When Request is Released (by agent or payment failure)
1. ✅ All online verified agents receive "Request Released" notification

### When Customer Rates Agent
1. ✅ Agent receives "Rating Received" notification (with stars)

---

## Flutter Implementation Example

### Notification Model
```dart
class InAppNotification {
  final int id;
  final String type;
  final String title;
  final String message;
  final String? icon;
  final String? color;
  final Map<String, dynamic>? data;
  final int? lineRequestId;
  final bool isRead;
  final String? readAt;
  final String timeAgo;
  final String createdAt;

  InAppNotification.fromJson(Map<String, dynamic> json) : 
    id = json['id'],
    type = json['type'],
    title = json['title'],
    message = json['message'],
    icon = json['icon'],
    color = json['color'],
    data = json['data'],
    lineRequestId = json['line_request_id'],
    isRead = json['is_read'] ?? false,
    readAt = json['read_at'],
    timeAgo = json['time_ago'],
    createdAt = json['created_at'];
}
```

### Notification Service
```dart
class NotificationService {
  final ApiClient _apiClient;

  Future<List<InAppNotification>> getNotifications({int page = 1, bool unreadOnly = false}) async {
    final response = await _apiClient.get(
      '/in-app-notifications',
      queryParameters: {'page': page, 'unread_only': unreadOnly}
    );
    return (response['notifications'] as List)
      .map((n) => InAppNotification.fromJson(n))
      .toList();
  }

  Future<int> getUnreadCount() async {
    final response = await _apiClient.get('/in-app-notifications/unread-count');
    return response['unread_count'];
  }

  Future<void> markAsRead(int id) async {
    await _apiClient.post('/in-app-notifications/$id/read');
  }

  Future<void> markAllAsRead() async {
    await _apiClient.post('/in-app-notifications/read-all');
  }
}
```

### Icon Mapping
```dart
IconData getNotificationIcon(String? iconName) {
  return switch(iconName) {
    'bell_ring' => Icons.notifications_active,
    'check_circle' => Icons.check_circle,
    'person_check' => Icons.person_add,
    'payments' => Icons.payment,
    'hourglass' => Icons.hourglass_empty,
    'task_alt' => Icons.task_alt,
    'cancel' => Icons.cancel,
    'refresh' => Icons.refresh,
    'directions_run' => Icons.directions_run,
    'star' => Icons.star,
    _ => Icons.notifications,
  };
}
```

---

## Database Schema

```sql
CREATE TABLE in_app_notifications (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  line_request_id BIGINT UNSIGNED NULL,
  type VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  icon VARCHAR(255) NULL,
  color VARCHAR(255) NULL,
  data JSON NULL,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  INDEX (user_id, read_at),
  INDEX (user_id, created_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (line_request_id) REFERENCES line_requests(id) ON DELETE CASCADE
);
```

---

## Notes

1. **Migration Required**: Run `php artisan migrate` on your server to create the `in_app_notifications` table.

2. **Polling vs Real-time**: Currently uses polling. For real-time, consider implementing:
   - Laravel Echo + Pusher/Soketi for WebSocket support
   - Firebase Cloud Messaging for push notifications

3. **Cleanup**: Old notifications (30+ days) can be cleaned up using:
   ```php
   app(InAppNotificationService::class)->cleanupOldNotifications(30);
   ```
