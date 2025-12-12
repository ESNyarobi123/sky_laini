# Flutter FCM Integration Guide

## Complete guide to integrate Firebase Cloud Messaging in Sky Laini Flutter app.

---

## 1. Firebase Setup (Flutter Side)

### pubspec.yaml dependencies

```yaml
dependencies:
  firebase_core: ^3.8.0
  firebase_messaging: ^15.1.5
  flutter_local_notifications: ^18.0.1
```

Run:
```bash
flutter pub get
```

---

## 2. Create FCM Service

Create file: `lib/services/fcm_service.dart`

```dart
import 'dart:convert';
import 'dart:io';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter/material.dart';

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = 
      FlutterLocalNotificationsPlugin();

  String? _fcmToken;
  String? get fcmToken => _fcmToken;

  // Notification channel for Android
  static const AndroidNotificationChannel channel = AndroidNotificationChannel(
    'sky_laini_channel',
    'Sky Laini Notifications',
    description: 'Notifications from Sky Laini app',
    importance: Importance.high,
    playSound: true,
    enableVibration: true,
  );

  /// Initialize FCM service
  Future<void> initialize() async {
    // Request permission
    await _requestPermission();

    // Initialize local notifications
    await _initializeLocalNotifications();

    // Create notification channel (Android)
    await _createNotificationChannel();

    // Get FCM token
    await _getToken();

    // Listen for token refresh
    _messaging.onTokenRefresh.listen(_onTokenRefresh);

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle background message tap
    FirebaseMessaging.onMessageOpenedApp.listen(_handleMessageOpenedApp);

    // Check for initial message (app opened from terminated state)
    RemoteMessage? initialMessage = await _messaging.getInitialMessage();
    if (initialMessage != null) {
      _handleMessageOpenedApp(initialMessage);
    }
  }

  /// Request notification permission
  Future<void> _requestPermission() async {
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      announcement: false,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    debugPrint('FCM Permission: ${settings.authorizationStatus}');
  }

  /// Initialize local notifications
  Future<void> _initializeLocalNotifications() async {
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const DarwinInitializationSettings iosSettings =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTap,
    );
  }

  /// Create notification channel for Android
  Future<void> _createNotificationChannel() async {
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);
  }

  /// Get FCM token
  Future<String?> _getToken() async {
    try {
      // For iOS, get APNS token first
      if (Platform.isIOS) {
        String? apnsToken = await _messaging.getAPNSToken();
        if (apnsToken == null) {
          debugPrint('FCM: Waiting for APNS token...');
          await Future.delayed(const Duration(seconds: 2));
        }
      }

      _fcmToken = await _messaging.getToken();
      debugPrint('FCM Token: $_fcmToken');
      return _fcmToken;
    } catch (e) {
      debugPrint('FCM Error getting token: $e');
      return null;
    }
  }

  /// Handle token refresh
  void _onTokenRefresh(String token) {
    _fcmToken = token;
    debugPrint('FCM Token refreshed: $token');
    // Re-register with backend
    onTokenRefreshCallback?.call(token);
  }

  /// Callback for token refresh (set by app)
  Function(String)? onTokenRefreshCallback;

  /// Handle foreground message
  void _handleForegroundMessage(RemoteMessage message) {
    debugPrint('FCM Foreground message: ${message.messageId}');

    RemoteNotification? notification = message.notification;
    AndroidNotification? android = message.notification?.android;

    // Show local notification
    if (notification != null) {
      _localNotifications.show(
        notification.hashCode,
        notification.title,
        notification.body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            channel.id,
            channel.name,
            channelDescription: channel.description,
            icon: '@mipmap/ic_launcher',
            importance: Importance.high,
            priority: Priority.high,
            playSound: true,
          ),
          iOS: const DarwinNotificationDetails(
            presentAlert: true,
            presentBadge: true,
            presentSound: true,
          ),
        ),
        payload: jsonEncode(message.data),
      );
    }

    // Trigger callback
    onMessageReceivedCallback?.call(message);
  }

  /// Callback for message received (set by app)
  Function(RemoteMessage)? onMessageReceivedCallback;

  /// Handle message tap (background or terminated)
  void _handleMessageOpenedApp(RemoteMessage message) {
    debugPrint('FCM Message opened app: ${message.data}');
    onMessageTapCallback?.call(message);
  }

  /// Callback for message tap (set by app)
  Function(RemoteMessage)? onMessageTapCallback;

  /// Handle local notification tap
  void _onNotificationTap(NotificationResponse response) {
    debugPrint('Local notification tapped: ${response.payload}');
    if (response.payload != null) {
      try {
        Map<String, dynamic> data = jsonDecode(response.payload!);
        onNotificationTapCallback?.call(data);
      } catch (e) {
        debugPrint('Error parsing notification payload: $e');
      }
    }
  }

  /// Callback for notification tap (set by app)
  Function(Map<String, dynamic>)? onNotificationTapCallback;

  /// Get current token (for registration)
  Future<String?> getToken() async {
    if (_fcmToken != null) return _fcmToken;
    return await _getToken();
  }

  /// Subscribe to topic
  Future<void> subscribeToTopic(String topic) async {
    await _messaging.subscribeToTopic(topic);
    debugPrint('FCM: Subscribed to topic: $topic');
  }

  /// Unsubscribe from topic
  Future<void> unsubscribeFromTopic(String topic) async {
    await _messaging.unsubscribeFromTopic(topic);
    debugPrint('FCM: Unsubscribed from topic: $topic');
  }

  /// Clear all notifications
  Future<void> clearAllNotifications() async {
    await _localNotifications.cancelAll();
  }

  /// Get device type
  String get deviceType => Platform.isAndroid ? 'android' : 'ios';
}

// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('FCM Background message: ${message.messageId}');
}
```

---

## 3. Create FCM Provider (State Management)

Create file: `lib/providers/fcm_provider.dart`

```dart
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import '../services/fcm_service.dart';
import '../services/api_service.dart';

class FcmProvider extends ChangeNotifier {
  final FcmService _fcmService = FcmService();
  final ApiService _apiService;

  bool _isInitialized = false;
  bool _isRegistered = false;
  String? _fcmToken;

  bool get isInitialized => _isInitialized;
  bool get isRegistered => _isRegistered;
  String? get fcmToken => _fcmToken;

  FcmProvider(this._apiService);

  /// Initialize FCM
  Future<void> initialize() async {
    if (_isInitialized) return;

    await _fcmService.initialize();
    _fcmToken = await _fcmService.getToken();
    _isInitialized = true;

    // Set callbacks
    _fcmService.onTokenRefreshCallback = _onTokenRefresh;
    _fcmService.onMessageReceivedCallback = _onMessageReceived;
    _fcmService.onMessageTapCallback = _onMessageTap;
    _fcmService.onNotificationTapCallback = _onNotificationTap;

    notifyListeners();
  }

  /// Register token with backend
  Future<bool> registerToken() async {
    if (_fcmToken == null) {
      _fcmToken = await _fcmService.getToken();
    }

    if (_fcmToken == null) {
      debugPrint('FCM: No token available');
      return false;
    }

    try {
      final response = await _apiService.post('/fcm/token', {
        'token': _fcmToken,
        'device_type': _fcmService.deviceType,
      });

      _isRegistered = response['success'] == true;
      notifyListeners();

      debugPrint('FCM Token registered: $_isRegistered');
      return _isRegistered;
    } catch (e) {
      debugPrint('FCM Token registration failed: $e');
      return false;
    }
  }

  /// Remove token from backend (on logout)
  Future<void> removeToken() async {
    try {
      await _apiService.delete('/fcm/token');
      _isRegistered = false;
      notifyListeners();
      debugPrint('FCM Token removed');
    } catch (e) {
      debugPrint('FCM Token removal failed: $e');
    }
  }

  /// Token refresh callback
  void _onTokenRefresh(String token) {
    _fcmToken = token;
    registerToken(); // Re-register with new token
  }

  /// Message received callback
  void _onMessageReceived(RemoteMessage message) {
    debugPrint('FCM Message received: ${message.notification?.title}');
    onMessageReceived?.call(message);
  }

  /// Message tap callback (navigation handler set by app)
  Function(RemoteMessage)? onMessageTap;

  void _onMessageTap(RemoteMessage message) {
    onMessageTap?.call(message);
  }

  /// Notification tap callback
  Function(Map<String, dynamic>)? onNotificationTap;

  void _onNotificationTap(Map<String, dynamic> data) {
    onNotificationTap?.call(data);
  }

  /// Message received callback (for refresh)
  Function(RemoteMessage)? onMessageReceived;

  /// Send test notification
  Future<bool> sendTestNotification() async {
    try {
      final response = await _apiService.post('/fcm/test', {});
      return response['success'] == true;
    } catch (e) {
      debugPrint('Test notification failed: $e');
      return false;
    }
  }

  /// Check token status
  Future<Map<String, dynamic>?> checkStatus() async {
    try {
      return await _apiService.get('/fcm/status');
    } catch (e) {
      debugPrint('FCM Status check failed: $e');
      return null;
    }
  }
}
```

---

## 4. Initialize in main.dart

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'services/fcm_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp();
  
  // Set background message handler
  FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
  
  runApp(MyApp());
}
```

---

## 5. Register Token on Login

In your login flow, after successful authentication:

```dart
// After login success
final fcmProvider = context.read<FcmProvider>();
await fcmProvider.initialize();
await fcmProvider.registerToken();
```

---

## 6. Remove Token on Logout

```dart
// Before logout
final fcmProvider = context.read<FcmProvider>();
await fcmProvider.removeToken();

// Then logout
await authProvider.logout();
```

---

## 7. Handle Notification Taps (Navigation)

In your main app widget:

```dart
@override
void initState() {
  super.initState();
  _setupNotificationHandlers();
}

void _setupNotificationHandlers() {
  final fcmProvider = context.read<FcmProvider>();

  // Handle notification tap
  fcmProvider.onNotificationTap = (data) {
    _handleNotificationNavigation(data);
  };

  fcmProvider.onMessageTap = (message) {
    _handleNotificationNavigation(message.data);
  };
}

void _handleNotificationNavigation(Map<String, dynamic> data) {
  final type = data['type'];
  final requestId = data['line_request_id'];

  switch (type) {
    case 'new_request':
      Navigator.pushNamed(context, '/agent/gigs');
      break;
    case 'order_created':
    case 'agent_accepted':
    case 'payment_received':
    case 'job_completed':
      if (requestId != null) {
        Navigator.pushNamed(
          context, 
          '/request-details',
          arguments: {'id': int.parse(requestId)},
        );
      }
      break;
    case 'admin_broadcast':
      Navigator.pushNamed(context, '/notifications');
      break;
    default:
      Navigator.pushNamed(context, '/notifications');
  }
}
```

---

## 8. Android Configuration

### android/app/build.gradle

Add:
```gradle
android {
    defaultConfig {
        // ...
        multiDexEnabled true
    }
}
```

### android/app/src/main/AndroidManifest.xml

Add inside `<application>`:
```xml
<meta-data
    android:name="com.google.firebase.messaging.default_notification_channel_id"
    android:value="sky_laini_channel" />

<meta-data
    android:name="com.google.firebase.messaging.default_notification_icon"
    android:resource="@mipmap/ic_launcher" />

<meta-data
    android:name="com.google.firebase.messaging.default_notification_color"
    android:resource="@color/colorAccent" />
```

---

## 9. iOS Configuration

### ios/Runner/AppDelegate.swift

```swift
import UIKit
import Flutter
import FirebaseCore
import FirebaseMessaging

@UIApplicationMain
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    FirebaseApp.configure()
    
    UNUserNotificationCenter.current().delegate = self
    
    application.registerForRemoteNotifications()
    
    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  override func application(
    _ application: UIApplication,
    didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data
  ) {
    Messaging.messaging().apnsToken = deviceToken
  }
}
```

### ios/Runner/Info.plist

Add:
```xml
<key>UIBackgroundModes</key>
<array>
    <string>fetch</string>
    <string>remote-notification</string>
</array>
```

---

## 10. Testing

1. **Real Device Required** - FCM doesn't work on iOS simulator
2. **Use test endpoint**: `POST /api/fcm/test`
3. **Check Firebase Console** for message delivery status
4. **Check server logs** for FCM responses

---

## Notification Types

| Type | Description | Navigation |
|------|-------------|------------|
| `new_request` | New line request for agents | Agent Gigs |
| `order_created` | Order created for customer | Request Details |
| `agent_accepted` | Agent accepted request | Request Details |
| `payment_received` | Payment completed | Request Details |
| `job_completed` | Job finished | Request Details |
| `admin_broadcast` | Admin announcement | Notifications |
| `admin_message` | Direct admin message | Notifications |

---

## Troubleshooting

1. **Token is null**: Check Firebase initialization
2. **Notifications not received**: Check server key in `.env`
3. **Background not working**: Check manifest/plist configuration
4. **iOS not working**: Ensure APNS certificate is configured
