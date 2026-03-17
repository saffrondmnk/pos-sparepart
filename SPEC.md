# Car Spare Parts POS System - Complete Specification

## Table of Contents
1. [Project Overview](#project-overview)
2. [Brainstorming & Planning](#brainstorming--planning)
3. [System Architecture](#system-architecture)
4. [Database Design](#database-design)
5. [Implementation Guide](#implementation-guide)
6. [Features Documentation](#features-documentation)
7. [Code Reference](#code-reference)
8. [Deployment Guide](#deployment-guide)

---

## Project Overview

### What is This Project?
A complete **Point of Sale (POS) System** designed specifically for car spare parts businesses. It allows businesses to:
- Manage inventory and track stock levels
- Process sales transactions with multiple customers simultaneously
- Generate receipts and reports
- Monitor user activity with force logout capability
- Customize branding and timezone settings
- Track complete stock audit trail

### Target Users
- **Super Admin**: Full system access, can manage everything including forcing logout other users
- **Admin**: Manages products, categories, users, and views reports. Can force logout cashiers
- **Cashier**: Processes sales and views transaction history. Limited to single device login

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **Database**: MySQL/PostgreSQL
- **PDF Generation**: DOMPDF
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze
- **Session Storage**: Database (for force logout capability)

---

## Brainstorming & Planning

### Phase 1: Core Requirements Analysis

Before writing any code, we identified these key requirements:

```
1. USER MANAGEMENT
   - Three distinct roles (Super Admin, Admin, Cashier)
   - Secure authentication with single device login
   - Profile management
   - Phone number field for users

2. INVENTORY MANAGEMENT
   - Product catalog with categories
   - Smart SKU generation (human-readable format)
   - Manual SKU editing capability
   - Stock tracking with minimum levels
   - Complete stock history/audit trail
   - Low stock alerts

3. SALES PROCESS
   - Point of Sale interface with multiple customer tabs
   - Persistent shopping cart (survives page refresh)
   - Multiple payment methods (Cash, Card, Digital)
   - Transaction receipts
   - Automatic stock deduction on sale

4. REPORTING
   - Sales reports with date filters
   - PDF export for receipts and reports
   - Complete transaction history
   - Global stock history view

5. SYSTEM CUSTOMIZATION
   - Company branding (logo, name)
   - Receipt customization
   - Browser tab title
   - Timezone configuration (APP_TIMEZONE)

6. MONITORING
   - Track user login/logout
   - View active sessions
   - Force logout capability with immediate effect
   - Session ID tracking for security
```

### Phase 2: Database Design Planning

We designed the database schema to support:
- User authentication and roles
- Product inventory with categories
- Smart SKU generation and editing
- Transaction processing with line items
- Stock change auditing with user tracking
- System settings storage
- Session tracking with session_id for force logout

### Phase 3: UI/UX Planning

**Dashboard Layout:**
```
┌─────────────────────────────────────┐
│  Logo + Company Name    User Menu   │
├─────────────────────────────────────┤
│ Dashboard | Products | Categories   │
│ Users | New Sale | Sales History    │
│ Reports | Settings | Sessions       │
│ Stock History                       │
├─────────────────────────────────────┤
│                                     │
│        Main Content Area            │
│                                     │
├─────────────────────────────────────┤
│  Date Display          Time Display │
└─────────────────────────────────────┘
```

---

## System Architecture

### Directory Structure

```
laravel-pos-system/
├── app/
│   ├── Helpers/
│   │   └── CurrencyHelper.php          # Currency formatting functions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                   # Laravel Breeze auth controllers
│   │   │   │   └── AuthenticatedSessionController.php (Single device login)
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php   # +SKU editing +All stock history
│   │   │   ├── ProfileController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SessionsController.php  # +Force logout with session deletion
│   │   │   ├── SettingsController.php
│   │   │   ├── TransactionController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php      # Role-based access control
│   │   │   └── ValidateSession.php     # Session validation middleware
│   │   └── Requests/
│   │       └── Auth/
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Product.php                 # +isLowStock() +isOutOfStock()
│   │   ├── Setting.php
│   │   ├── StockHistory.php            # +getChangeTypeLabel() +getChangeTypeColor()
│   │   ├── Transaction.php
│   │   ├── TransactionItem.php
│   │   ├── User.php                    # +isSuperAdmin() +isAdmin() +isCashier()
│   │   └── UserSession.php             # +session_id +getDurationAttribute()
│   ├── Policies/
│   │   └── UserPolicy.php
│   └── Providers/
│       └── AppServiceProvider.php      # +Login/Logout event listeners
├── bootstrap/
│   └── app.php                         # +ValidateSession middleware
├── config/
│   ├── app.php                         # Timezone: env('APP_TIMEZONE', 'UTC')
│   ├── auth.php
│   ├── currency.php                    # Custom currency config
│   └── ...
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 2026_03_17_121414_add_session_id_to_user_sessions_table.php
│   │   └── ...
│   └── seeders/
├── resources/
│   └── views/
│       ├── products/
│       │   ├── all-stock-history.blade.php  # Global stock history view
│       │   └── edit-sku.blade.php           # SKU editing view
│       ├── transactions/
│       │   └── create.blade.php             # +Multiple customer tabs
│       └── ...
├── routes/
│   └── web.php                         # +stock-history +edit-sku routes
└── public/
    └── images/                         # Uploaded logos and product images
```

### Application Flow

```
1. User Login
   ↓
2. Check for existing session (single device login)
   ↓
3. ValidateSession middleware validates session
   ↓
4. RoleMiddleware checks permissions
   ↓
5. Controller handles request
   ↓
6. Model interacts with database
   ↓
7. View renders response
```

---

## Database Design

### Entity Relationship Diagram (ERD)

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│    users    │       │ transactions │       │   products  │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id          │◄──────┤ user_id      │       │ id          │
│ name        │       │ id           │◄──────┤ category_id │
│ email       │       │ total_amount │       │ name        │
│ password    │       │ status       │       │ price       │
│ role        │       │ created_at   │       │ stock_qty   │
│ phone       │       └──────────────┘       │ sku (unique)│
└─────────────┘                              └─────────────┘
                               │                      │
                               │                      │
                               ▼                      ▼
                        ┌──────────────┐       ┌─────────────┐
                        │transaction_  │       │ categories  │
                        │   items      │       ├─────────────┤
                        ├──────────────┤       │ id          │
                        │ transaction_ │       │ name        │
                        │    _id       │       │ description │
                        │ product_id   │──────►│ id          │
                        │ quantity     │       └─────────────┘
                        │ unit_price   │
                        └──────────────┘

┌──────────────┐       ┌─────────────┐       ┌─────────────┐
│    settings  │       │user_sessions│       │stock_history│
├──────────────┤       ├─────────────┤       ├─────────────┤
│ id           │       │ id          │◄──────┤ product_id  │
│ company_name │       │ user_id     │       │ user_id     │◄────┐
│ logo_path    │       │ session_id  │◄──────┤ qty_before  │     │
│ app_title    │       │ ip_address  │       │ qty_after   │     │
│ receipt_title│       │ login_at    │       │ type        │     │
│ receipt_addr │       │ logout_at   │       │ notes       │     │
│ receipt_phone│       │ status      │       └─────────────┘     │
└──────────────┘       └─────────────┘                          │
                                                                │
┌───────────────────────────────────────────────────────────────┘
│
▼
┌─────────────┐
│    users    │
│ id          │
└─────────────┘
```

### Detailed Table Schemas

#### 1. Users Table
Stores all system users with their roles.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'cashier') DEFAULT 'cashier',
    phone VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Role Hierarchy:**
- `super_admin`: Can do everything, including managing other admins and force logout anyone
- `admin`: Can manage products, categories, users (except super admin), view reports, force logout cashiers
- `cashier`: Can only create transactions and view sales history. Single device login enforced

**Helper Methods:**
```php
$user->isSuperAdmin();  // Check if super admin
$user->isAdmin();       // Check if admin (includes super_admin)
$user->isCashier();     // Check if cashier
```

#### 2. Categories Table
Product categories for organization.

```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 3. Products Table
Inventory items with stock tracking.

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category_id BIGINT UNSIGNED,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

**Key Fields:**
- `sku`: Stock Keeping Unit - unique identifier (human-readable format: SKU-[Category][Product]-###, e.g., SKU-OEYSM-001)
- `price`: Selling price to customers
- `cost_price`: Purchase price from suppliers
- `min_stock_level`: Alert threshold for low stock

**Helper Methods:**
```php
$product->isLowStock();     // Check if stock <= min_stock_level
$product->isOutOfStock();   // Check if stock <= 0
```

#### 4. Transactions Table
Sales transactions header.

```sql
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    transaction_number VARCHAR(255) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'digital') DEFAULT 'cash',
    status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);
```

**Transaction Number Format:** TXN-YYYYMMDD-XXXX (e.g., TXN-20240317-0042)

#### 5. Transaction Items Table
Line items for each transaction.

```sql
CREATE TABLE transaction_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);
```

#### 6. Stock Histories Table
Audit trail for all stock changes.

```sql
CREATE TABLE stock_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    quantity_changed INT NOT NULL,
    type ENUM('add', 'subtract', 'set') NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);
```

**Change Types:**
- `add`: Added stock (e.g., new shipment arrived, initial product creation)
- `subtract`: Removed stock (e.g., damaged items, sales)
- `set`: Absolute value set (e.g., inventory count adjustment)

**Helper Methods:**
```php
$history->getChangeTypeLabel();  // Returns: 'Stock Added', 'Stock Reduced', 'Stock Set'
$history->getChangeTypeColor();  // Returns: 'green', 'red', 'blue' for UI styling
```

#### 7. Settings Table
System-wide configuration.

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT 'Car Spare Parts POS',
    receipt_title VARCHAR(255) DEFAULT 'Car Spare Parts POS',
    logo_path VARCHAR(255) NULL,
    app_title VARCHAR(255) DEFAULT 'Laravel',
    receipt_address TEXT NULL,
    receipt_phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 8. User Sessions Table
Login activity tracking with session management.

```sql
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(255) NULL,           -- Laravel session ID for force logout
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_at TIMESTAMP NOT NULL,
    logout_at TIMESTAMP NULL,
    status ENUM('active', 'logged_out', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id)       -- For quick session lookup
);
```

**Session States:**
- `active`: User is currently logged in
- `logged_out`: User logged out normally or was force logged out
- `expired`: Session timed out

**Helper Methods:**
```php
$session->getDurationAttribute();  // Returns formatted duration (e.g., "2h 30m")
$session->isActive();              // Check if status === 'active'
```

---

## Implementation Guide

### Step 1: Initial Setup

```bash
# Create Laravel project
composer create-project laravel/laravel laravel-pos-system
cd laravel-pos-system

# Install required packages
composer require barryvdh/laravel-dompdf
composer require laravel/breeze --dev
php artisan breeze:install blade

# Create symbolic link for storage
php artisan storage:link
```

### Step 2: Database Setup

**Add to `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_system
DB_USERNAME=root
DB_PASSWORD=your_password

# Timezone Configuration
APP_TIMEZONE=Asia/Jakarta

# Session Configuration (for force logout)
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

**Update `config/app.php`:**
```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

### Step 3: Create Migrations

**Users Migration (Add Phone):**
```bash
php artisan make:migration add_phone_to_users_table
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('role');
        $table->enum('role', ['super_admin', 'admin', 'cashier'])->default('cashier')->change();
    });
}
```

**Categories Migration:**
```bash
php artisan make:migration create_categories_table
```

**Products Migration:**
```bash
php artisan make:migration create_products_table
```

**Transactions Migration:**
```bash
php artisan make:migration create_transactions_table
```

**Transaction Items Migration:**
```bash
php artisan make:migration create_transaction_items_table
```

**Stock Histories Migration:**
```bash
php artisan make:migration create_stock_histories_table
```

**Settings Migration:**
```bash
php artisan make:migration create_settings_table
```

**User Sessions Migration:**
```bash
php artisan make:migration create_user_sessions_table
```

**Add Session ID to User Sessions:**
```bash
php artisan make:migration add_session_id_to_user_sessions_table
```

```php
public function up(): void
{
    Schema::table('user_sessions', function (Blueprint $table) {
        $table->string('session_id')->nullable()->after('user_id')->index();
    });
}
```

### Step 4: Update Models

#### User Model (`app/Models/User.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }
}
```

#### Category Model (`app/Models/Category.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

#### Product Model (`app/Models/Product.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'description',
        'image',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
```

#### Transaction Model (`app/Models/Transaction.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_number',
        'total_amount',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = now()->format('Ymd');
        $lastTransaction = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction 
            ? intval(substr($lastTransaction->transaction_number, -4)) + 1 
            : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
```

#### TransactionItem Model (`app/Models/TransactionItem.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

#### StockHistory Model (`app/Models/StockHistory.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity_before',
        'quantity_after',
        'quantity_changed',
        'type',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getChangeTypeLabel(): string
    {
        return match($this->type) {
            'add' => 'Stock Added',
            'subtract' => 'Stock Reduced',
            'set' => 'Stock Set',
            default => 'Stock Updated',
        };
    }

    public function getChangeTypeColor(): string
    {
        return match($this->type) {
            'add' => 'green',
            'subtract' => 'red',
            'set' => 'blue',
            default => 'gray',
        };
    }
}
```

#### Setting Model (`app/Models/Setting.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'receipt_title',
        'logo_path',
        'app_title',
        'receipt_address',
        'receipt_phone',
    ];

    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'Car Spare Parts POS',
            'receipt_title' => 'Car Spare Parts POS',
            'app_title' => 'Laravel',
        ]);
    }
}
```

#### UserSession Model (`app/Models/UserSession.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->logout_at) {
            $endTime = now();
        } else {
            $endTime = $this->logout_at;
        }

        $diff = $this->login_at->diff($endTime);
        
        $parts = [];
        if ($diff->h > 0) {
            $parts[] = $diff->h . 'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . 'm';
        }
        if ($diff->s > 0 || empty($parts)) {
            $parts[] = $diff->s . 's';
        }

        return implode(' ', $parts);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
```

### Step 5: Create Controllers

#### Product Controller with Smart SKU Generation

```bash
php artisan make:controller ProductController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Generate human-readable SKU based on category and product name
     * Format: SKU-[Category Initials][Product Initials]-###
     * Example: SKU-OEYSM-001 (Oil Engine + Yamalube Super Matic)
     */
    private function generateSku(Category $category, string $productName): string
    {
        // Get first letter of each word in category name
        $categoryWords = explode(' ', $category->name);
        $categoryPart = '';
        foreach ($categoryWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
            if (!empty($cleanWord)) {
                $categoryPart .= strtoupper(substr($cleanWord, 0, 1));
            }
        }
        
        // Get first letter of each word in product name
        $productWords = explode(' ', $productName);
        $productPart = '';
        foreach ($productWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
            if (!empty($cleanWord)) {
                $productPart .= strtoupper(substr($cleanWord, 0, 1));
            }
        }
        
        // Get next sequential number for this category
        $lastProduct = Product::where('category_id', $category->id)
            ->where('sku', 'like', 'SKU-' . $categoryPart . $productPart . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastProduct) {
            // Extract number from last SKU
            preg_match('/-(\d{3})$/', $lastProduct->sku, $matches);
            $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'SKU-' . $categoryPart . $productPart . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        $products = $query->latest()->paginate(20);
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $validated['image'] = 'images/products/' . $imageName;
        }

        $category = Category::find($validated['category_id']);
        $validated['sku'] = $this->generateSku($category, $validated['name']);

        $product = Product::create($validated);

        // Create stock history entry for new product
        $product->stockHistories()->create([
            'user_id' => auth()->id(),
            'quantity_before' => 0,
            'quantity_after' => $validated['stock_quantity'],
            'quantity_changed' => $validated['stock_quantity'],
            'type' => 'add',
            'notes' => 'Initial stock when product created',
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $validated['image'] = 'images/products/' . $imageName;
        }

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $validated['stock_quantity'];
        $quantityChanged = $newQuantity - $oldQuantity;

        $product->update($validated);

        if ($quantityChanged !== 0) {
            $product->stockHistories()->create([
                'user_id' => auth()->id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_changed' => abs($quantityChanged),
                'type' => $quantityChanged > 0 ? 'add' : 'subtract',
                'notes' => 'Stock updated via product edit',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }
        $product->delete();
        
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function showUpdateStock(Product $product)
    {
        return view('products.update-stock', compact('product'));
    }

    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $validated['stock_quantity'];
        $quantityChanged = $newQuantity - $oldQuantity;

        if ($quantityChanged !== 0) {
            $product->update(['stock_quantity' => $newQuantity]);

            $product->stockHistories()->create([
                'user_id' => auth()->id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_changed' => abs($quantityChanged),
                'type' => $quantityChanged > 0 ? 'add' : 'subtract',
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Stock updated successfully.');
    }

    public function stockHistory(Product $product)
    {
        $histories = $product->stockHistories()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('products.stock-history', compact('product', 'histories'));
    }

    public function allStockHistory(Request $request)
    {
        $query = StockHistory::with(['product', 'user']);
        
        if ($request->has('product') && $request->product) {
            $query->where('product_id', $request->product);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $histories = $query->orderBy('created_at', 'desc')->paginate(20);
        $products = Product::orderBy('name')->get();
        
        $summary = [
            'total_add' => StockHistory::where('type', 'add')->sum('quantity_changed'),
            'total_subtract' => StockHistory::where('type', 'subtract')->sum('quantity_changed'),
            'total_transactions' => StockHistory::count(),
        ];
        
        return view('products.all-stock-history', compact('histories', 'products', 'summary'));
    }

    public function editSku(Product $product)
    {
        return view('products.edit-sku', compact('product'));
    }

    public function updateSku(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
        ]);

        $oldSku = $product->sku;
        $product->update(['sku' => $validated['sku']]);

        return redirect()->route('products.index')->with('success', 'SKU updated from ' . $oldSku . ' to ' . $validated['sku']);
    }
}
```

#### Sessions Controller with Force Logout

```bash
php artisan make:controller SessionsController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        
        // Build query based on user role
        $query = UserSession::with('user')->latest('login_at');
        
        if ($currentUser->isSuperAdmin()) {
            // Super admin can see all sessions
            // No additional filter needed
        } elseif ($currentUser->isAdmin()) {
            // Admin can only see cashier sessions
            $query->whereHas('user', function ($q) {
                $q->where('role', 'cashier');
            });
        } else {
            // Cashiers shouldn't access this page at all
            abort(403, 'Unauthorized access');
        }
        
        // Filter by user if specified
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by status if specified
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }
        
        $sessions = $query->paginate(20);
        
        // Get users list for filter dropdown based on role
        if ($currentUser->isSuperAdmin()) {
            $users = User::all();
        } else {
            $users = User::where('role', 'cashier')->get();
        }
        
        // Get summary statistics
        $stats = [
            'total_sessions' => UserSession::count(),
            'active_sessions' => UserSession::where('status', 'active')->count(),
            'today_logins' => UserSession::whereDate('login_at', today())->count(),
        ];
        
        // If admin, recalculate stats for cashiers only
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $stats = [
                'total_sessions' => UserSession::whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
                'active_sessions' => UserSession::where('status', 'active')->whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
                'today_logins' => UserSession::whereDate('login_at', today())->whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
            ];
        }
        
        return view('sessions.index', compact('sessions', 'users', 'stats'));
    }
    
    public function forceLogout(UserSession $session): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Check permissions
        if ($currentUser->isSuperAdmin()) {
            // Super admin can force logout anyone
        } elseif ($currentUser->isAdmin()) {
            // Admin can only force logout cashiers
            if (!$session->user->isCashier()) {
                abort(403, 'You can only manage cashier sessions');
            }
        } else {
            abort(403, 'Unauthorized access');
        }
        
        // Cannot logout yourself
        if ($session->user_id === $currentUser->id) {
            return redirect()->route('sessions.index')
                ->with('error', 'You cannot force logout your own session');
        }
        
        // Delete the actual Laravel session from database to force immediate logout
        if ($session->session_id) {
            DB::table('sessions')->where('id', $session->session_id)->delete();
        }
        
        // Update session status
        $session->update([
            'status' => 'logged_out',
            'logout_at' => now(),
        ]);
        
        return redirect()->route('sessions.index')
            ->with('success', 'User has been force logged out successfully');
    }
}
```

#### Authenticated Session Controller (Single Device Login)

```bash
php artisan make:controller Auth/AuthenticatedSessionController
```

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Check for existing session BEFORE authenticating
        $credentials = $request->only('email', 'password');
        
        // Try to get the user first to check existing sessions
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user) {
            // Check if user already has an active session (single device login)
            $existingSession = UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingSession) {
                // Log out the existing session immediately
                if ($existingSession->session_id) {
                    DB::table('sessions')->where('id', $existingSession->session_id)->delete();
                }

                // Update the existing session record
                $existingSession->update([
                    'status' => 'logged_out',
                    'logout_at' => now(),
                ]);
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        // Update UserSession with the NEW session ID (Login event created it with old ID)
        UserSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest('login_at')
            ->first()
            ?->update(['session_id' => session()->getId()]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

### Step 6: Create Middleware

#### Role Middleware

```bash
php artisan make:middleware RoleMiddleware
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        switch ($role) {
            case 'super_admin':
                if (!$user->isSuperAdmin()) {
                    abort(403, 'Super Admin access required');
                }
                break;
            case 'admin':
                if (!$user->isAdmin()) {
                    abort(403, 'Admin access required');
                }
                break;
            case 'cashier':
                if (!$user->isCashier()) {
                    abort(403, 'Cashier access required');
                }
                break;
        }

        return $next($request);
    }
}
```

#### Validate Session Middleware

```bash
php artisan make:middleware ValidateSession
```

```php
<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ValidateSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = session()->getId();
            
            // Check if this user has been explicitly force-logged out
            $forceLoggedOutSession = UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('status', 'logged_out')
                ->first();
            
            // Only logout if explicitly marked as logged_out (force logout)
            if ($forceLoggedOutSession) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your session has been terminated by an administrator.');
            }
            
            // Update the session_id if we find an active session without session_id
            // or with a different session_id (session regeneration after login)
            UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($query) use ($sessionId) {
                    $query->whereNull('session_id')
                          ->orWhere('session_id', '!=', $sessionId);
                })
                ->latest('login_at')
                ->first()
                ?->update(['session_id' => $sessionId]);
        }
        
        return $next($request);
    }
}
```

**Register Middleware in `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
    
    // Append ValidateSession middleware to web group
    $middleware->appendToGroup('web', \App\Http\Middleware\ValidateSession::class);
})
```

### Step 7: Configure Event Listeners

**Update `app/Providers/AppServiceProvider.php`:**

```php
<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\UserSession;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/CurrencyHelper.php');
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, UserPolicy::class);

        // Share settings with all views
        View::composer('*', function ($view) {
            $view->with('settings', Setting::getSettings());
        });

        // Track user login
        Event::listen(Login::class, function (Login $event) {
            UserSession::create([
                'user_id' => $event->user->id,
                'session_id' => session()->getId(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => 'active',
            ]);
        });

        // Track user logout
        Event::listen(Logout::class, function (Logout $event) {
            // Find the most recent active session for this user
            $session = UserSession::where('user_id', $event->user->id)
                ->where('status', 'active')
                ->latest('login_at')
                ->first();
            
            if ($session) {
                $session->update([
                    'logout_at' => now(),
                    'status' => 'logged_out',
                ]);
            }
        });
    }
}
```

### Step 8: Create Routes

**File:** `routes/web.php`

```php
<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', fn() => redirect('/login'));

// Dashboard (all authenticated users)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        // Products
        Route::resource('products', ProductController::class);
        Route::get('/products/{product}/update-stock', [ProductController::class, 'showUpdateStock'])
            ->name('products.stock.show');
        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
            ->name('products.stock.update');
        Route::get('/products/{product}/stock-history', [ProductController::class, 'stockHistory'])
            ->name('products.stock.history');
        Route::get('/products/{product}/edit-sku', [ProductController::class, 'editSku'])
            ->name('products.sku.edit');
        Route::patch('/products/{product}/sku', [ProductController::class, 'updateSku'])
            ->name('products.sku.update');
        
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Users
        Route::resource('users', UserController::class);
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generateReport'])
            ->name('reports.generate');
        
        // Settings
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        
        // Sessions monitoring
        Route::get('/sessions', [SessionsController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/{session}/force-logout', [SessionsController::class, 'forceLogout'])
            ->name('sessions.force-logout');
        
        // All Stock History
        Route::get('/stock-history', [ProductController::class, 'allStockHistory'])
            ->name('stock.history.all');
    });

    // Transaction routes (all authenticated users)
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');
    Route::get('/transactions/products', [TransactionController::class, 'getProducts'])
        ->name('transactions.products');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');
    
    // Receipt download
    Route::get('/receipt/{transaction}/download', [ReportController::class, 'downloadReceipt'])
        ->name('receipt.download');
});

// Auth routes (Laravel Breeze)
require __DIR__.'/auth.php';
```

### Step 9: Create Helper Functions

**File:** `app/Helpers/CurrencyHelper.php`

```php
<?php

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     * 
     * @param float $amount The amount to format
     * @param int $decimalPlaces Number of decimal places
     * @return string Formatted currency string
     */
    function format_currency($amount, $decimalPlaces = null): string
    {
        $decimalPlaces = $decimalPlaces ?? config('currency.decimal_places', 0);
        $symbol = config('currency.symbol', 'Rp');
        $formatted = number_format(
            $amount,
            $decimalPlaces,
            config('currency.decimal_separator', ','),
            config('currency.thousands_separator', '.')
        );
        
        return $symbol . ' ' . $formatted;
    }
}

if (!function_exists('format_currency_simple')) {
    /**
     * Format amount without currency symbol
     * 
     * @param float $amount The amount to format
     * @param int $decimalPlaces Number of decimal places
     * @return string Formatted number string
     */
    function format_currency_simple($amount, $decimalPlaces = null): string
    {
        $decimalPlaces = $decimalPlaces ?? config('currency.decimal_places', 0);
        return number_format(
            $amount,
            $decimalPlaces,
            config('currency.decimal_separator', ','),
            config('currency.thousands_separator', '.')
        );
    }
}
```

### Step 10: Create Views

#### Products Index View with Clickable SKU

**File:** `resources/views/products/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your product inventory</p>
            </div>
            <a href="{{ route('products.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Add Product
            </a>
        </div>

        <div class="mb-4 flex gap-4">
            <form method="GET" action="{{ route('products.index') }}" class="flex gap-2 flex-1">
                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <select name="category" class="px-4 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 appearance-none bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->image)
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="h-12 w-12 object-cover rounded">
                                @else
                                <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">No Image</span>
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('products.sku.edit', $product) }}" class="text-blue-600 hover:text-blue-900 hover:underline" title="Click to edit SKU">
                                    {{ $product->sku }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ format_currency($product->price) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->isLowStock())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ $product->stock_quantity }} (Low)
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $product->stock_quantity }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="{{ route('products.stock.show', $product->id) }}" class="text-purple-600 hover:text-purple-900 mr-3">Update Stock</a>
                                <a href="{{ route('products.stock.history', $product->id) }}" class="text-green-600 hover:text-green-900 mr-3">History</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No products found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
```

#### All Stock History View

**File:** `resources/views/products/all-stock-history.blade.php`

```blade
@extends('layouts.app')

@section('title', 'All Stock History')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Stock History</h1>
                <p class="mt-1 text-sm text-gray-600">Complete stock movement history across all products</p>
            </div>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Back to Products
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-gray-50 border-b">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600">Total Stock Added</p>
                        <p class="text-2xl font-bold text-green-700">{{ $summary['total_add'] }}</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-red-600">Total Stock Reduced</p>
                        <p class="text-2xl font-bold text-red-700">{{ $summary['total_subtract'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600">Total Transactions</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $summary['total_transactions'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
                <form method="GET" action="{{ route('stock.history.all') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select name="product" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="add" {{ request('type') == 'add' ? 'selected' : '' }}>Stock Added</option>
                            <option value="subtract" {{ request('type') == 'subtract' ? 'selected' : '' }}>Stock Reduced</option>
                            <option value="set" {{ request('type') == 'set' ? 'selected' : '' }}>Stock Set</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Apply Filters
                        </button>
                        <a href="{{ route('stock.history.all') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($histories as $history)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $history->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('products.stock.history', $history->product) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $history->product->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $history->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $history->getChangeTypeColor() }}-100 text-{{ $history->getChangeTypeColor() }}-800">
                                    {{ $history->getChangeTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $history->quantity_before }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $history->type === 'add' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $history->type === 'add' ? '+' : '-' }}{{ $history->quantity_changed }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ $history->quantity_after }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $history->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No stock history found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $histories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
```

#### Edit SKU View

**File:** `resources/views/products/edit-sku.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Edit SKU - ' . $product->name)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit SKU</h1>
            <p class="mt-1 text-sm text-gray-600">Product: {{ $product->name }}</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form action="{{ route('products.sku.update', $product) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('sku') border-red-500 @enderror"
                            placeholder="Enter new SKU">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Current SKU: {{ $product->sku }}</p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Update SKU
                        </button>
                        <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### POS Interface with Multiple Customer Tabs

**File:** `resources/views/transactions/create.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Products Grid -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex gap-2 overflow-x-auto pb-2" id="category-filters">
                            <button type="button" 
                                data-category-id="all"
                                class="category-btn active px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white whitespace-nowrap">
                                All
                            </button>
                            @foreach($categories as $category)
                            <button type="button"
                                data-category-id="{{ $category->id }}"
                                class="category-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                                {{ $category->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="p-4">
                        <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
                            @foreach($categories as $category)
                                @foreach($category->products as $product)
                                    @if($product->stock_quantity > 0)
                                    <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition" 
                                         data-category="{{ $category->id }}">
                                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                                            @if($product->image)
                                                <img src="/{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="p-3">
                                            <h3 class="font-medium text-sm text-gray-900 truncate">{{ $product->name }}</h3>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-lg font-bold text-blue-600">{{ format_currency($product->price) }}</span>
                                                <span class="text-xs {{ $product->stock_quantity <= $product->min_stock_level ? 'text-red-600' : 'text-green-600' }}">
                                                    Stock: {{ $product->stock_quantity }}
                                                </span>
                                            </div>
                                            <button type="button" 
                                                class="add-to-cart-btn w-full mt-2 px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->stock_quantity }}">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary with Customer Tabs -->
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                    <!-- Customer Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="flex overflow-x-auto" id="customer-tabs">
                            <!-- Tabs will be generated by JavaScript -->
                        </div>
                        <button type="button" id="add-customer-btn" class="w-full px-4 py-2 bg-green-100 text-green-700 hover:bg-green-200 transition text-sm font-medium">
                            + Add Customer
                        </button>
                    </div>

                    <div class="p-4">
                        <div id="cart-items" class="space-y-3 max-h-96 overflow-y-auto mb-4">
                            <p class="text-center text-gray-500 py-8">No items in cart</p>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-lg font-semibold mb-4">
                                <span>Total:</span>
                                <span id="cart-total">{{ format_currency(0) }}</span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select id="payment-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="digital">Digital Payment</option>
                                </select>
                            </div>
                            
                            <button type="button" id="checkout-btn" disabled
                                class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed font-semibold">
                                Complete Sale
                            </button>
                            
                            <button type="button" id="clear-cart-btn"
                                class="w-full mt-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="checkout-form" method="POST" action="{{ route('transactions.store') }}" class="hidden">
    @csrf
    <input type="hidden" name="items" id="cart-items-input">
    <input type="hidden" name="payment_method" id="payment-method-input">
    <input type="hidden" name="customer_id" id="customer-id-input">
</form>
@endsection

@push('scripts')
<script>
// Currency configuration from Laravel config
const currencyConfig = {
    symbol: "<?php echo addslashes(config('currency.symbol', 'Rp')); ?>",
    decimal_places: <?php echo intval(config('currency.decimal_places', 0)); ?>,
    decimal_separator: "<?php echo addslashes(config('currency.decimal_separator', ',')); ?>",
    thousands_separator: "<?php echo addslashes(config('currency.thousands_separator', '.')); ?>",
    locale: "<?php echo addslashes(config('currency.locale', 'id-ID')); ?>"
};

// Customer management with localStorage persistence
let customers = JSON.parse(localStorage.getItem('pos_customers')) || [];
let activeCustomerId = localStorage.getItem('pos_active_customer') || null;

// Initialize with one customer if none exists
if (customers.length === 0) {
    customers = [{
        id: 'customer_1',
        name: 'Customer 1',
        cart: []
    }];
    activeCustomerId = 'customer_1';
    saveCustomers();
}

// Ensure active customer exists
if (!activeCustomerId || !customers.find(c => c.id === activeCustomerId)) {
    activeCustomerId = customers[0].id;
}

// Get current active customer
function getActiveCustomer() {
    return customers.find(c => c.id === activeCustomerId) || customers[0];
}

// Get current cart
function getCart() {
    const customer = getActiveCustomer();
    return customer ? customer.cart : [];
}

// Set current cart
function setCart(cart) {
    const customer = getActiveCustomer();
    if (customer) {
        customer.cart = cart;
        saveCustomers();
    }
}

// Save to localStorage
function saveCustomers() {
    localStorage.setItem('pos_customers', JSON.stringify(customers));
    localStorage.setItem('pos_active_customer', activeCustomerId);
}

// Format currency for display
function formatCurrency(amount) {
    const config = currencyConfig;
    const numAmount = parseFloat(amount) || 0;
    const formatted = numAmount.toLocaleString(config.locale, {
        minimumFractionDigits: config.decimal_places,
        maximumFractionDigits: config.decimal_places
    });
    return config.symbol + ' ' + formatted;
}

// Render customer tabs
function renderCustomerTabs() {
    const tabsContainer = document.getElementById('customer-tabs');
    let html = '';

    customers.forEach((customer, index) => {
        const isActive = customer.id === activeCustomerId;
        const itemCount = customer.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        html += `
            <button type="button" 
                class="customer-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition flex items-center gap-2 ${isActive ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'}"
                data-customer-id="${customer.id}">
                <span>${customer.name}</span>
                ${itemCount > 0 ? `<span style="background-color: #FACC15; color: #000000; font-size: 0.75rem; border-radius: 9999px; padding: 0.125rem 0.375rem; font-weight: 700;">${itemCount}</span>` : ''}
                ${customers.length > 1 ? `<span class="remove-customer-btn ml-1 text-gray-400 hover:text-red-500" data-customer-id="${customer.id}">&times;</span>` : ''}
            </button>
        `;
    });

    tabsContainer.innerHTML = html;

    // Add event listeners
    document.querySelectorAll('.customer-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            // Don't switch if clicking remove button
            if (e.target.classList.contains('remove-customer-btn')) {
                return;
            }
            
            const customerId = this.getAttribute('data-customer-id');
            if (customerId !== activeCustomerId) {
                activeCustomerId = customerId;
                saveCustomers();
                renderCustomerTabs();
                renderCart();
            }
        });
    });

    // Remove customer buttons
    document.querySelectorAll('.remove-customer-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const customerId = this.getAttribute('data-customer-id');
            
            if (customers.length <= 1) {
                alert('You must have at least one customer');
                return;
            }

            if (confirm('Remove this customer and their cart?')) {
                customers = customers.filter(c => c.id !== customerId);
                
                // Switch to another customer if removing active one
                if (customerId === activeCustomerId) {
                    activeCustomerId = customers[0].id;
                }
                
                saveCustomers();
                renderCustomerTabs();
                renderCart();
            }
        });
    });
}

// Add new customer
function addCustomer() {
    const newId = 'customer_' + (customers.length + 1);
    const newCustomer = {
        id: newId,
        name: 'Customer ' + (customers.length + 1),
        cart: []
    };
    customers.push(newCustomer);
    activeCustomerId = newId;
    saveCustomers();
    renderCustomerTabs();
    renderCart();
}

// Category filtering
document.querySelectorAll('.category-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const categoryId = this.getAttribute('data-category-id');
        
        // Update button styles
        document.querySelectorAll('.category-btn').forEach(function(b) {
            b.classList.remove('bg-blue-600', 'text-white');
            b.classList.add('bg-gray-200', 'text-gray-700');
        });
        this.classList.remove('bg-gray-200', 'text-gray-700');
        this.classList.add('bg-blue-600', 'text-white');
        
        // Filter products
        document.querySelectorAll('.product-card').forEach(function(card) {
            if (categoryId === 'all' || card.getAttribute('data-category') === categoryId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Add to cart buttons
document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = parseInt(this.getAttribute('data-id'));
        const name = this.getAttribute('data-name');
        const price = parseFloat(this.getAttribute('data-price'));
        const maxStock = parseInt(this.getAttribute('data-stock'));
        
        addToCart(id, name, price, maxStock);
    });
});

// Add customer button
document.getElementById('add-customer-btn').addEventListener('click', addCustomer);

// Clear cart button
document.getElementById('clear-cart-btn').addEventListener('click', function() {
    setCart([]);
    renderCart();
    renderCustomerTabs();
});

// Checkout button
document.getElementById('checkout-btn').addEventListener('click', function() {
    const cart = getCart();
    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    document.getElementById('cart-items-input').value = JSON.stringify(cart);
    document.getElementById('payment-method-input').value = document.getElementById('payment-method').value;
    document.getElementById('customer-id-input').value = activeCustomerId;
    document.getElementById('checkout-form').submit();
    
    // Clear cart after successful checkout
    setCart([]);
    saveCustomers();
});

function addToCart(id, name, price, maxStock) {
    let cart = getCart();
    const existingItem = cart.find(function(item) { return item.id === id; });
    const numPrice = parseFloat(price) || 0;
    
    if (existingItem) {
        if (existingItem.quantity < maxStock) {
            existingItem.quantity++;
        } else {
            alert('Maximum stock reached');
            return;
        }
    } else {
        cart.push({ id: id, name: name, price: numPrice, quantity: 1, maxStock: maxStock });
    }
    
    setCart(cart);
    renderCart();
    renderCustomerTabs();
}

function updateQuantity(id, change) {
    let cart = getCart();
    const item = cart.find(function(item) { return item.id === id; });
    if (item) {
        const newQty = item.quantity + change;
        if (newQty > 0 && newQty <= item.maxStock) {
            item.quantity = newQty;
        } else if (newQty <= 0) {
            cart = cart.filter(function(i) { return i.id !== id; });
        }
        setCart(cart);
        renderCart();
        renderCustomerTabs();
    }
}

function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(function(item) { return item.id !== id; });
    setCart(cart);
    renderCart();
    renderCustomerTabs();
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">No items in cart</p>';
        totalEl.textContent = 'Rp 0';
        checkoutBtn.disabled = true;
        return;
    }
    
    let total = 0;
    let html = '';
    
    cart.forEach(function(item) {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += '<div class="flex items-center justify-between border-b border-gray-100 pb-2">' +
            '<div class="flex-1">' +
                '<h4 class="font-medium text-sm text-gray-900">' + item.name + '</h4>' +
                '<p class="text-xs text-gray-500">' + formatCurrency(item.price) + ' each</p>' +
            '</div>' +
            '<div class="flex items-center gap-2">' +
                '<button type="button" class="update-qty-btn w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center" data-id="' + item.id + '" data-change="-1">-</button>' +
                '<span class="text-sm font-medium w-6 text-center">' + item.quantity + '</span>' +
                '<button type="button" class="update-qty-btn w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center" data-id="' + item.id + '" data-change="1">+</button>' +
            '</div>' +
            '<div class="text-right ml-2">' +
                '<p class="font-semibold text-sm">' + formatCurrency(itemTotal) + '</p>' +
                '<button type="button" class="remove-item-btn text-xs text-red-600 hover:text-red-800" data-id="' + item.id + '">Remove</button>' +
            '</div>' +
        '</div>';
    });
    
    container.innerHTML = html;
    totalEl.textContent = formatCurrency(total);
    checkoutBtn.disabled = false;
    
    // Attach event listeners to dynamically created buttons
    container.querySelectorAll('.update-qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            const change = parseInt(this.getAttribute('data-change'));
            updateQuantity(id, change);
        });
    });
    
    container.querySelectorAll('.remove-item-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            removeFromCart(id);
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderCustomerTabs();
    renderCart();
});

// Clear all customers data on successful checkout (handled by form submission)
document.getElementById('checkout-form').addEventListener('submit', function() {
    // The form will submit, and on next page load, we should clear the completed customer's cart
    localStorage.removeItem('pos_customers');
    localStorage.removeItem('pos_active_customer');
});
</script>
@endpush
```

---

## Features Documentation

### 1. Authentication & Authorization

**Multi-Role System:**
- Super Admin: Full system access
- Admin: Product/category management, reports, can force logout cashiers
- Cashier: Sales only, single device login enforced

**Single Device Login:**
```
Implementation:
1. User logs in → Check for existing active session
2. If exists → Delete old session from database
3. Update old session record to 'logged_out'
4. Create new session with new session_id
5. ValidateSession middleware checks on every request
6. If force-logged-out → Redirect to login with error
```

**How it works:**
- Only 1 active session per user account
- New login immediately invalidates old session
- Old browser shows error on next action
- Works across all browsers and devices

### 2. Smart SKU Generation

**Format:** `SKU-[Category Initials][Product Initials]-###`

**Examples:**
- Category: "Oil Engine" + Product: "Yamalube Super Matic"
- Result: `SKU-OEYSM-001`

**Algorithm:**
1. Extract first letter of each word in category name
2. Extract first letter of each word in product name  
3. Find last product in same category with same initials
4. Increment sequence number by 1
5. Pad with zeros to 3 digits

**Features:**
- Human-readable format
- Category-based sequential numbering
- Unique constraint enforced
- Manual editing capability via dedicated route

### 3. Inventory Management

**Stock Tracking:**
- Real-time stock quantity updates
- Minimum stock level alerts
- Automatic stock deduction on sales
- Complete audit trail with user tracking

**Stock History Types:**
- `add`: New stock added (shipments, initial creation)
- `subtract`: Stock removed (sales, damage)
- `set`: Absolute value set (inventory counts)

**History Tracking:**
- Records: Before/after quantities, change amount, user, timestamp, notes
- Views: Per-product history + Global all-stock-history with filters
- Summary: Total added, subtracted, transaction counts

### 4. Point of Sale (POS)

**Multiple Customer Tabs:**
```
Features:
- Add unlimited customers (each with separate cart)
- Switch between customers instantly
- Yellow badge with black text shows item count
- Remove customers (minimum 1 required)
- Each customer has isolated cart
```

**Persistent Cart:**
```
Storage: localStorage (browser storage)
Persistence: Survives page refresh, browser close/reopen
Clear: Only after successful checkout
```

**Cart Operations:**
- Add/remove items
- Quantity adjustment (+/-)
- Stock validation (can't exceed available)
- Real-time total calculation

### 5. Session Monitoring

**Session Tracking:**
- User ID, Session ID (for force logout), IP address, User agent
- Login time, logout time, status
- Duration calculation

**Force Logout:**
```
Process:
1. Admin/Super Admin clicks "Force Logout"
2. Delete session from Laravel sessions table
3. Update UserSession status to 'logged_out'
4. On user's next action → ValidateSession middleware detects logout
5. User redirected to login page with error message
```

**Access Control:**
- Super Admin: All sessions
- Admin: Cashier sessions only
- Cashier: No access

### 6. System Customization

**Timezone Configuration:**
- Configurable via `APP_TIMEZONE` in .env
- Default: UTC
- Example: Asia/Jakarta (UTC+7)
- All timestamps stored and displayed in configured timezone

**Settings Available:**
- Company logo (displayed in nav and receipts)
- Company name (used in reports and nav)
- Receipt title (top of printed receipts)
- Browser tab title (app_title)
- Receipt address (business address)
- Receipt phone number

**Currency Configuration:**
- Configurable symbol, decimal places, separators
- Default: Indonesian Rupiah (IDR)
- Helper functions: `format_currency()`, `format_currency_simple()`

---

## Code Reference

### Helper Functions

**File:** `app/Helpers/CurrencyHelper.php`

```php
format_currency($amount, $decimalPlaces = null)
// Returns: "Rp 1.234.567"

format_currency_simple($amount, $decimalPlaces = null)
// Returns: "1.234.567"
```

### Currency Configuration

**File:** `config/currency.php`

```php
return [
    'symbol' => 'Rp',
    'code' => 'IDR',
    'locale' => 'id-ID',
    'decimal_places' => 0,
    'thousands_separator' => '.',
    'decimal_separator' => ',',
];
```

### Model Helper Methods

**User Model:**
```php
$user->isSuperAdmin();  // boolean
$user->isAdmin();       // boolean (includes super_admin)
$user->isCashier();     // boolean
```

**Product Model:**
```php
$product->isLowStock();     // boolean
$product->isOutOfStock();   // boolean
```

**StockHistory Model:**
```php
$history->getChangeTypeLabel();  // "Stock Added", "Stock Reduced", "Stock Set"
$history->getChangeTypeColor();  // "green", "red", "blue"
```

**UserSession Model:**
```php
$session->getDurationAttribute();  // "2h 30m 45s"
$session->isActive();              // boolean
```

---

## Deployment Guide

### Production Checklist

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   APP_TIMEZONE=Asia/Jakarta
   
   DB_CONNECTION=mysql
   DB_HOST=your_database_host
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_secure_password
   
   SESSION_DRIVER=database
   SESSION_LIFETIME=120
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

3. **Optimize Laravel**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

4. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 775 public/images
   chown -R www-data:www-data storage bootstrap/cache public/images
   ```

5. **Database Setup**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

6. **Clear All Caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

### Default Login Credentials

After seeding, use these credentials:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@pos.com | password |
| Admin | admin@pos.com | password |
| Cashier | cashier@pos.com | password |

**Important:** Change default passwords immediately after first login!

### Migration Notes

**New Migration Required:**
```bash
php artisan migrate --force
```

**Migration Files:**
- `2026_03_17_121414_add_session_id_to_user_sessions_table.php` (Critical for force logout)

---

## Summary

This POS system provides:

✅ **Complete inventory management** with smart SKU generation and stock tracking  
✅ **Multi-role authentication** with single device login enforcement  
✅ **Point of Sale interface** with multiple customer tabs and persistent cart  
✅ **PDF receipts and reports** with customizable branding  
✅ **Complete stock history auditing** with global view and filtering  
✅ **System customization** (logo, branding, timezone)  
✅ **Session monitoring** with immediate force logout capability  
✅ **Responsive design** with Tailwind CSS  
✅ **Real-time dashboard** with statistics  

The system is production-ready and built with Laravel best practices including:
- Eloquent relationships
- Form request validation
- Middleware for authorization and session validation
- Database transactions for data integrity
- Event listeners for session tracking
- View composers for shared data
- Blade components for reusable UI
- Helper functions for common operations
- Single device login security

---

**End of Specification Document**

*Last Updated: March 2026*
*This document provides everything needed to understand, build, and deploy the Car Spare Parts POS System.*
