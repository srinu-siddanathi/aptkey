# AptKey Resident API Documentation

## Base URL
```
http://localhost/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

Tokens are obtained through the login endpoint.

---

## Endpoints

### 1. Authentication

#### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "resident@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "resident@example.com",
        "phone": "+91-1234567890",
        "role": "resident",
        "tenant_id": 1
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

#### Register (Optional)
```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "resident@example.com",
    "password": "password123",
    "phone": "+91-1234567890",
    "tenant_id": 1
}
```

#### Get Profile
```http
GET /api/resident/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "resident@example.com",
        "phone": "+91-1234567890",
        "role": "resident",
        "tenant_id": 1,
        "units": [
            {
                "id": 1,
                "block": "A",
                "unit_number": "101",
                "type": "2BHK",
                "monthly_maintenance": 5000.00
            }
        ]
    }
}
```

#### Update Profile
```http
PUT /api/resident/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "phone": "+91-9876543210",
    "password": "newpassword123"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

---

### 2. Dashboard

#### Get Dashboard Data
```http
GET /api/resident/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "stats": {
            "total_due": 15000.00,
            "pending_invoices": 2,
            "overdue_invoices": 1,
            "paid_this_month": 5000.00
        },
        "recent_transactions": [
            {
                "id": 1,
                "invoice_number": "INV-20241215-ABC123",
                "unit": "A - 101",
                "amount": 5000.00,
                "paid_amount": 0.00,
                "outstanding": 5000.00,
                "status": "pending",
                "invoice_date": "2024-12-01",
                "due_date": "2024-12-15",
                "is_overdue": false
            }
        ],
        "upcoming_dues": [
            {
                "id": 2,
                "invoice_number": "INV-20241220-XYZ789",
                "unit": "A - 101",
                "amount": 5000.00,
                "due_date": "2024-12-20",
                "days_until_due": 5
            }
        ]
    }
}
```

---

### 3. Invoices

#### Get All Invoices
```http
GET /api/resident/invoices
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-20241215-ABC123",
            "unit": "A - 101",
            "amount": 5000.00,
            "paid_amount": 0.00,
            "outstanding": 5000.00,
            "status": "pending",
            "invoice_date": "2024-12-01",
            "due_date": "2024-12-15",
            "is_overdue": false,
            "description": "Monthly maintenance for December 2024",
            "line_items": null
        }
    ]
}
```

#### Get Single Invoice
```http
GET /api/resident/invoices/{id}
Authorization: Bearer {token}
```

#### Mark Invoice as Paid
```http
POST /api/resident/invoices/{id}/pay
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "paid_amount": 5000.00,
    "payment_method": "online",
    "transaction_id": "TXN123456789"
}
```

**Payment Methods:** `online`, `cheque`, `cash`, `upi`, `bank_transfer`

**Response:**
```json
{
    "success": true,
    "message": "Payment recorded successfully",
    "data": {
        "id": 1,
        "invoice_number": "INV-20241215-ABC123",
        "paid_amount": 5000.00,
        "outstanding": 0.00,
        "status": "paid"
    }
}
```

---

### 4. Complaints

#### Get All Complaints
```http
GET /api/resident/complaints
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "ticket_number": "TKT-20241215-ABC123",
            "subject": "Elevator not working",
            "description": "Elevator in Block A is not functioning",
            "category": "elevator",
            "priority": "high",
            "status": "open",
            "unit": {
                "id": 1,
                "identifier": "A - 101"
            },
            "assigned_to": {
                "id": 2,
                "name": "Apartment Manager"
            },
            "resolution_notes": null,
            "resolved_at": null,
            "created_at": "2024-12-15 10:30:00"
        }
    ]
}
```

#### Get Single Complaint
```http
GET /api/resident/complaints/{id}
Authorization: Bearer {token}
```

#### Create Complaint
```http
POST /api/resident/complaints
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "subject": "Elevator not working",
    "description": "Elevator in Block A is not functioning properly",
    "category": "elevator",
    "priority": "high",
    "unit_id": 1
}
```

**Categories:** `plumbing`, `electrical`, `cleaning`, `security`, `parking`, `noise`, `elevator`, `other`

**Priorities:** `low`, `medium`, `high`, `urgent`

**Response:**
```json
{
    "success": true,
    "message": "Complaint raised successfully",
    "data": {
        "id": 1,
        "ticket_number": "TKT-20241215-ABC123",
        "subject": "Elevator not working",
        "status": "open",
        "created_at": "2024-12-15 10:30:00"
    }
}
```

---

### 5. Notices

#### Get All Notices
```http
GET /api/resident/notices
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Maintenance Schedule",
            "content": "Monthly maintenance will be conducted on...",
            "type": "maintenance",
            "priority": "high",
            "publish_date": "2024-12-01",
            "expiry_date": "2024-12-31",
            "created_by": "Apartment Manager",
            "views_count": 25,
            "created_at": "2024-12-01 09:00:00"
        }
    ]
}
```

#### Get Single Notice
```http
GET /api/resident/notices/{id}
Authorization: Bearer {token}
```

**Note:** Viewing a notice increments the view count.

---

## Error Responses

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Forbidden (403)
```json
{
    "success": false,
    "message": "Unauthorized. Only residents can access this endpoint."
}
```

---

## Testing with cURL

### Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"resident@example.com","password":"password"}'
```

### Get Dashboard
```bash
curl -X GET http://localhost/api/resident/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Create Complaint
```bash
curl -X POST http://localhost/api/resident/complaints \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Elevator not working",
    "description": "Elevator in Block A is not functioning",
    "category": "elevator",
    "priority": "high",
    "unit_id": 1
  }'
```

---

## Flutter Integration Example

```dart
// Login
final response = await http.post(
  Uri.parse('http://localhost/api/auth/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'resident@example.com',
    'password': 'password',
  }),
);

final data = jsonDecode(response.body);
final token = data['token'];

// Get Dashboard
final dashboardResponse = await http.get(
  Uri.parse('http://localhost/api/resident/dashboard'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);
```

---

## Notes

1. **Token Expiration**: Tokens don't expire by default. Configure in `config/sanctum.php` if needed.

2. **CORS**: Make sure CORS is configured for your Flutter app domain.

3. **Base URL**: Update the base URL for production.

4. **HTTPS**: Always use HTTPS in production.

5. **Rate Limiting**: Consider adding rate limiting for API endpoints.

