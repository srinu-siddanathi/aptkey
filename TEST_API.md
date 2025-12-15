# Quick API Testing Guide

## Prerequisites

1. Create a test resident user:
```bash
php artisan tinker
```

```php
$tenant = \App\Models\Tenant::first();
$resident = \App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Test Resident',
    'email' => 'resident@test.com',
    'password' => bcrypt('password'),
    'role' => 'resident',
]);
echo "Resident created: {$resident->email} / password\n";
```

## Test Endpoints

### 1. Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"resident@test.com","password":"password"}' \
  | jq
```

Save the token from the response.

### 2. Get Dashboard
```bash
curl -X GET http://localhost/api/resident/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  | jq
```

### 3. Get Invoices
```bash
curl -X GET http://localhost/api/resident/invoices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  | jq
```

### 4. Create Complaint
```bash
curl -X POST http://localhost/api/resident/complaints \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Test Complaint",
    "description": "This is a test complaint",
    "category": "other",
    "priority": "medium"
  }' \
  | jq
```

### 5. Get Notices
```bash
curl -X GET http://localhost/api/resident/notices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  | jq
```

## Using Postman

1. Import the collection (create manually):
   - Base URL: `http://localhost/api`
   - Create environment variable: `token`

2. Login request:
   - Method: POST
   - URL: `{{base_url}}/auth/login`
   - Body (JSON):
     ```json
     {
       "email": "resident@test.com",
       "password": "password"
     }
     ```
   - Save token to environment variable

3. Other requests:
   - Add header: `Authorization: Bearer {{token}}`

## Expected Responses

All successful responses follow this format:
```json
{
  "success": true,
  "data": { ... }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error message"
}
```

