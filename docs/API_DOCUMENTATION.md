# 📡 Sky Laini API Documentation

**Version:** 1.0.0  
**Base URL:** `https://your-domain.com/api`  
**Authentication:** Bearer Token (Laravel Sanctum)

---

## 🔐 Authentication

### Register User
```http
POST /api/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "0712345678",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"  // or "agent"
}
```

**Response (201):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0712345678",
    "role": "customer"
  },
  "token": "1|abc123..."
}
```

---

### Login
```http
POST /api/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer"
  },
  "token": "1|abc123..."
}
```

---

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### Forgot Password
```http
POST /api/forgot-password
```

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

---

### Change Password
```http
PUT /api/password
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

---

## 👤 User Profile

### Get Profile
```http
GET /api/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0712345678",
    "customer": {...},
    "agent": {...}
  },
  "role": "customer"
}
```

---

### Update Profile
```http
PUT /api/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "John Updated",
  "phone": "0712345679"
}
```

---

## 🛒 Customer Endpoints

### Get Dashboard
```http
GET /api/customer/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "customer": {...},
  "active_requests": [...],
  "stats": {
    "total_requests": 10,
    "active_count": 1,
    "completed_count": 9,
    "total_spent": 9000
  }
}
```

---

### Get Customer Profile
```http
GET /api/customer/profile
Authorization: Bearer {token}
```

---

### Update Customer Profile
```http
PUT /api/customer/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "ward": "Kinondoni",
  "district": "Kinondoni",
  "region": "Dar es Salaam"
}
```

---

### Update Customer Location
```http
POST /api/customer/location
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083
}
```

---

## 📋 Line Requests

### List Requests (Customer)
```http
GET /api/line-requests
Authorization: Bearer {token}
```

**Response (paginated):**
```json
{
  "data": [
    {
      "id": 1,
      "request_number": "REQ-ABC12345",
      "line_type": "vodacom",
      "status": "pending",
      "payment_status": null,
      "customer_latitude": -6.7924,
      "customer_longitude": 39.2083,
      "agent": {...}
    }
  ],
  "current_page": 1,
  "last_page": 1
}
```

---

### Create Request
```http
POST /api/line-requests
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "line_type": "vodacom",  // airtel, vodacom, halotel, tigo, zantel
  "customer_latitude": -6.7924,
  "customer_longitude": 39.2083,
  "customer_address": "Kinondoni, Dar es Salaam",
  "customer_phone": "0712345678"
}
```

**Response (201):**
```json
{
  "id": 1,
  "request_number": "REQ-ABC12345",
  "line_type": "vodacom",
  "status": "pending",
  "customer": {...},
  "agent": {...}
}
```

---

### Get Single Request
```http
GET /api/line-requests/{id}
Authorization: Bearer {token}
```

---

### Cancel Request
```http
POST /api/line-requests/{id}/cancel
Authorization: Bearer {token}
```

---

### Rate Agent
```http
POST /api/line-requests/{id}/rate
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "rating": 5,
  "review": "Huduma nzuri sana!"
}
```

---

### Initiate Payment
```http
POST /api/line-requests/{id}/pay
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Payment initiated. Please check your phone.",
  "order_id": "ZP123456"
}
```

---

### Check Payment Status
```http
GET /api/line-requests/{id}/payment-status
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "paid",
  "confirmation_code": "ABC123"
}
```

---

### Get Agent Location
```http
GET /api/line-requests/{id}/agent-location
Authorization: Bearer {token}
```

**Response:**
```json
{
  "agent": {
    "name": "Agent Name",
    "phone": "0712345678",
    "latitude": -6.7900,
    "longitude": 39.2100,
    "is_online": true,
    "rating": 4.5
  },
  "customer_location": {
    "latitude": -6.7924,
    "longitude": 39.2083
  }
}
```

---

## 📍 Tracking

### Get Agent Location for Request
```http
GET /api/tracking/{lineRequest}
Authorization: Bearer {token}
```

---

### Update Customer Location
```http
POST /api/tracking/location
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083
}
```

---

## 🚗 Agent Endpoints

### Get Dashboard
```http
GET /api/agent/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "agent": {
    "id": 1,
    "is_online": true,
    "rating": 4.5,
    "wallet": {...},
    "user": {...}
  },
  "stats": {
    "pending_requests": 2,
    "active_requests": 1,
    "completed_today": 5,
    "earnings_today": 5000,
    "total_earnings": 150000,
    "total_completed": 100,
    "rating": 4.5,
    "is_online": true,
    "is_verified": true
  }
}
```

---

### Get Agent Profile
```http
GET /api/agent/profile
Authorization: Bearer {token}
```

---

### Update Agent Profile
```http
PUT /api/agent/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "is_online": true,
  "is_available": true
}
```

---

### Update Agent Location
```http
POST /api/agent/location
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "latitude": -6.7900,
  "longitude": 39.2100
}
```

---

### Toggle Online Status
```http
POST /api/agent/toggle-status
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "is_online": true
}
```

---

### Get Available Gigs
```http
GET /api/agent/gigs
Authorization: Bearer {token}
```

**Response (paginated):**
```json
{
  "data": [
    {
      "id": 1,
      "request_number": "REQ-ABC12345",
      "line_type": "vodacom",
      "status": "pending",
      "customer_latitude": -6.7924,
      "customer_longitude": 39.2083,
      "customer": {
        "user": {
          "name": "Customer Name"
        }
      }
    }
  ]
}
```

---

### Get Gigs Count
```http
GET /api/agent/gigs/count
Authorization: Bearer {token}
```

**Response:**
```json
{
  "count": 5
}
```

---

### Get Agent Requests
```http
GET /api/agent/requests
Authorization: Bearer {token}
```

---

### Get Single Request
```http
GET /api/agent/requests/{id}
Authorization: Bearer {token}
```

---

### Accept Request
```http
POST /api/agent/requests/{id}/accept
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Request accepted. Payment request sent to customer.",
  "payment_initiated": true
}
```

---

### Reject Request
```http
POST /api/agent/requests/{id}/reject
Authorization: Bearer {token}
```

---

### Release Request
```http
POST /api/agent/requests/{id}/release
Authorization: Bearer {token}
```

---

### Retry Payment
```http
POST /api/agent/requests/{id}/retry-payment
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Payment request resent successfully. Attempt 2/3",
  "released": false
}
```

---

### Complete Job
```http
POST /api/agent/requests/{id}/complete
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "code": "ABC123"
}
```

**Response:**
```json
{
  "message": "Job completed successfully!"
}
```

---

### Get Navigation Directions
```http
GET /api/agent/requests/{id}/directions
Authorization: Bearer {token}
```

**Response:**
```json
{
  "origin": {
    "latitude": -6.7900,
    "longitude": 39.2100
  },
  "destination": {
    "latitude": -6.7924,
    "longitude": 39.2083,
    "address": "Kinondoni, Dar es Salaam"
  },
  "google_maps_url": "https://www.google.com/maps/dir/...",
  "navigation_intent": {
    "android": "google.navigation:q=-6.7924,39.2083",
    "ios": "comgooglemaps://?daddr=-6.7924,39.2083&directionsmode=driving",
    "web": "https://www.google.com/maps/dir/?api=1&..."
  }
}
```

---

## 💰 Wallet

### Get Wallet
```http
GET /api/agent/wallet
Authorization: Bearer {token}
```

**Response:**
```json
{
  "wallet": {
    "id": 1,
    "balance": 50000
  },
  "transactions": {
    "data": [
      {
        "id": 1,
        "transaction_type": "credit",
        "amount": 800,
        "balance_before": 49200,
        "balance_after": 50000,
        "description": "Earnings for Request #REQ-ABC12345",
        "created_at": "2024-12-08T10:30:00Z"
      }
    ]
  }
}
```

---

### Request Withdrawal
```http
POST /api/agent/withdraw
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "amount": 10000,
  "method": "mobile_money",  // or "bank"
  "account_number": "0712345678",
  "account_name": "John Doe"
}
```

---

## 📄 Documents

### Get Documents
```http
GET /api/agent/documents
Authorization: Bearer {token}
```

---

### Upload Documents
```http
POST /api/agent/documents
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form Data:**
- `nida_number`: string
- `id_document`: file (jpeg, png, jpg, pdf)
- `passport_photo`: file (image)

---

## ⏰ Working Hours

### Get Working Hours
```http
GET /api/agent/working-hours
Authorization: Bearer {token}
```

**Response:**
```json
{
  "working_hours": {
    "monday": {"enabled": true, "start": "08:00", "end": "18:00"},
    "tuesday": {"enabled": true, "start": "08:00", "end": "18:00"},
    ...
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
```

---

### Check Status
```http
GET /api/agent/working-hours/status
Authorization: Bearer {token}
```

---

## 💬 Chat

### Get Conversations
```http
GET /api/chat/conversations
Authorization: Bearer {token}
```

---

### Get Unread Count
```http
GET /api/chat/unread-count
Authorization: Bearer {token}
```

**Response:**
```json
{
  "unread_count": 3
}
```

---

### Get Messages
```http
GET /api/chat/{lineRequest}
Authorization: Bearer {token}
```

---

### Send Message
```http
POST /api/chat/{lineRequest}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form Data:**
- `message`: string (required if no attachment)
- `attachment`: file (optional, max 5MB)

---

### Mark as Read
```http
POST /api/chat/{lineRequest}/read
Authorization: Bearer {token}
```

---

## 🔔 Notifications

### Get Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

---

### Mark as Read
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

## 🧾 Invoices

### List Invoices
```http
GET /api/invoices
Authorization: Bearer {token}
```

---

### Get Invoice
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

---

### Print Invoice
```http
GET /api/invoices/{id}/print
Authorization: Bearer {token}
```

---

### Generate Invoice
```http
POST /api/invoices/generate/{lineRequest}
Authorization: Bearer {token}
```

---

## 🎫 Support Tickets

### List Tickets
```http
GET /api/support/tickets
Authorization: Bearer {token}
```

---

### Create Ticket
```http
POST /api/support/tickets
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "subject": "Issue with payment",
  "category": "refund",  // general, refund, complaint
  "message": "I paid but agent didn't come..."
}
```

---

### Get Ticket
```http
GET /api/support/tickets/{id}
Authorization: Bearer {token}
```

---

### Reply to Ticket
```http
POST /api/support/tickets/{id}/reply
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "message": "Here is more information..."
}
```

---

## ⚙️ Settings

### Get Language
```http
GET /api/settings/language
Authorization: Bearer {token}
```

---

### Update Language
```http
PUT /api/settings/language
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "locale": "sw"  // or "en"
}
```

---

## 📊 Public Endpoints

### Get Price
```http
GET /api/settings/price
```

**Response:**
```json
{
  "price": 1000
}
```

---

### Get App Info
```http
GET /api/app/info
```

**Response:**
```json
{
  "app_name": "Sky Laini",
  "version": "1.0.0",
  "min_version": "1.0.0",
  "update_required": false,
  "maintenance_mode": false
}
```

---

### Get Leaderboard
```http
GET /api/leaderboard?period=month&limit=20
```

**Query Parameters:**
- `period`: today, week, month, year, all (default: all)
- `limit`: number (default: 20)

---

## ❌ Error Responses

### 400 Bad Request
```json
{
  "message": "Invalid input data"
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server error occurred"
}
```

---

## 📝 Notes

1. All authenticated routes require `Authorization: Bearer {token}` header
2. All paginated responses include: `data`, `current_page`, `last_page`, `total`
3. Timestamps are in ISO 8601 format (UTC)
4. File uploads use `multipart/form-data`
5. Default content type is `application/json`

---

*Last Updated: December 2024*
