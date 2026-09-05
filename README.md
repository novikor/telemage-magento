# Telemage Magento Connector

Thin Magento 2 connector (`Novikor_Telemage`) for the [Telemage](https://github.com/novikor/telemage) integration service.
Provides store-local logic to securely link Magento customers via JWE without sharing passwords.

## Installation

```bash
composer require novikor/module-telemage
bin/magento module:enable Novikor_Telemage
bin/magento setup:upgrade
```

## Configuration

In Magento Admin, navigate to **Stores > Configuration > Services > Telemage Integration**:
- **General**: Enable module, set **Telegram Bot Identifier**, and **JWE secret**.
- **API Settings**: Set **Telemage Base URL** and **Telemage Integration URL Token**.

## Responsibilities

- Exposes `POST /V1/telemage/customer/token/byJWE` and customer account navigation link.
- Handles token encryption/decryption using `web-token/jwt-framework`.
- External orchestration and business logic remain in [Telemage](https://github.com/novikor/telemage).
