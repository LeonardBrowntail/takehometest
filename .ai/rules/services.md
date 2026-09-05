---
paths:
  - 'app/Services/**'
---

# Services

## Money is integer cents end-to-end
Money is stored as integer cents: wallets.amount and ledgers.amount are bigInteger (in cents). Service boundaries use (int $major, int $minor) pairs; DB writes use MoneyService::totalCents(); API output formats with MoneyService::toDecimal(). Never do float arithmetic on money — the old decimal(15,2) balance + float amounts truncated cents (deposit 1.99 -> 1).
