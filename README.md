# E-Wallet API

A mock e-wallet system that demonstrates transferring money between accounts.

## Authentication

All endpoints (except Register and Login) require a Sanctum bearer token. Include it in the `Authorization` header:

```
Authorization: Bearer <your-token>
```

## Rate Limiting

All API endpoints are rate-limited to **30 requests per minute** per user (or per IP for guests).

## Response Format

All responses follow a consistent JSON structure:

```json
{
  "status": true,
  "message": "...",
  "data": { }
}
```

On validation or error:

```json
{
  "status": false,
  "message": "...",
  "errors": { }
}
```

---

## Endpoints

### Register

`POST /api/auth/register`

Create a new user and their wallet.

**Request Body:**

| Field      | Type   | Rules                                                                 |
|------------|--------|-----------------------------------------------------------------------|
| `username` | string | Required, min 3 chars, alphanumeric/dash/underscore, unique           |
| `email`    | string | Required, valid RFC email, unique                                     |
| `phone`    | string | Required, valid Indonesian phone number, unique                       |
| `password` | string | Required, min 8 chars, must be confirmed (`password_confirmation`)    |

**Response (200):**

```json
{
  "status": true,
  "message": "registered"
}
```

---

### Login

`POST /api/auth/login`

Authenticate and receive a bearer token.

**Request Body:**

| Field      | Type   | Rules                                                                 |
|------------|--------|-----------------------------------------------------------------------|
| `identity` | string | Required, username, email, or Indonesian phone number                 |
| `password` | string | Required                                                              |

**Response (200):**

```json
{
  "status": true,
  "message": "logged in",
  "data": {
    "token": "1|abc...",
    "user": {
      "id": 1,
      "attributes": {
        "username": "johndoe",
        "email": "john@example.com",
        "phone": "081234567890"
      },
      "relationships": {
        "wallet": { "..." : "WalletResource" }
      }
    }
  }
}
```

---

### Logout

`GET /api/auth/logout`

Revoke the current access token.

**Headers:** `Authorization: Bearer <token>`

**Response (200):**

```json
{
  "status": true,
  "message": "logged out"
}
```

---

### Get Wallet

`GET /api/wallet`

Get the authenticated user's wallet details.

**Headers:** `Authorization: Bearer <token>`

**Response (200):**

```json
{
  "status": true,
  "message": "fetched wallet data",
  "data": {
    "id": "uuid-string",
    "attributes": {
      "owner_id": 1,
      "balance": "50000.00",
      "status": "active"
    },
    "relationships": {
      "owner": { "..." : "UserResource" },
      "ledgers": [ "..." : "LedgerResource" ],
      "transactions": [ "..." : "TransactionResource" ]
    }
  }
}
```

---

### Top Up (Deposit)

`POST /api/topup`

Add funds to the authenticated user's wallet.

**Headers:** `Authorization: Bearer <token>`

**Request Body:**

| Field    | Type    | Rules                                      |
|----------|---------|--------------------------------------------|
| `amount` | decimal | Required, 2 decimal places, minimum 1.00   |

**Response (200):**

```json
{
  "status": true,
  "message": "deposited",
  "data": {
    "id": "uuid-string",
    "attributes": {
      "type": "deposit",
      "status": "completed",
      "description": "..."
    },
    "relationships": {
      "ledgers": [ "..." : "LedgerResource" ]
    }
  }
}
```

---

### Transfer

`POST /api/transfer`

Transfer funds to another user's wallet.

**Headers:** `Authorization: Bearer <token>`

**Request Body:**

| Field            | Type   | Rules                                                        |
|------------------|--------|--------------------------------------------------------------|
| `destination_id` | string | Required, valid UUID, must exist in `wallets`, cannot be own wallet |
| `amount`         | decimal| Required, 2 decimal places, minimum 1.00                    |

**Response (200):**

```json
{
  "status": true,
  "message": "transfered",
  "data": {
    "id": "uuid-string",
    "attributes": {
      "type": "transfer",
      "status": "completed",
      "description": "..."
    },
    "relationships": {
      "ledgers": [ "..." : "LedgerResource" ]
    }
  }
}
```

---

### Get Transactions

`GET /api/transactions`

List the authenticated user's transactions (paginated, 10 per page).

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**

| Param   | Type | Description         |
|---------|------|---------------------|
| `page`  | int  | Page number (default: 1) |

**Response (200):**

```json
{
  "status": true,
  "message": "fetched transactions",
  "data": [
    {
      "id": "uuid-string",
      "attributes": {
        "type": "transfer",
        "status": "completed",
        "description": "..."
      },
      "relationships": {
        "ledgers": [ "..." : "LedgerResource" ]
      }
    }
  ]
}
```

---

## Error Codes

| HTTP Code | Meaning                                   |
|-----------|-------------------------------------------|
| 400       | Validation failed or invalid credentials   |
| 401       | Unauthenticated or unauthorized            |
| 422       | Business rule failure (e.g. insufficient balance) |
| 500       | Internal server error                      |
