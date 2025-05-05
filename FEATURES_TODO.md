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

---

## 4. Product Reviews/Ratings✅
- /TODO (SQL): Create `reviews` table (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`)
- /TODO (PHP): Add form for members to submit reviews/ratings on product detail page
- /TODO (PHP): Display reviews/ratings on product detail page

---

## 5. Multiple Product Photos & Sliders✅
- /TODO (SQL): Create `product_photos` table (`photo_id`, `product_id`, `photo_path`)
- /TODO (PHP): Update product add/edit forms to allow multiple photo uploads
- /TODO (PHP): Display product photos as a slider/gallery on product detail page

---

## 6. Favorites/Wishlist
- /TODO (SQL): Create `wishlists` table (`wishlist_id`, `user_id`, `product_id`)
- /TODO (PHP): Add “Add to Wishlist” button on product pages
- /TODO (PHP): Add member page to view/manage wishlist

---

## 7. Discount Voucher Handling
- /TODO (SQL): Create `vouchers` table (`voucher_id`, `code`, `discount_amount`, `expiry_date`, `usage_limit`)
- /TODO (SQL): Create `voucher_usages` table (`usage_id`, `voucher_id`, `user_id`, `used_at`)
- /TODO (PHP): Add voucher code input on checkout page
- /TODO (PHP): Validate and apply voucher during checkout

---

## 8. E-Receipts (PDF/Email)
- /TODO (PHP): Generate PDF/e-mail receipt after successful order (use a PHP library like TCPDF or PHPMailer)

---

## 9. Filtering, Sorting, and Paging
- /TODO (PHP/JS): Update product listing page to support:
  - Filtering by category and price
  - Sorting (e.g., price, newest)
  - Paging (pagination controls)

---

## 10. Enhanced Authentication & User Features
- /TODO (SQL): Add password strength requirements table
- /TODO (JS): Implement password strength indicator using jQuery
- /TODO (AJAX): Add real-time email uniqueness check during registration
- /TODO (PHP): Implement session flash messages system
- /TODO (PHP): Add secure file upload validation

---

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

> For each feature, implement the SQL/database changes first, then update PHP logic and UI as needed.
> Use these /TODOs as a checklist as you build out your advanced shop features.
# Feature TODOs

## User & Security
- [ ] User Email Verification (via PHP mail() or PHPMailer)
- [ ] Captcha Integration (e.g. Google reCAPTCHA with PHP)
- [ ] Temporary Login Blocking (3 Attempts using session/db tracking)
- [ ] Block + Unblock User Account (Admin toggle in DB)
- [ ] Remember Me (Retain Login Session via cookies with tokens)

## Product Enhancements
- [ ] Category Maintenance + CRUD
- [ ] Product Stock Handling
- [ ] Low-In-Stock Alert (Admin view)
- [ ] Product Filtering (by Category & Price)
- [ ] Filtering, Sorting and Paging (Combined using SQL + jQuery)
- [ ] 1 Product = Multiple Photos (store in separate table)
- [ ] Product Photos Sliders (Dynamic with jQuery plugin)

## Order & Payment (Fake)
- [ ] Order Cancellation (Member)
- [ ] Order Status Update (Admin)
- [ ] Payment (Fake - Data Entry Only)
- [ ] E-Receipt (PDF via TCPDF or Email)
- [ ] Shipping Address Handling

## UX & Media
- [ ] Product Rating + Review
- [ ] Record Listing (Table View + Photo View toggle with jQuery)
- [ ] Drag-and-Drop Photo Upload (using a small JS lib or jQuery plugin)