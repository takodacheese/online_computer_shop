# Online Computer Shop - Advanced Features /TODO List

This file contains feature plans and /TODOs for advanced functionality. Each section notes what needs to be done in SQL (database), PHP, or other layers.

---
✅
## 1. Category Maintenance + Filtering(half)
- /TODO (SQL): Create `categories` table (`category_id`, `category_name`)
- /TODO (SQL): Add `category_id` FK to `products` table
- /TODO (PHP): Add CRUD functions for categories in `functions.php`
- /TODO (PHP): Add admin pages for category add/edit/delete
- /TODO (PHP): Update product add/edit forms to select category
- /TODO (PHP): Add category filter to product listing page

---

## 2. Product Stock Handling & Low-In-Stock Alert✅
- /TODO (SQL): Add `stock` INT column to `products` table
- /TODO (PHP): Update product add/edit forms to manage stock
- /TODO (PHP): Deduct stock on successful order/checkout
- /TODO (PHP): Show "Low Stock" alert to admin if stock < threshold

---

## 3. Order Status Update (Admin) & Order Cancellation (Member)✅
- /TODO (SQL): Add `status` column to `orders` table (e.g., Pending, Processing, Shipped, Cancelled)
- /TODO (PHP): Admin: Add UI to update order status
- /TODO (PHP): Member: Add "Cancel Order" option for eligible orders

## 6. Favorites/Wishlist ✅
- /TODO (SQL): Create `wishlists` table (`wishlist_id`, `user_id`, `product_id`)
- /TODO (PHP): Add “Add to Wishlist” button on product pages
- /TODO (PHP): Add member page to view/manage wishlist


## 8. E-Receipts (PDF/Email)
- /TODO (PHP): Generate PDF/e-mail receipt after successful order (use a PHP library like TCPDF or PHPMailer)

## 11. Custom PC Builder Enhancements
- /TODO (JS): Implement real-time compatibility checking
- /TODO (PHP): Add validation for CPU + motherboard compatibility
- /TODO (UI): Add warning indicators for incompatible components

---

## 12. Admin Dashboard Enhancements
- /TODO (PHP): Add interactive sales charts (Chart.js)
- /TODO (PHP): Implement user activity logging
- /TODO (PHP): Add top-selling products widget
- /TODO (PHP): Add order status summary

---

## 13. UX Improvements
- /TODO (JS): Add confirmation modal for deletions
- /TODO (JS): Implement expandable order detail rows
- /TODO (HTML5): Add form autofill suggestions
- /TODO (JS): Add "Back to Top" button
- /TODO (CSS): Implement dark mode toggle with localStorage

---
