# 📱 Sky Laini Mobile App Development Guide

## Project Overview

**App Name:** Sky Laini  
**Purpose:** On-demand SIM card registration service connecting customers with verified agents  
**Platform:** Flutter (iOS & Android)  
**Backend:** Laravel REST API  
**Base URL:** `https://your-domain.com/api`

---

## 🎨 Design System

### Brand Colors
```
Primary:        #1E3A8A (Deep Blue)
Primary Light:  #3B82F6 (Sky Blue)
Secondary:      #F59E0B (Amber/Gold)
Success:        #10B981 (Green)
Error:          #EF4444 (Red)
Warning:        #F59E0B (Amber)
Background:     #F8FAFC (Light Gray)
Surface:        #FFFFFF (White)
Text Primary:   #1F2937 (Dark Gray)
Text Secondary: #6B7280 (Gray)
```

### Typography
```
Headings:   Inter Bold (24-32sp)
Subheadings: Inter SemiBold (18-20sp)
Body:       Inter Regular (14-16sp)
Caption:    Inter Regular (12sp)
```

### Spacing System
```
xs: 4dp  | sm: 8dp  | md: 16dp
lg: 24dp | xl: 32dp | 2xl: 48dp
```

---

## 👥 User Types & Flows

### 1. Customer Flow
```
Splash → Onboarding → Login/Register → Dashboard
                                           ↓
                            ┌──────────────┴──────────────┐
                            ↓                              ↓
                    Request Laini                    View History
                            ↓                              ↓
                    Select Network              Request Details
                            ↓                              ↓
                    Confirm Location                  Track Agent
                            ↓                              ↓
                    Wait for Agent                   Chat/Rate
                            ↓
                    Pay via M-Pesa
                            ↓
                    Get Confirmation Code
                            ↓
                    Rate Agent
```

### 2. Agent Flow
```
Splash → Onboarding → Login/Register → Document Upload
                                              ↓
                                    Wait for Verification
                                              ↓
                                        Dashboard
                                              ↓
                    ┌─────────────┬───────────┴───────────┬─────────────┐
                    ↓             ↓                       ↓             ↓
              Available      My Requests              Earnings      Profile
                Gigs              ↓                       ↓             ↓
                  ↓          View Details          Wallet/Withdraw  Settings
            Accept Gig            ↓
                  ↓          Navigation
            Wait Payment          ↓
                  ↓          Complete Job
            Enter Code            ↓
                  ↓          Get Paid
              Earn $$$
```

---

## 📱 Screen Specifications

### 1. SPLASH SCREEN
**Duration:** 2-3 seconds with animation

**Elements:**
- App logo (centered, animated)
- App name "Sky Laini"
- Tagline: "Laini yako, Mahali popote"
- Loading indicator

**API Call:** `GET /api/app/info` (check maintenance mode)

---

### 2. ONBOARDING SCREENS (3 slides)

**Slide 1: Welcome**
- Icon: SIM card illustration
- Title: "Karibu Sky Laini"
- Description: "Pata laini yako haraka na rahisi"

**Slide 2: How It Works**  
- Icon: Map pin with agent
- Title: "Wakala Karibu Nawe"
- Description: "Agent atakuja mahali ulipo"

**Slide 3: Payment**
- Icon: Mobile money illustration
- Title: "Lipa kwa M-Pesa"
- Description: "Malipo salama kupitia simu yako"

**Actions:** Skip, Next, Get Started

---

### 3. LOGIN SCREEN

**Elements:**
- Logo (top)
- Email input field
- Password input field (with show/hide toggle)
- "Forgot Password?" link
- Login button (primary)
- Divider "au"
- "Huna akaunti? Jisajili" link
- Language toggle (SW/EN)

**API:** `POST /api/login`
```json
Request:
{
  "email": "user@example.com",
  "password": "password123"
}

Response:
{
  "user": {...},
  "token": "bearer-token-here"
}
```

---

### 4. REGISTRATION SCREEN

**Elements:**
- Full Name input
- Email input
- Phone Number input (with +255 prefix)
- Password input
- Confirm Password input
- Role selector (Customer / Agent tabs)
- Terms & Conditions checkbox
- Register button
- "Una akaunti? Ingia" link

**API:** `POST /api/register`
```json
Request:
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "0712345678",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer" // or "agent"
}
```

---

### 5. CUSTOMER DASHBOARD

**Header:**
- Greeting: "Habari, [Name]" with time-based greeting
- Profile avatar (tap to go to profile)
- Notifications bell with badge count

**Quick Stats Cards (horizontal scroll):**
- Active Requests count
- Completed count
- Total Spent

**Main Action Button (Large, Centered):**
- "Omba Laini Mpya" with + icon

**Active Requests Section:**
- List of ongoing requests with status badges
- Each item shows: Network, Status, Agent name (if assigned), Time

**Bottom Navigation:**
- Home | History | Chat | Profile

**API:** `GET /api/customer/dashboard`

---

### 6. REQUEST LAINI SCREEN (Customer)

**Step 1: Select Network**
- Grid of network logos (Airtel, Vodacom, Tigo, Halotel, Zantel)
- Tap to select (highlight selected)

**Step 2: Confirm Location**
- Map view showing current location
- "Mahali pangu" marker
- Address text display
- "Badilisha mahali" button
- Auto-detect location on load

**Step 3: Contact Info**
- Phone number input (pre-filled from profile)
- Optional: Additional notes

**Step 4: Review & Submit**
- Summary card showing:
  - Network selected
  - Location address
  - Price (from `/api/settings/price`)
- Submit button

**API:** `POST /api/line-requests`
```json
{
  "line_type": "vodacom",
  "customer_latitude": -6.7924,
  "customer_longitude": 39.2083,
  "customer_address": "Kinondoni, Dar es Salaam",
  "customer_phone": "0712345678"
}
```

---

### 7. REQUEST TRACKING SCREEN (Customer)

**Header:**
- Request number
- Back button

**Status Timeline (vertical):**
- ✓ Request Created (timestamp)
- ✓ Agent Assigned (agent name)
- ○ Payment Pending / ✓ Payment Complete
- ○ Agent on the way
- ○ Completed

**Map Section:**
- Shows agent location (if paid)
- Shows customer location
- Route line between them (optional)

**Agent Card (if assigned):**
- Agent photo/avatar
- Agent name
- Rating (stars)
- Phone call button
- Chat button

**Payment Section (if pending):**
- Amount display
- "Lipa Sasa" button
- Payment instructions

**Confirmation Code Card (after payment):**
- Large code display
- Icon indicating to share with agent
- "Nakili Code" button

**API Calls:**
- `GET /api/line-requests/{id}`
- `GET /api/line-requests/{id}/payment-status`
- `GET /api/tracking/{id}` (real-time agent location)

---

### 8. AGENT DASHBOARD

**Header:**
- Profile section with avatar
- Online/Offline toggle switch (prominent)
- Earnings display (today)

**Stats Row:**
- Today's Jobs | Total Earnings | Rating

**Available Gigs Section:**
- Pull to refresh
- List of pending requests nearby
- Each shows: Network, Distance, Time posted
- "Accept" button on each

**My Active Jobs Section:**
- Current accepted jobs
- Status indicators

**Quick Actions:**
- View Wallet
- Working Hours
- Documents

**Bottom Navigation:**
- Dashboard | Gigs | Jobs | Earnings | Profile

**API:** `GET /api/agent/dashboard`

---

### 9. GIG DETAILS SCREEN (Agent)

**Map Header:**
- Customer location pin
- Distance from agent

**Request Details Card:**
- Network type (with logo)
- Customer name
- Time posted
- Price amount

**Location Section:**
- Full address
- "Navigate" button (opens Google Maps)

**Action Buttons:**
- "Kubali Kazi" (Accept) - Primary
- "Kataa" (Reject) - Secondary/Outline

**API:** `POST /api/agent/requests/{id}/accept`

---

### 10. ACTIVE JOB SCREEN (Agent)

**Header:**
- Job number
- Timer (time since accepted)

**Customer Info:**
- Name
- Phone (tap to call)
- Chat button

**Location & Navigation:**
- Map preview
- "Anza Navigation" button
- Address text

**Payment Status Card:**
- Status: Pending/Paid
- If pending: "Tuma Ombi Tena" (retry) button
- Attempts counter (e.g., "Jaribio 1/3")

**Completion Section (when paid):**
- "Ingiza Code" input field
- Submit button
- Instructions text

**API Calls:**
- `GET /api/agent/requests/{id}/payment-status`
- `POST /api/agent/requests/{id}/complete`
- `POST /api/agent/requests/{id}/retry-payment`

---

### 11. WALLET SCREEN (Agent)

**Balance Card:**
- Large balance display
- Currency: TZS
- "Toa Pesa" (Withdraw) button

**Quick Stats:**
- Today's earnings
- This week
- This month

**Transaction History:**
- List with filters (All, Credits, Debits)
- Each: Type icon, Description, Amount, Date

**API:** `GET /api/agent/wallet`

---

### 12. WITHDRAWAL SCREEN (Agent)

**Amount Input:**
- Large number input
- Quick amount buttons (5000, 10000, 20000, 50000)
- Available balance display

**Payment Method:**
- Radio: Mobile Money / Bank Transfer
- Account number input
- Account name input

**Summary & Submit:**
- Amount after fees (if any)
- Submit button

**API:** `POST /api/agent/withdraw`

---

### 13. CHAT SCREEN

**Conversations List:**
- Profile photo
- Name
- Last message preview
- Time
- Unread badge

**Chat View:**
- WhatsApp-style bubbles
- Sender alignment (left/right)
- Timestamps
- Read receipts
- Image/file attachments support
- Message input with send button

**API Calls:**
- `GET /api/chat/conversations`
- `GET /api/chat/{lineRequest}`
- `POST /api/chat/{lineRequest}`

---

### 14. PROFILE SCREEN

**Profile Header:**
- Avatar (editable)
- Name
- Role badge (Customer/Agent)

**Account Section:**
- Edit Profile
- Change Password
- Language (Kiswahili/English)

**Agent-Specific:**
- My Documents
- Working Hours
- Verification Status

**Support:**
- Help & FAQ
- Contact Support
- Report a Problem

**Other:**
- Terms & Conditions
- Privacy Policy
- App Version

**Action:**
- Logout button (red)

---

### 15. SUPPORT/TICKETS SCREEN

**Create Ticket Button**

**Tickets List:**
- Subject
- Status badge (Open/Closed)
- Date
- Last message preview

**Ticket Detail:**
- Subject header
- Message thread
- Reply input
- Close ticket option

**API:** `/api/support/tickets/*`

---

## 🔔 Push Notifications

### Customer Notifications
| Event | Title | Body |
|-------|-------|------|
| Agent Assigned | "Agent Amekubali!" | "[Name] anakuja kwako" |
| Payment Needed | "Lipa Sasa" | "Baki TZS [amount] kulipa" |
| Agent Nearby | "Agent Karibu!" | "[Name] yuko dakika 5 kutoka kwako" |
| Job Complete | "Asante!" | "Laini yako iko tayari. Kadiria agent" |

### Agent Notifications
| Event | Title | Body |
|-------|-------|------|
| New Request | "Kazi Mpya!" | "Mteja anahitaji [network] - [distance]km" |
| Payment Received | "Malipo Yamepokelewa!" | "TZS [amount] - Nenda kwa mteja" |
| Review Received | "Rating Mpya" | "[Name] amekupa nyota [count]" |

---

## 🔄 Real-time Features

### WebSocket/Pusher Events
1. **Agent Location Updates** - Every 10 seconds when on active job
2. **Chat Messages** - Instant delivery
3. **Request Status Changes** - Immediate UI updates
4. **Payment Status** - Poll every 5 seconds until paid

---

## 📡 API Integration Checklist

### Authentication
- [x] Login (`POST /api/login`)
- [x] Register (`POST /api/register`)
- [x] Logout (`POST /api/logout`)
- [x] Forgot Password (`POST /api/forgot-password`)
- [x] Change Password (`PUT /api/password`)

### Customer
- [x] Dashboard (`GET /api/customer/dashboard`)
- [x] Profile (`GET/PUT /api/customer/profile`)
- [x] Create Request (`POST /api/line-requests`)
- [x] View Requests (`GET /api/line-requests`)
- [x] Track Agent (`GET /api/tracking/{id}`)
- [x] Pay (`POST /api/line-requests/{id}/pay`)
- [x] Rate Agent (`POST /api/line-requests/{id}/rate`)

### Agent
- [x] Dashboard (`GET /api/agent/dashboard`)
- [x] Toggle Status (`POST /api/agent/toggle-status`)
- [x] Available Gigs (`GET /api/agent/gigs`)
- [x] Accept/Reject (`POST /api/agent/requests/{id}/accept`)
- [x] Complete Job (`POST /api/agent/requests/{id}/complete`)
- [x] Wallet (`GET /api/agent/wallet`)
- [x] Withdraw (`POST /api/agent/withdraw`)
- [x] Documents (`GET/POST /api/agent/documents`)
- [x] Working Hours (`GET/PUT /api/agent/working-hours`)

### Shared
- [x] Chat (`/api/chat/*`)
- [x] Notifications (`/api/notifications/*`)
- [x] Support Tickets (`/api/support/*`)
- [x] Invoices (`/api/invoices/*`)

---

## 🛡️ Security Requirements

1. **Token Storage:** Use Flutter Secure Storage
2. **API Calls:** Always include `Authorization: Bearer {token}`
3. **SSL Pinning:** Implement for production
4. **Biometric Auth:** Optional for quick login
5. **Session Timeout:** 30 days, refresh on activity

---

## 📦 Recommended Flutter Packages

```yaml
dependencies:
  # State Management
  flutter_bloc: ^8.1.0
  # or provider: ^6.0.0
  
  # API & Network
  dio: ^5.0.0
  retrofit: ^4.0.0
  
  # Local Storage
  shared_preferences: ^2.2.0
  flutter_secure_storage: ^9.0.0
  
  # Location & Maps
  google_maps_flutter: ^2.5.0
  geolocator: ^10.0.0
  
  # UI Components
  flutter_svg: ^2.0.0
  cached_network_image: ^3.3.0
  shimmer: ^3.0.0
  
  # Forms & Validation
  flutter_form_builder: ^9.0.0
  
  # Notifications
  firebase_messaging: ^14.0.0
  flutter_local_notifications: ^16.0.0
  
  # Utilities
  intl: ^0.18.0
  url_launcher: ^6.2.0
```

---

## 📁 Suggested Project Structure

```
lib/
├── main.dart
├── app/
│   ├── app.dart
│   ├── routes.dart
│   └── theme.dart
├── core/
│   ├── api/
│   │   ├── api_client.dart
│   │   ├── endpoints.dart
│   │   └── interceptors.dart
│   ├── constants/
│   ├── utils/
│   └── widgets/
├── features/
│   ├── auth/
│   │   ├── data/
│   │   ├── domain/
│   │   └── presentation/
│   ├── customer/
│   │   ├── dashboard/
│   │   ├── request/
│   │   └── tracking/
│   ├── agent/
│   │   ├── dashboard/
│   │   ├── gigs/
│   │   ├── wallet/
│   │   └── documents/
│   ├── chat/
│   ├── profile/
│   └── support/
└── l10n/
    ├── app_en.arb
    └── app_sw.arb
```

---

## 🌍 Localization

Support two languages:
- **Swahili (sw)** - Default
- **English (en)**

Store preference in: `GET/PUT /api/settings/language`

---

## ✅ Development Milestones

### Phase 1: Foundation (Week 1-2)
- [ ] Project setup & architecture
- [ ] Authentication flows
- [ ] API client setup
- [ ] Basic theming

### Phase 2: Customer Features (Week 3-4)
- [ ] Customer dashboard
- [ ] Request creation flow
- [ ] Request tracking
- [ ] Payment integration

### Phase 3: Agent Features (Week 5-6)
- [ ] Agent dashboard
- [ ] Gig acceptance flow
- [ ] Job completion
- [ ] Wallet & withdrawals

### Phase 4: Shared Features (Week 7)
- [ ] Chat system
- [ ] Notifications
- [ ] Support tickets
- [ ] Profile management

### Phase 5: Polish (Week 8)
- [ ] Testing & bug fixes
- [ ] Performance optimization
- [ ] App store preparation

---

## 📞 Contact & Support

**Backend API Base:** `https://your-domain.com/api`  
**Documentation:** This file  
**Last Updated:** December 2024

---

*Happy Coding! 🚀*
