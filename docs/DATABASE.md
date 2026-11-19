# توثيق قاعدة البيانات

الـ migrations في `database/migrations/` هي المرجع. هذا الملف ملخص.

## المخطط والعلاقات

```
users                       (مستخدمو لوحة الإدارة فقط)
store_settings   [صف واحد]  (إعدادات المتجر)
categories 1 ──< products
customers  1 ──< orders 1 ──< order_items >── 0..1 products
pages                       (صفحات ثابتة: about/contact/privacy/returns)
```

- `products.category_id` → `categories.id` (`nullOnDelete`: حذف التصنيف لا يحذف المنتجات).
- `orders.customer_id` → `customers.id` (`nullOnDelete`).
- `order_items.order_id` → `orders.id` (`cascadeOnDelete`).
- `order_items.product_id` → `products.id` (`nullOnDelete`: يبقى بند الطلب بلقطة الاسم والسعر حتى لو حُذف المنتج).

## الجداول

### users
| العمود | النوع | ملاحظة |
|---|---|---|
| id, name, email (unique), password | | كلمة المرور مشفّرة (cast `hashed`) |
| role | string(20) | `admin` / `manager` / `staff` |
| is_active | bool | الحساب غير المفعّل لا يدخل اللوحة |
| email_verified_at, remember_token, timestamps | | |

> العملاء (الزبائن) **ليس لهم حسابات دخول**.

### store_settings (صف واحد)
name, description, logo, email, phone, country, city, address, `currency_code`, `currency_symbol`, `timezone`, `locale`, `primary_color` / `secondary_color` / `accent_color` (HEX من 7 محارف)، `shipping_fee`، `cod_enabled`، `manual_payment_enabled`، `manual_payment_label`، `manual_payment_instructions`، `installed_at` (يحدد اكتمال التثبيت).
تُخزَّن مؤقتًا في الكاش بالمفتاح `store.settings` وتُفرَّغ تلقائيًا عند الحفظ.

### categories
name, slug (unique, يدعم العربية), description, image, sort_order, is_active, `is_demo`.

### products
category_id, name, slug (unique), sku (unique, اختياري), description, `price` decimal(12,2)، `compare_at_price` (سعر قبل الخصم)، `stock` (غير سالب)، `images` (JSON: مسارات على القرص `public`)، is_active, is_featured, `is_demo`.

### customers
name, phone (**unique** — مفتاح مطابقة العميل عند الطلب)، email, city, address, `is_demo`. تُنشأ/تُحدَّث تلقائيًا عند إتمام الطلب.

### orders
- `number` (unique، مثل `ORD-261119-AB3XK`)، `checkout_token` (unique، يمنع التكرار).
- `status`: pending → confirmed → processing → shipped → delivered، أو cancelled.
- `payment_method` (`cod`/`manual`/…)، `payment_status`: unpaid / paid / refunded.
- `subtotal`, `shipping_fee`, `total`, `currency_code` — **تُحسب على الخادم**.
- لقطة بيانات الزبون: customer_name, customer_phone, customer_email, city, address, notes.
- `is_demo`.
- إلغاء الطلب يعيد المخزون؛ إعادة تفعيل طلب ملغي تخصمه (انظر `OrderObserver`).

### order_items
order_id, product_id (nullable), name, sku, unit_price, quantity, line_total — **لقطة وقت الطلب**.

### pages
slug (unique), title, body (Markdown — يُحوَّل لـ HTML بعد إزالة HTML الخام)، is_published.

## ملفات النظام

لا توجد جداول للجلسات أو الكاش أو الطوابير: الجلسات والكاش على الملفات، والطابور `sync`. (إن احتجت غير ذلك أنشئ الجداول وعدّل `.env`.)

## الملفات على القرص

| المسار | المحتوى |
|---|---|
| `storage/app/public/products` | صور المنتجات |
| `storage/app/public/categories` | صور التصنيفات |
| `storage/app/public/branding` | الشعار |
| `storage/app/installed.lock` | قفل التثبيت الأول |

## البيانات التجريبية

`DemoDataSeeder` يملأ التصنيفات والمنتجات بوسم `is_demo=true`. الأمر `php artisan store:demo-clean` يحذف كل الصفوف الموسومة (الطلبات، العملاء، المنتجات، التصنيفات).
