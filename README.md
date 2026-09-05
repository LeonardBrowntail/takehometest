# E-Wallet API

A mock REST e-wallet backend built with **Laravel 13** and **Laravel Sanctum**. It provides:

1. **Authentication** — register, login, and logout via Sanctum API tokens.
2. **A safe transfer system** — deposits and transfers between wallets, using an integer-cents money model, ledger entries per transaction, and an optimistic concurrency nonce to prevent lost/double balance updates.

## Tech Stack

- PHP 8.5 / Laravel 13 / Eloquent
- Laravel Sanctum (bearer token auth)
- SQLite (default driver)
- JSON:API (JSON:API)-shaped resources (`type`, `id`, `attributes`, `relationships`)

## Setup

```bash
composer install
copy .env.example .env        # Windows
php artisan key:generate
php artisan migrate
php artisan serve
```

> The API is served at `http://localhost:8000`. If the local `database/database.sqlite` is stale (pre-existing schema), recreate it before starting: delete the file and run `php artisan migrate` again.

## Authentication

All endpoints **except** `POST /api/auth/register` and `POST /api/auth/login` require a Sanctum bearer token in the `Authorization` header:

```
Authorization: Bearer <token>
```

Obtain a token by calling `POST /api/auth/login`. Send `Accept: application/json` on requests so errors are returned as JSON with correct status codes.

## Rate Limiting

All `/api/*` endpoints are rate-limited to **30 requests per minute** per user (or per IP for guests, e.g. on register/login).

## Money Model

- All monetary values are **integer cents** in the database (`wallets.amount`, `ledgers.amount`).
- The API accepts and returns money as decimal strings with exactly **2 decimal places** (e.g. `"50.00"`), minimum `1.00` on deposits/transfers.
- Input like `100`, `100.5`, or `100.999` is rejected by validation.

## Response Format

Successful responses:

```json
{
  "status": true,
  "message": "...",
  "data": { }
}
```

Validation/credential failures (`400`) follow the envelope with field errors:

```json
{
  "status": false,
  "message": "invalid field(s)",
  "errors": { "field": ["message"] }
}
```

Resource payloads are JSON:API objects: `{ "data": { "id": "...", "type": "...", "attributes": { }, "relationships": { } } }`. Relationships are only included when requested via `?include=`.

## Status Codes

| HTTP | Meaning |
|------|---------|
| 200  | Success |
| 400  | Validation failed or invalid credentials |
| 401  | Unauthenticated (missing/invalid token) or rejected business action (frozen wallet, unknown receiver wallet) |
| 422  | Business rule failure (e.g. insufficient balance) |
| 429  | Too many requests (rate limit exceeded) |
| 500  | Unhandled server error |

## Endpoints

### Register

`POST /api/auth/register` — public

Create a user and their wallet.

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | required, min 3, alphanumeric/dash/underscore, unique |
| `email` | string | required, valid RFC email, unique |
| `phone` | string | required, valid Indonesian mobile number, unique |
| `password` | string | required, min 8, must match `password_confirmation` |

**Response 200:**

```json
{ "status": true, "message": "registered" }
```

---

### Login

`POST /api/auth/login` — public

Authenticate and receive a bearer token. Logging in revokes any existing tokens for that user (single active session).

| Field | Type | Rules |
|-------|------|-------|
| `identity` | string | required — username, email, or Indonesian phone number |
| `password` | string | required |

**Response 200:**

```json
{
  "status": true,
  "message": "logged in",
  "data": {
    "token": "9|nqY7bJ...",
    "user": {
      "data": {
        "id": "9",
        "type": "users",
        "attributes": {
          "username": "alice",
          "email": "alice@example.com",
          "phone": "081234567891"
        }
      }
    }
  }
}
```

Invalid credentials return `400` with an error under `identity`.

---

### Logout

`POST /api/auth/logout` — authenticated

Revoke the current access token. (Note: this is a **POST** request.)

**Headers:** `Authorization: Bearer <token>`

**Response 200:**

```json
{ "status": true, "message": "logged out" }
```

---

### Get Wallet

`GET /api/wallet` — authenticated

Get the authenticated user's wallet.

**Headers:** `Authorization: Bearer <token>`

**Response 200:**

```json
{
  "status": true,
  "message": "fetched wallet data",
  "data": {
    "data": {
      "id": "4cf7…",
      "type": "wallets",
      "attributes": {
        "owner_id": 9,
        "balance": "50000.00",
        "status": "active"
      }
    }
  }
}
```

---

### Top Up

`POST /api/topup` — authenticated

Add funds to the authenticated user's wallet.

| Field | Type | Rules |
|-------|------|-------|
| `amount` | decimal string | required, exactly 2 decimals, min `1.00` |

**Response 200:**

```json
{
  "status": true,
  "message": "deposited",
  "data": {
    "data": {
      "id": "5d1f…",
      "type": "transactions",
      "attributes": {
        "type": "deposit",
        "status": "completed",
        "description": "Transaction completed."
      }
    }
  }
}
```

---

### Transfer

`POST /api/transfer` — authenticated

Transfer funds from the authenticated user's wallet to another wallet. Both balance changes are written atomically with ledger entries for each side; concurrent updates are protected by an optimistic nonce check.

| Field | Type | Rules |
|-------|------|-------|
| `destination_id` | string | required, valid UUID, must exist in `wallets`, cannot be the sender's own wallet |
| `amount` | decimal string | required, exactly 2 decimals, min `1.00` |

**Response 200:**

```json
{
  "status": true,
  "message": "transfered",
  "data": {
    "data": {
      "id": "77aa…",
      "type": "transactions",
      "attributes": {
        "type": "transfer",
        "status": "completed",
        "description": "Transaction completed."
      }
    }
  }
}
```

Insufficient balance returns `422` with message `"Insufficient balance."`.

---

### Get Transactions

`GET /api/transactions` — authenticated

List the authenticated user's transactions (10 per page).

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number (default: 1) |

**Response 200:**

```json
{
  "status": true,
  "message": "fetched transactions",
  "data": {
    "data": [
      {
        "id": "5d1f…",
        "type": "transactions",
        "attributes": {
          "type": "deposit",
          "status": "completed",
          "description": "Transaction completed."
        }
      }
    ]
  }
}
```

---

## Example Flow

```bash
# 1. Register two users
curl -i -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"username":"alice","email":"alice@example.com","phone":"081234567891","password":"password123","password_confirmation":"password123"}' \
  http://localhost:8000/api/auth/register

curl -i -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"username":"bob","email":"bob@example.com","phone":"081234567892","password":"password123","password_confirmation":"password123"}' \
  http://localhost:8000/api/auth/register

# 2. Login as alice
curl -s -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"identity":"alice","password":"password123"}' \
  http://localhost:8000/api/auth/login

# 3. Top up alice's wallet (use the token from step 2)
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"amount":"100.00"}' \
  http://localhost:8000/api/topup

# 4. Transfer to bob (destination_id is bob's wallet UUID — see bob's login/GET wallet; or the wallets table)
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"destination_id":"<bob-wallet-uuid>","amount":"25.50"}' \
  http://localhost:8000/api/transfer

# 5. View alice's transactions
curl -s -H "Accept: application/json" -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/transactions?page=1"
```

## Known Issues & Limitations

- **Unknown routes and wrong methods return `500`** instead of `404`/`405`. The catch-all `Exception` renderer for `api/*` also leaks the raw exception message into `errors`; in production this should return a generic message and let `404`/`405` pass through.
- **Stale local database**: the pre-existing `database/database.sqlite` ships an old `wallets.balance` schema, which crashes `GET /api/wallet` until the DB is recreated with `php artisan migrate`.
- Error semantics are inconsistent: a missing/frozen receiver wallet surfaces as `401 Unauthorized` (`UnauthorizedException`) rather than `404`/`422`.
- Failed balances/ledgers write together atomically, but a failed transaction is also recorded with `status: failed`, so the ledger totals always reconcile.
- No tests cover the API yet (only the starter example tests exist).