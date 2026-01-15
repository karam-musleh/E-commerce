# E-commerce

## Overview

**Vertex-Store** is a **full-featured e-commerce API** built with **Laravel 12**.  
It is designed to provide a complete e-commerce backend with features for:

-   Products, categories, brands, and product attributes
-   Discounts, taxes, and additional pricing
-   Cart and order management
-   Payments with modular gateway support
-   Admin management for products, orders, reviews, and promotions

The system is modular, maintainable, and ready for future expansions.

---

## Features

### Products

-   Full CRUD for products
-   Products can have **multiple attributes** (color, size, etc.)
-   Attributes can include **additional pricing**
-   Supports **discounts** (percent or fixed amount)
-   Manage stock and availability
-   Featured products
-   Product reviews with approve/reject workflow

### Categories & Brands

-   Nested categories with parent/child relationships
-   Featured categories for homepage display
-   CRUD for brands with featured toggle
-   Slug-based routing for SEO-friendly URLs

### Cart

-   Add/remove products from cart
-   Update quantities
-   View cart content
-   Handles products with attributes and additional prices

### Orders

-   Place order directly from the cart
-   Cancel orders with reason
-   Track order status: pending, confirmed, delivered, canceled
-   Track payment status: unpaid / paid
-   Supports multiple delivery addresses per user

### Payments

-   Modular payment system using `PaymentGatewayInterface`
-   Fake Gateway for local testing
-   Ready for real gateways like Stripe and PayPal
-   Webhook simulation for payment confirmation
-   Stores payment logs with reference, transaction ID, amount, currency, and status

### Discounts & Pricing

-   Discounts at product level (percent or fixed)
-   Additional pricing for product attributes
-   Automatic subtotal, tax, and total calculation for orders

### Admin Panel API

-   Manage products, categories, brands
-   Manage orders and update statuses
-   Manage product reviews (approve/reject)
-   Manage promotions like daily deals and flash sales
-   Toggle featured and active status for products, brands, and categories

---

## Payment Flow (Example)

1. User initiates payment via `/api/orders/{order}/pay`
2. `PaymentService` creates a payment record (status: pending)
3. `PaymentGatewayResolver` selects the payment gateway (e.g., FakeGateway locally)
4. Gateway generates a `redirect_url`
5. User opens URL → Payment marked as **paid** via webhook simulation
6. Order updated: `payment_status = paid`, `status = confirmed`

---

## Installation & Setup

1. Clone the repository:

```bash
git clone  https://github.com/karam-musleh/E-commerce.git
cd E-commerce
```
