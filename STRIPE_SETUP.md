# Stripe Payment Setup Guide

## Get Your Test API Keys

1. Go to https://dashboard.stripe.com/test/apikeys
2. Create a free Stripe account (if you don't have one)
3. Copy your **Publishable key** (starts with `pk_test_`)
4. Copy your **Secret key** (starts with `sk_test_`)

## Update Your .env File

Edit `C:\wamp64\www\cinema\.env` and replace:

```
STRIPE_PUBLISHABLE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key_here
```

With your actual test keys:

```
STRIPE_PUBLISHABLE_KEY=pk_test_abc123...
STRIPE_SECRET_KEY=sk_test_xyz789...
```

## Test Card Numbers

Use these test card numbers for payments:

| Card Number | Brand | Result |
|-------------|-------|--------|
| 4242 4242 4242 4242 | Visa | Success |
| 4000 0000 0000 0002 | Visa | Declined |

- Use any future expiry date (MM/YY)
- Use any 3-digit CVC
- Use any ZIP code

## Important Notes

- **NEVER** use live keys in development
- **NEVER** commit real API keys to git
- The `.env` file is already in `.gitignore` for security
