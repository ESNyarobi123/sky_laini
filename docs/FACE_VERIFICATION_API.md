# Face Verification API Documentation (Liveness Detection)

## Overview

This API allows agents to submit face verification photos from 5 different angles for liveness detection. This ensures that the person registering is real and prevents fraudulent registrations.

## Base URL

```
https://your-domain.com/api
```

## Authentication

All endpoints require Bearer token authentication.

```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Get Face Verification Status

**GET** `/api/agent/face-verification`

Returns the current face verification status and instructions.

#### Response

```json
{
    "agent_id": 1,
    "face_verified": false,
    "verification": {
        "id": 1,
        "status": "pending",
        "is_complete": true,
        "completion_percentage": 100,
        "missing_images": [],
        "rejection_reason": null,
        "created_at": "2025-12-16T08:00:00+03:00",
        "images": {
            "center": "https://domain.com/storage/face_verifications/1/center.jpg",
            "left": "https://domain.com/storage/face_verifications/1/left.jpg",
            "right": "https://domain.com/storage/face_verifications/1/right.jpg",
            "up": "https://domain.com/storage/face_verifications/1/up.jpg",
            "down": "https://domain.com/storage/face_verifications/1/down.jpg"
        }
    },
    "instructions": {
        "sw": {
            "title": "Uthibitishaji wa Uso",
            "description": "Tafadhali piga picha za uso wako kwa mwelekeo tofauti ili kuthibitisha utambulisho wako.",
            "steps": {
                "center": "Angalia moja kwa moja kwenye kamera",
                "left": "Geuza kichwa chako kushoto",
                "right": "Geuza kichwa chako kulia",
                "up": "Angalia juu",
                "down": "Angalia chini"
            }
        },
        "en": {
            "title": "Face Verification",
            "description": "Please take photos of your face from different angles to verify your identity.",
            "steps": {
                "center": "Look straight at the camera",
                "left": "Turn your head to the left",
                "right": "Turn your head to the right",
                "up": "Look up",
                "down": "Look down"
            }
        }
    }
}
```

---

### 2. Start Face Verification Session

**POST** `/api/agent/face-verification/start`

Starts a new face verification session. Returns error if one is already pending.

#### Request Body

```json
{
    "platform": "android"
}
```

#### Response (Success - 201)

```json
{
    "message": "Mchakato wa uthibitishaji umeanza. Pakia picha zako za uso.",
    "verification_id": 1,
    "required_images": ["center", "left", "right", "up", "down"],
    "next_step": "center"
}
```

#### Response (Already Pending - 400)

```json
{
    "message": "Una mchakato wa uthibitishaji unaoendelea. Tafadhali ukamilishe.",
    "verification_id": 1,
    "completion_percentage": 40,
    "missing_images": ["right", "up", "down"]
}
```

---

### 3. Upload Single Face Image

**POST** `/api/agent/face-verification/upload`

Upload a single face image for a specific direction.

#### Request (Multipart Form Data)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| direction | string | Yes | One of: `center`, `left`, `right`, `up`, `down` |
| image | file | Yes | JPEG/PNG image, max 5MB |

#### cURL Example

```bash
curl -X POST "https://domain.com/api/agent/face-verification/upload" \
  -H "Authorization: Bearer {token}" \
  -F "direction=center" \
  -F "image=@/path/to/face_center.jpg"
```

#### Response (Success)

```json
{
    "message": "Picha imehifadhiwa.",
    "direction": "center",
    "image_url": "https://domain.com/storage/face_verifications/1/center.jpg",
    "completion_percentage": 20,
    "is_complete": false,
    "missing_images": ["left", "right", "up", "down"],
    "next_step": "left"
}
```

---

### 4. Upload All Face Images At Once

**POST** `/api/agent/face-verification/upload-all`

Upload all 5 face images at once for faster submission.

#### Request (Multipart Form Data)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| face_center | file | Yes | Center face image (looking straight) |
| face_left | file | Yes | Left face image (head turned left) |
| face_right | file | Yes | Right face image (head turned right) |
| face_up | file | Yes | Up face image (looking up) |
| face_down | file | Yes | Down face image (looking down) |
| platform | string | No | Device platform (android/ios) |

#### cURL Example

```bash
curl -X POST "https://domain.com/api/agent/face-verification/upload-all" \
  -H "Authorization: Bearer {token}" \
  -F "face_center=@/path/to/center.jpg" \
  -F "face_left=@/path/to/left.jpg" \
  -F "face_right=@/path/to/right.jpg" \
  -F "face_up=@/path/to/up.jpg" \
  -F "face_down=@/path/to/down.jpg" \
  -F "platform=android"
```

#### Response (Success - 201)

```json
{
    "message": "Picha zote zimehifadhiwa. Tafadhali subiri uhakiki.",
    "verification_id": 1,
    "status": "pending",
    "images": {
        "center": "https://domain.com/storage/face_verifications/1/center.jpg",
        "left": "https://domain.com/storage/face_verifications/1/left.jpg",
        "right": "https://domain.com/storage/face_verifications/1/right.jpg",
        "up": "https://domain.com/storage/face_verifications/1/up.jpg",
        "down": "https://domain.com/storage/face_verifications/1/down.jpg"
    }
}
```

---

### 5. Submit Verification for Review

**POST** `/api/agent/face-verification/submit`

Submit the verification for admin review (only if all images are uploaded).

#### Response (Success)

```json
{
    "message": "Uthibitishaji umewasilishwa. Utapokea jibu hivi karibuni.",
    "verification_id": 1,
    "status": "pending"
}
```

#### Response (Incomplete - 400)

```json
{
    "message": "Tafadhali pakia picha zote kabla ya kuwasilisha.",
    "missing_images": ["up", "down"]
}
```

---

## Status Values

| Status | Description |
|--------|-------------|
| `pending` | Waiting for admin review |
| `approved` | Face verification approved - agent can work |
| `rejected` | Face verification rejected - agent must resubmit |

---

## Android Implementation Example (Kotlin)

```kotlin
// FaceVerificationRepository.kt

interface FaceVerificationApi {
    @GET("agent/face-verification")
    suspend fun getStatus(): Response<FaceVerificationResponse>

    @POST("agent/face-verification/start")
    suspend fun startVerification(): Response<StartVerificationResponse>

    @Multipart
    @POST("agent/face-verification/upload")
    suspend fun uploadImage(
        @Part("direction") direction: RequestBody,
        @Part image: MultipartBody.Part
    ): Response<UploadResponse>

    @Multipart
    @POST("agent/face-verification/upload-all")
    suspend fun uploadAllImages(
        @Part faceCenter: MultipartBody.Part,
        @Part faceLeft: MultipartBody.Part,
        @Part faceRight: MultipartBody.Part,
        @Part faceUp: MultipartBody.Part,
        @Part faceDown: MultipartBody.Part,
        @Part("platform") platform: RequestBody
    ): Response<UploadAllResponse>
}

// Usage
suspend fun uploadFaceImage(direction: String, imageFile: File) {
    val requestFile = imageFile.asRequestBody("image/jpeg".toMediaType())
    val imagePart = MultipartBody.Part.createFormData("image", imageFile.name, requestFile)
    val directionBody = direction.toRequestBody("text/plain".toMediaType())
    
    api.uploadImage(directionBody, imagePart)
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid data or incomplete verification |
| 401 | Unauthorized - Missing or invalid token |
| 404 | Not Found - Agent profile not found |
| 422 | Validation Error - Invalid file type or missing fields |
| 500 | Server Error |

---

## Notes

1. **Image Requirements**:
   - Format: JPEG or PNG
   - Maximum size: 5MB per image
   - Recommended resolution: 640x480 or higher
   - Good lighting is important

2. **Face Detection Tips**:
   - Face should be clearly visible
   - No sunglasses or face coverings
   - Good lighting conditions
   - Neutral background preferred

3. **Verification Process**:
   - After all 5 images are uploaded, admin will review
   - Agent will receive in-app notification with result
   - If rejected, agent can resubmit with new images
