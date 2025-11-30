# 🚀 SKY LAINI - Mobile Line Registration Platform

A comprehensive enterprise platform for mobile line registration services in Tanzania, connecting customers (Msajiliwa Laini) with agents (Msajili wa Laini) for real-time mobile line registration.

## ✨ Features

### User Roles
- **Customer (Msajiliwa Laini)**: Register, request line registration, track agents, receive confirmations
- **Agent (Msajili wa Laini)**: Accept requests, GPS tracking, earn commissions, manage wallet
- **Admin**: Dashboard, agent verification, withdrawal approvals, fraud monitoring

### Core Features
- ✅ **Smart Agent Matching**: Uber-like matching based on distance, rating, and availability
- ✅ **Real-time GPS Tracking**: Live agent location tracking and geofencing
- ✅ **Wallet System**: Commission tracking, withdrawals, tier-based bonuses
- ✅ **Payment Integration**: ZenoPay API integration for payments
- ✅ **Multi-line Support**: Airtel, Vodacom, Halotel, Tigo, Zantel
- ✅ **Rating & Reviews**: Customer rating system for agents
- ✅ **Fraud Detection**: Speed anomalies, location mismatch detection
- ✅ **Notifications**: SMS/USSD/Push notification system

## 🏗️ Architecture

### Database Schema
- **Users**: Authentication with role-based access (Customer/Agent/Admin)
- **Customers**: Customer profiles with location data
- **Agents**: Agent profiles with verification, ratings, and location
- **Line Requests**: Request management with status tracking
- **Wallets**: Agent wallet system with transactions
- **Payments**: Payment processing with ZenoPay integration
- **Ratings**: Customer rating and review system
- **Agent Locations**: Historical GPS tracking data
- **Fraud Alerts**: Fraud detection and monitoring
- **System Settings**: Configurable system parameters

### Services
- **AgentMatchingService**: Smart agent matching algorithm
- **LocationService**: GPS distance calculations and geofencing
- **ZenoPayService**: Payment gateway integration
- **WalletService**: Wallet operations and commission management
- **NotificationService**: SMS/USSD/Push notifications
- **FraudDetectionService**: Fraud detection and alerting

## 📦 Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database in `.env`
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Start development server:
   ```bash
   php artisan serve
   npm run dev
   ```

## 🔧 Configuration

### ZenoPay API
Add to `.env`:
```
ZENOPAY_API_KEY=your_api_key_here
ZENOPAY_BASE_URL=https://api.zenopay.com
```

### Database
Configure your database connection in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sky_laini
DB_USERNAME=root
DB_PASSWORD=
```

## 📡 API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user

### Customer
- `GET /api/customer/profile` - Get customer profile
- `PUT /api/customer/profile` - Update customer profile
- `POST /api/customer/location` - Update customer location

### Line Requests
- `GET /api/line-requests` - List customer's line requests
- `POST /api/line-requests` - Create new line request
- `GET /api/line-requests/{id}` - Get specific line request

## 🛠️ Built With

- **Laravel 12** - PHP Framework
- **Laravel Sanctum** - API Authentication
- **MySQL** - Database
- **Pest** - Testing Framework
- **Laravel Pint** - Code Style

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
