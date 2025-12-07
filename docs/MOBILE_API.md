# 📱 SKY LAINI - Mobile App API Documentation

> **Version:** 1.0.0  
> **Last Updated:** December 7, 2025  
> **Base URL:** `https://your-domain.com/api`

---

## 📋 Table of Contents

1. [Authentication](#-authentication)
2. [User Profile](#-user-profile)
3. [Line Requests](#-line-requests)
4. [Payments](#-payments)
5. [Chat](#-chat)
6. [Invoices](#-invoices)
7. [Agent Features](#-agent-features)
8. [Working Hours](#-working-hours)
9. [Navigation & Directions](#-navigation--directions)
10. [Wallet & Earnings](#-wallet--earnings)
11. [Leaderboard](#-leaderboard)
12. [Notifications](#-notifications)
13. [Support](#-support)
14. [Settings](#-settings)
15. [Error Handling](#-error-handling)
16. [Mobile Implementation Guide](#-mobile-implementation-guide)

---

## 🔐 Authentication

All authenticated endpoints require the `Authorization` header:
```
Authorization: Bearer {your_token}
```

### Register New User
```http
POST /api/register
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Full name |
| email | string | Yes | Valid email address |
| password | string | Yes | Minimum 8 characters |
| password_confirmation | string | Yes | Must match password |
| role | string | Yes | `customer` or `agent` |

**Success Response (201):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "customer",
        "locale": "sw",
        "created_at": "2025-12-07T00:00:00.000000Z"
    },
    "token": "1|abc123xyz..."
}
```

---

### Login
```http
POST /api/login
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "customer",
        "locale": "sw"
    },
    "token": "1|abc123xyz...",
    "message": "Login successful"
}
```

**Error Response (401):**
```json
{
    "message": "Invalid credentials"
}
```

---

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer",
    "locale": "sw",
    "email_verified_at": null,
    "created_at": "2025-12-07T00:00:00.000000Z"
}
```

---

## 👤 User Profile

### Customer Profile

#### Get Profile
```http
GET /api/customer/profile
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "customer": {
        "id": 1,
        "user_id": 1,
        "phone": "+255123456789",
        "address": "Dar es Salaam",
        "current_latitude": -6.7924,
        "current_longitude": 39.2083
    },
    "stats": {
        "total_requests": 10,
        "completed_requests": 8,
        "pending_requests": 2
    }
}
```

#### Update Profile
```http
PUT /api/customer/profile
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "phone": "+255123456789",
    "address": "New Address, Dar es Salaam"
}
```

#### Update Location
```http
POST /api/customer/location
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "latitude": -6.7924,
    "longitude": 39.2083
}
```

**Success Response (200):**
```json
{
    "message": "Location updated successfully",
    "latitude": -6.7924,
    "longitude": 39.2083
}
```

---

### Agent Profile

#### Get Profile
```http
GET /api/agent/profile
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "user": {
        "id": 2,
        "name": "Agent Name",
        "email": "agent@example.com"
    },
    "agent": {
        "id": 1,
        "user_id": 2,
        "phone": "+255987654321",
        "is_online": true,
        "is_verified": true,
        "rating": 4.8,
        "completed_jobs": 50,
        "current_latitude": -6.8000,
        "current_longitude": 39.2100,
        "working_hours": {...},
        "timezone": "Africa/Dar_es_Salaam"
    },
    "wallet": {
        "balance": 150000,
        "pending": 25000
    }
}
```

#### Update Profile
```http
PUT /api/agent/profile
Authorization: Bearer {token}
Content-Type: application/json
```

#### Update Location
```http
POST /api/agent/location
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "latitude": -6.8000,
    "longitude": 39.2100,
    "is_online": true
}
```

---

## 📋 Line Requests

### List My Requests (Customer)
```http
GET /api/line-requests
Authorization: Bearer {token}
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| status | string | all | Filter by status: `pending`, `accepted`, `in_progress`, `completed`, `cancelled` |
| per_page | integer | 15 | Items per page |

**Success Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "request_number": "REQ-2024-001",
            "line_type": "vodacom",
            "status": "pending",
            "payment_status": "unpaid",
            "service_fee": 1000,
            "customer_phone": "+255123456789",
            "customer_latitude": -6.7924,
            "customer_longitude": 39.2083,
            "customer_address": "Dar es Salaam",
            "agent": null,
            "completion_code": null,
            "created_at": "2025-12-07T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

---

### Create New Request
```http
POST /api/line-requests
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "line_type": "vodacom",
    "customer_phone": "+255123456789",
    "customer_latitude": -6.7924,
    "customer_longitude": 39.2083,
    "customer_address": "Kinondoni, Dar es Salaam"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| line_type | string | Yes | `vodacom`, `airtel`, `tigo`, `halotel`, `tanztel` |
| customer_phone | string | Yes | Phone number for the new SIM |
| customer_latitude | float | Yes | Current latitude |
| customer_longitude | float | Yes | Current longitude |
| customer_address | string | No | Optional address description |

**Success Response (201):**
```json
{
    "message": "Request created successfully",
    "request": {
        "id": 1,
        "request_number": "REQ-2024-001",
        "line_type": "vodacom",
        "status": "pending",
        "service_fee": 1000,
        "created_at": "2025-12-07T00:00:00.000000Z"
    }
}
```

---

### View Single Request
```http
GET /api/line-requests/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "id": 1,
    "request_number": "REQ-2024-001",
    "line_type": "vodacom",
    "status": "accepted",
    "payment_status": "paid",
    "service_fee": 1000,
    "customer_phone": "+255123456789",
    "customer_latitude": -6.7924,
    "customer_longitude": 39.2083,
    "customer_address": "Kinondoni, Dar es Salaam",
    "completion_code": "123456",
    "agent": {
        "id": 1,
        "name": "Agent Name",
        "phone": "+255987654321",
        "rating": 4.8,
        "is_online": true,
        "current_latitude": -6.8000,
        "current_longitude": 39.2100
    },
    "created_at": "2025-12-07T00:00:00.000000Z",
    "updated_at": "2025-12-07T00:00:00.000000Z"
}
```

---

### Cancel Request
```http
POST /api/line-requests/{id}/cancel
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "message": "Request cancelled successfully"
}
```

---

### Rate Agent
```http
POST /api/line-requests/{id}/rate
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "rating": 5,
    "comment": "Excellent service, very fast!"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| rating | integer | Yes | 1-5 stars |
| comment | string | No | Optional feedback |

---

### Get Agent Location (Real-time Tracking)
```http
GET /api/line-requests/{id}/agent-location
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "agent": {
        "name": "Agent Name",
        "phone": "+255987654321",
        "latitude": -6.8000,
        "longitude": 39.2100,
        "is_online": true,
        "rating": 4.8
    },
    "customer_location": {
        "latitude": -6.7924,
        "longitude": 39.2083
    }
}
```

> **📍 Tip:** Poll this endpoint every 5-10 seconds for real-time tracking

---

## 💳 Payments

### Initiate Payment
```http
POST /api/line-requests/{id}/pay
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "phone": "+255123456789"
}
```

**Success Response (200):**
```json
{
    "message": "Payment initiated. Check your phone for USSD prompt.",
    "order_id": "ZNP-123456",
    "amount": 1000,
    "status": "pending"
}
```

---

### Check Payment Status
```http
GET /api/line-requests/{id}/payment-status
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "payment_status": "paid",
    "completion_code": "123456",
    "message": "Payment successful. Share this code with agent."
}
```

---

### Cancel Payment
```http
POST /api/line-requests/{id}/cancel-pay
Authorization: Bearer {token}
```

---

## 💬 Chat

### Get All Conversations
```http
GET /api/chat/conversations
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "conversations": [
        {
            "line_request_id": 1,
            "request_number": "REQ-2024-001",
            "participant": {
                "id": 2,
                "name": "Agent Name",
                "avatar": "https://ui-avatars.com/api/?name=Agent+Name"
            },
            "last_message": "Hello, I'm on my way!",
            "last_message_at": "2025-12-07T10:30:00.000000Z",
            "unread_count": 2,
            "is_online": true
        }
    ]
}
```

---

### Get Unread Count
```http
GET /api/chat/unread-count
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "unread_count": 5
}
```

---

### Get Messages for Request
```http
GET /api/chat/{lineRequestId}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "messages": [
        {
            "id": 1,
            "sender_id": 2,
            "sender_type": "agent",
            "sender": {
                "id": 2,
                "name": "Agent Name"
            },
            "message": "Hello, I'm on my way!",
            "attachment": null,
            "is_read": true,
            "read_at": "2025-12-07T10:31:00.000000Z",
            "created_at": "2025-12-07T10:30:00.000000Z"
        },
        {
            "id": 2,
            "sender_id": 1,
            "sender_type": "customer",
            "sender": {
                "id": 1,
                "name": "Customer Name"
            },
            "message": "Great, I'll be waiting!",
            "attachment": null,
            "is_read": false,
            "read_at": null,
            "created_at": "2025-12-07T10:32:00.000000Z"
        }
    ],
    "participant": {
        "id": 2,
        "name": "Agent Name",
        "is_online": true
    }
}
```

---

### Send Message
```http
POST /api/chat/{lineRequestId}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "message": "Hello, where are you?"
}
```

**Success Response (201):**
```json
{
    "message": {
        "id": 3,
        "sender_id": 1,
        "sender_type": "customer",
        "message": "Hello, where are you?",
        "is_read": false,
        "created_at": "2025-12-07T10:35:00.000000Z"
    },
    "success": true
}
```

---

### Mark Messages as Read
```http
POST /api/chat/{lineRequestId}/read
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "message": "Messages marked as read"
}
```

---

## 🧾 Invoices

### List Invoices
```http
GET /api/invoices
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-2024-001",
            "line_request_id": 1,
            "amount": 1000,
            "tax": 0,
            "total": 1000,
            "status": "paid",
            "payment_method": "mobile_money",
            "paid_at": "2025-12-07T10:00:00.000000Z",
            "created_at": "2025-12-07T09:00:00.000000Z"
        }
    ],
    "meta": {...}
}
```

---

### Get Invoice Details
```http
GET /api/invoices/{id}
Authorization: Bearer {token}
```

---

### Download Invoice
```http
GET /api/invoices/{id}/download
Authorization: Bearer {token}
```

Returns HTML content that can be displayed in a WebView or browser.

---

### Generate Invoice for Request
```http
POST /api/invoices/generate/{lineRequestId}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "invoice": {
        "id": 1,
        "invoice_number": "INV-2024-001",
        "amount": 1000,
        "total": 1000,
        "status": "paid"
    },
    "message": "Invoice generated successfully"
}
```

---

## 🚀 Agent Features

### Get Available Gigs
```http
GET /api/agent/gigs
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "gigs": [
        {
            "id": 5,
            "request_number": "REQ-2024-005",
            "line_type": "vodacom",
            "customer_name": "John Doe",
            "customer_phone": "+255123456789",
            "customer_latitude": -6.7924,
            "customer_longitude": 39.2083,
            "customer_address": "Kinondoni, Dar es Salaam",
            "distance_km": 2.5,
            "service_fee": 1000,
            "created_at": "2025-12-07T10:00:00.000000Z"
        }
    ]
}
```

---

### Get My Requests (Agent)
```http
GET /api/agent/requests
Authorization: Bearer {token}
```

---

### View Request Details (Agent)
```http
GET /api/agent/requests/{id}
Authorization: Bearer {token}
```

---

### Respond to Request
```http
POST /api/agent/requests/{id}/respond
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "action": "accept"
}
```

| Field | Required | Values |
|-------|----------|--------|
| action | Yes | `accept` or `reject` |

---

### Complete Job
```http
POST /api/agent/requests/{id}/complete
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "completion_code": "123456"
}
```

**Success Response (200):**
```json
{
    "message": "Job completed successfully!",
    "earnings": 800,
    "wallet_balance": 150800
}
```

**Error Response (400):**
```json
{
    "message": "Invalid completion code"
}
```

---

## 🕐 Working Hours

### Get Working Hours
```http
GET /api/agent/working-hours
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "working_hours": {
        "monday": {"enabled": true, "start": "08:00", "end": "18:00"},
        "tuesday": {"enabled": true, "start": "08:00", "end": "18:00"},
        "wednesday": {"enabled": true, "start": "08:00", "end": "18:00"},
        "thursday": {"enabled": true, "start": "08:00", "end": "18:00"},
        "friday": {"enabled": true, "start": "08:00", "end": "18:00"},
        "saturday": {"enabled": false, "start": "08:00", "end": "18:00"},
        "sunday": {"enabled": false, "start": "08:00", "end": "18:00"}
    },
    "timezone": "Africa/Dar_es_Salaam",
    "auto_offline": true
}
```

---

### Update Working Hours
```http
PUT /api/agent/working-hours
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "working_hours": {
        "monday": {"enabled": true, "start": "09:00", "end": "17:00"},
        "tuesday": {"enabled": true, "start": "09:00", "end": "17:00"},
        "wednesday": {"enabled": true, "start": "09:00", "end": "17:00"},
        "thursday": {"enabled": true, "start": "09:00", "end": "17:00"},
        "friday": {"enabled": true, "start": "09:00", "end": "17:00"},
        "saturday": {"enabled": true, "start": "10:00", "end": "14:00"},
        "sunday": {"enabled": false, "start": "08:00", "end": "18:00"}
    },
    "timezone": "Africa/Dar_es_Salaam",
    "auto_offline": true
}
```

---

### Check Working Hours Status
```http
GET /api/agent/working-hours/status
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "within_working_hours": true,
    "current_time": "14:30",
    "timezone": "Africa/Dar_es_Salaam",
    "today": "friday",
    "message": "You are within your working hours"
}
```

---

## 🗺️ Navigation & Directions

### Get Directions to Customer (Agent)
```http
GET /api/agent/requests/{id}/directions
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "origin": {
        "latitude": -6.8000,
        "longitude": 39.2100
    },
    "destination": {
        "latitude": -6.7924,
        "longitude": 39.2083,
        "address": "Kinondoni, Dar es Salaam"
    },
    "google_maps_url": "https://www.google.com/maps/dir/-6.8000,39.2100/-6.7924,39.2083",
    "navigation_intent": {
        "android": "google.navigation:q=-6.7924,39.2083",
        "ios": "comgooglemaps://?daddr=-6.7924,39.2083&directionsmode=driving",
        "web": "https://www.google.com/maps/dir/?api=1&origin=-6.8000,39.2100&destination=-6.7924,39.2083&travelmode=driving"
    }
}
```

> **📱 Mobile Implementation:**
> ```dart
> // Flutter - Open Google Maps
> if (Platform.isAndroid) {
>     launchUrl(Uri.parse(response['navigation_intent']['android']));
> } else if (Platform.isIOS) {
>     launchUrl(Uri.parse(response['navigation_intent']['ios']));
> }
> ```

---

## 💰 Wallet & Earnings

### Get Wallet Balance
```http
GET /api/agent/wallet
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "balance": 150000,
    "pending": 25000,
    "total_earned": 500000,
    "transactions": [
        {
            "id": 1,
            "type": "credit",
            "amount": 800,
            "description": "Earnings from REQ-2024-001",
            "created_at": "2025-12-07T10:00:00.000000Z"
        }
    ]
}
```

---

### Request Withdrawal
```http
POST /api/agent/withdraw
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "amount": 50000,
    "phone": "+255123456789"
}
```

**Success Response (200):**
```json
{
    "message": "Withdrawal request submitted",
    "withdrawal": {
        "id": 1,
        "amount": 50000,
        "status": "pending",
        "phone": "+255123456789"
    },
    "new_balance": 100000
}
```

---

## 🏆 Leaderboard

### Get Leaderboard (Public)
```http
GET /api/leaderboard
```

**Query Parameters:**
| Parameter | Type | Default | Options |
|-----------|------|---------|---------|
| period | string | all | `today`, `week`, `month`, `year`, `all` |

**Success Response (200):**
```json
{
    "leaderboard": [
        {
            "rank": 1,
            "agent_id": 1,
            "name": "Top Agent",
            "avatar": "https://ui-avatars.com/api/?name=Top+Agent",
            "rating": 4.9,
            "completed_jobs": 150,
            "earnings": 450000,
            "score": 95.5,
            "tier": "gold",
            "is_online": true
        },
        {
            "rank": 2,
            "agent_id": 2,
            "name": "Second Agent",
            "avatar": "https://ui-avatars.com/api/?name=Second+Agent",
            "rating": 4.7,
            "completed_jobs": 120,
            "earnings": 380000,
            "score": 88.2,
            "tier": "silver",
            "is_online": false
        }
    ],
    "period": "all"
}
```

---

## 🔔 Notifications

### List Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "notifications": [
        {
            "id": 1,
            "type": "new_request",
            "title": "New Request Available",
            "message": "A customer near you needs a vodacom line",
            "data": {
                "request_id": 5
            },
            "read": false,
            "created_at": "2025-12-07T10:00:00.000000Z"
        }
    ],
    "unread_count": 3
}
```

---

### Mark Notification as Read
```http
POST /api/notifications/{id}/read
Authorization: Bearer {token}
```

---

### Mark All as Read
```http
POST /api/notifications/read-all
Authorization: Bearer {token}
```

---

## 🆘 Support

### List Support Tickets
```http
GET /api/support/tickets
Authorization: Bearer {token}
```

---

### Create Support Ticket
```http
POST /api/support/tickets
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "subject": "Payment Issue",
    "message": "I made a payment but it wasn't confirmed...",
    "priority": "high"
}
```

| Field | Type | Required | Options |
|-------|------|----------|---------|
| subject | string | Yes | - |
| message | string | Yes | - |
| priority | string | No | `low`, `medium`, `high` |

---

### View Ticket
```http
GET /api/support/tickets/{id}
Authorization: Bearer {token}
```

---

### Reply to Ticket
```http
POST /api/support/tickets/{id}/reply
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "message": "Thanks for the update. The issue is now resolved."
}
```

---

## ⚙️ Settings

### Get Language Preference
```http
GET /api/settings/language
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "locale": "sw"
}
```

---

### Update Language
```http
PUT /api/settings/language
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "locale": "en"
}
```

| Locale | Language |
|--------|----------|
| sw | Swahili (Kiswahili) |
| en | English |

---

## ❌ Error Handling

### Standard Error Response
```json
{
    "message": "Error description",
    "errors": {
        "field_name": [
            "Validation error message"
        ]
    }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Validation failed |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - No permission |
| 404 | Not Found |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

---

## 📲 Mobile Implementation Guide

### 1. Authentication Flow

```dart
// Flutter Example
class AuthService {
  static const String baseUrl = 'https://your-domain.com/api';
  
  Future<User> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      // Store token securely
      await secureStorage.write(key: 'token', value: data['token']);
      return User.fromJson(data['user']);
    }
    throw Exception('Login failed');
  }
}
```

### 2. Location Tracking

```dart
// Flutter - Using geolocator package
import 'package:geolocator/geolocator.dart';

class LocationService {
  Future<Position> getCurrentLocation() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw Exception('Location services disabled');
    }
    
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    
    return await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
    );
  }
  
  // Real-time location updates
  Stream<Position> getLocationStream() {
    return Geolocator.getPositionStream(
      locationSettings: LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 10, // meters
      ),
    );
  }
}
```

### 3. Real-time Chat Polling

```dart
// Poll for new messages every 5 seconds
class ChatService {
  Timer? _pollingTimer;
  
  void startPolling(int lineRequestId) {
    _pollingTimer = Timer.periodic(Duration(seconds: 5), (_) async {
      final messages = await fetchMessages(lineRequestId);
      // Update UI with new messages
    });
  }
  
  void stopPolling() {
    _pollingTimer?.cancel();
  }
}
```

### 4. Push Notifications (Firebase)

```dart
// Firebase Cloud Messaging setup
import 'package:firebase_messaging/firebase_messaging.dart';

class NotificationService {
  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  
  Future<void> initialize() async {
    // Request permission
    await _fcm.requestPermission();
    
    // Get FCM token and send to server
    final token = await _fcm.getToken();
    await sendTokenToServer(token);
    
    // Handle foreground messages
    FirebaseMessaging.onMessage.listen((message) {
      showLocalNotification(message);
    });
  }
}
```

### 5. Opening Maps for Navigation

```dart
import 'package:url_launcher/url_launcher.dart';

void openNavigation(Map<String, dynamic> directions) {
  String url;
  
  if (Platform.isAndroid) {
    url = directions['navigation_intent']['android'];
  } else if (Platform.isIOS) {
    url = directions['navigation_intent']['ios'];
  } else {
    url = directions['navigation_intent']['web'];
  }
  
  launchUrl(Uri.parse(url));
}
```

---

## 🔒 Rate Limiting

| Endpoint Type | Limit |
|---------------|-------|
| Authentication (login/register) | 5 requests/minute |
| Other API endpoints | 60 requests/minute |

When rate limited, you'll receive:
```json
{
    "message": "Too Many Attempts.",
    "retry_after": 60
}
```

---

## 📞 Contact & Support

For API issues or questions:
- **Email:** support@skylaini.co.tz
- **Phone:** +255 123 456 789

---

> **Note:** Always use HTTPS in production. Store tokens securely using platform-specific secure storage solutions.
