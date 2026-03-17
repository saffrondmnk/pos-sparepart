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
- Process sales transactions
- Generate receipts and reports
- Monitor user activity
- Customize branding

### Target Users
- **Super Admin**: Full system access, can manage everything
- **Admin**: Manages products, categories, users, and views reports
- **Cashier**: Processes sales and views transaction history

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **Database**: MySQL/PostgreSQL
- **PDF Generation**: DOMPDF
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze

---

## Brainstorming & Planning

### Phase 1: Core Requirements Analysis

Before writing any code, we identified these key requirements:

```
1. USER MANAGEMENT
   - Three distinct roles (Super Admin, Admin, Cashier)
   - Secure authentication
   - Profile management

2. INVENTORY MANAGEMENT
   - Product catalog with categories
   - Stock tracking with minimum levels
   - Stock history/audit trail
   - Low stock alerts

3. SALES PROCESS
   - Point of Sale interface
   - Shopping cart functionality
   - Multiple payment methods (Cash, Card, Digital)
   - Transaction receipts

4. REPORTING
   - Sales reports with date filters
   - PDF export for receipts and reports
   - Transaction history

5. SYSTEM CUSTOMIZATION
   - Company branding (logo, name)
   - Receipt customization
   - Browser tab title

6. MONITORING
   - Track user login/logout
   - View active sessions
   - Force logout capability
```

### Phase 2: Database Design Planning

We designed the database schema to support:
- User authentication and roles
- Product inventory with categories
- Transaction processing with line items
- Stock change auditing
- System settings storage
- Session tracking

### Phase 3: UI/UX Planning

**Dashboard Layout:**
```
┌─────────────────────────────────────┐
│  Logo + Company Name    User Menu   │
├─────────────────────────────────────┤
│ Dashboard | Products | Categories   │
│ Users | New Sale | Sales History    │
│ Reports | Settings | Sessions       │
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
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SessionsController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── TransactionController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php      # Role-based access control
│   │   └── Requests/
│   │       └── Auth/
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Setting.php
│   │   ├── StockHistory.php
│   │   ├── Transaction.php
│   │   ├── TransactionItem.php
│   │   ├── User.php
│   │   └── UserSession.php
│   ├── Policies/
│   │   └── UserPolicy.php
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── currency.php                    # Custom currency config
│   └── ...
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/                          # All Blade templates
├── routes/
│   ├── web.php
│   └── auth.php
└── public/
    └── images/                         # Uploaded logos and product images
```

### Application Flow

```
1. User Login
   ↓
2. RoleMiddleware checks permissions
   ↓
3. Controller handles request
   ↓
4. Model interacts with database
   ↓
5. View renders response
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
└─────────────┘       └──────────────┘       └─────────────┘
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
│ id           │       │ id          │       │ id          │
│ company_name │       │ user_id     │◄──────┤ product_id  │
│ logo_path    │       │ ip_address  │       │ user_id     │◄────┐
│ app_title    │       │ login_at    │       │ qty_before  │     │
│ receipt_title│       │ logout_at   │       │ qty_after   │     │
└──────────────┘       │ status      │       │ type        │     │
                       └─────────────┘       └─────────────┘     │
                                                                 │
                                                                 │
┌────────────────────────────────────────────────────────────────┘
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
- `super_admin`: Can do everything, including managing other admins
- `admin`: Can manage products, categories, users (except super admin), view reports
- `cashier`: Can only create transactions and view sales history

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
- `sku`: Stock Keeping Unit - unique identifier (auto-generated as SKU-XXXXXXXX)
- `price`: Selling price to customers
- `cost_price`: Purchase price from suppliers
- `min_stock_level`: Alert threshold for low stock

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
- `add`: Added stock (e.g., new shipment arrived)
- `subtract`: Removed stock (e.g., damaged items)
- `set`: Absolute value set (e.g., inventory count adjustment)

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
Login activity tracking.

```sql
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_at TIMESTAMP NOT NULL,
    logout_at TIMESTAMP NULL,
    status ENUM('active', 'logged_out', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Implementation Guide

This section provides step-by-step instructions to build this POS system from scratch.

### Step 1: Environment Setup

#### 1.1 Install Prerequisites
```bash
# Install PHP 8.2+
# Install Composer
# Install Node.js 18+
# Install MySQL or PostgreSQL
```

#### 1.2 Create Laravel Project
```bash
composer create-project laravel/laravel pos-system
cd pos-system
```

#### 1.3 Install Required Packages
```bash
# Install Laravel Breeze (authentication scaffolding)
composer require laravel/breeze --dev
php artisan breeze:install blade

# Install DOMPDF for PDF generation
composer require barryvdh/laravel-dompdf

# Install Tailwind CSS forms plugin
npm install @tailwindcss/forms
```

#### 1.4 Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="Car Spare Parts POS"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 2: Database Setup

#### 2.1 Create Database
```bash
mysql -u root -p
CREATE DATABASE pos_system;
exit
```

#### 2.2 Run Default Migrations
```bash
php artisan migrate
```

This creates:
- users table
- password_resets table
- sessions table
- cache tables
- job tables

### Step 3: Create Models and Migrations

#### 3.1 Category
```bash
php artisan make:model Category -m
```

**Migration:**
```php
// database/migrations/xxxx_create_categories_table.php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->timestamps();
    });
}
```

**Model:**
```php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

#### 3.2 Product
```bash
php artisan make:model Product -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('sku')->unique();
        $table->string('name');
        $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        $table->text('description')->nullable();
        $table->string('image')->nullable();
        $table->decimal('price', 10, 2);
        $table->decimal('cost_price', 10, 2);
        $table->integer('stock_quantity')->default(0);
        $table->integer('min_stock_level')->default(5);
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'category_id', 'description', 'image',
        'price', 'cost_price', 'stock_quantity', 'min_stock_level'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
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

#### 3.3 Transaction
```bash
php artisan make:model Transaction -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->restrictOnDelete();
        $table->string('transaction_number')->unique();
        $table->decimal('total_amount', 10, 2);
        $table->enum('payment_method', ['cash', 'card', 'digital'])->default('cash');
        $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_number', 'total_amount',
        'payment_method', 'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

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
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$date}-{$random}";
    }
}
```

#### 3.4 Transaction Item
```bash
php artisan make:model TransactionItem -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('transaction_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->restrictOnDelete();
        $table->integer('quantity');
        $table->decimal('unit_price', 10, 2);
        $table->decimal('subtotal', 10, 2);
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'product_id', 'quantity',
        'unit_price', 'subtotal'
    ];

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

#### 3.5 Stock History
```bash
php artisan make:model StockHistory -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('stock_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->restrictOnDelete();
        $table->integer('quantity_before');
        $table->integer('quantity_after');
        $table->integer('quantity_changed');
        $table->enum('type', ['add', 'subtract', 'set']);
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'quantity_before', 'quantity_after',
        'quantity_changed', 'type', 'notes'
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
            'add' => 'Added',
            'subtract' => 'Subtracted',
            'set' => 'Set',
            default => 'Unknown'
        };
    }
}
```

#### 3.6 Settings
```bash
php artisan make:model Setting -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('company_name')->default('Car Spare Parts POS');
        $table->string('receipt_title')->default('Car Spare Parts POS');
        $table->string('logo_path')->nullable();
        $table->string('app_title')->default('Laravel');
        $table->text('receipt_address')->nullable();
        $table->string('receipt_phone')->nullable();
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name', 'receipt_title', 'logo_path',
        'app_title', 'receipt_address', 'receipt_phone'
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'company_name' => 'Car Spare Parts POS',
            'receipt_title' => 'Car Spare Parts POS',
            'app_title' => 'Laravel',
        ]);
    }
}
```

#### 3.7 User Session
```bash
php artisan make:model UserSession -m
```

**Migration:**
```php
public function up(): void
{
    Schema::create('user_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('login_at');
        $table->timestamp('logout_at')->nullable();
        $table->enum('status', ['active', 'logged_out', 'expired'])->default('active');
        $table->timestamps();
    });
}
```

**Model:**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'user_agent',
        'login_at', 'logout_at', 'status'
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
        $endTime = $this->logout_at ?? now();
        $diff = $this->login_at->diff($endTime);
        
        $parts = [];
        if ($diff->h > 0) $parts[] = $diff->h . 'h';
        if ($diff->i > 0) $parts[] = $diff->i . 'm';
        if ($diff->s > 0 || empty($parts)) $parts[] = $diff->s . 's';

        return implode(' ', $parts);
    }
}
```

#### 3.8 Update User Model
Add role checking methods to `app/Models/User.php`:

```php
public function isSuperAdmin(): bool
{
    return $this->role === 'super_admin';
}

public function isAdmin(): bool
{
    return in_array($this->role, ['super_admin', 'admin']);
}

public function isCashier(): bool
{
    return $this->role === 'cashier';
}

public function transactions(): HasMany
{
    return $this->hasMany(Transaction::class);
}
```

#### 3.9 Run All Migrations
```bash
php artisan migrate
```

### Step 4: Create Middleware

#### 4.1 Role Middleware
```bash
php artisan make:middleware RoleMiddleware
```

**File:** `app/Http/Middleware/RoleMiddleware.php`

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $hasRole = match($role) {
            'admin' => $user->isAdmin(),
            'super_admin' => $user->isSuperAdmin(),
            'cashier' => $user->isCashier(),
            default => false
        };

        if (!$hasRole) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
```

#### 4.2 Register Middleware
**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

### Step 5: Create Controllers

#### 5.1 Dashboard Controller
```bash
php artisan make:controller DashboardController
```

```php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        
        $todaySales = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');
            
        $todayTransactions = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();
            
        $totalProducts = Product::count();
        
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->orWhere('stock_quantity', '<=', 0)
            ->with('category')
            ->get();
            
        $totalRevenue = Transaction::where('status', 'completed')->sum('total_amount');
        
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'todaySales', 'todayTransactions', 'totalProducts',
            'lowStockProducts', 'totalRevenue', 'recentTransactions'
        ));
    }
}
```

#### 5.2 Category Controller
```bash
php artisan make:controller CategoryController
```

```php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with products');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
```

#### 5.3 Product Controller (Key Methods)
```bash
php artisan make:controller ProductController
```

**Key Methods:**

```php
// List products with filters
public function index(Request $request): View
{
    $query = Product::with('category');
    
    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%")
              ->orWhere('sku', 'like', "%{$request->search}%");
    }
    
    if ($request->category) {
        $query->where('category_id', $request->category);
    }
    
    $products = $query->latest()->paginate(20);
    $categories = Category::all();
    
    return view('products.index', compact('products', 'categories'));
}

// Create product with image upload
public function store(Request $request): RedirectResponse
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

    $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));

    Product::create($validated);

    return redirect()->route('products.index')
        ->with('success', 'Product created successfully');
}

// Update stock with history tracking
public function updateStock(Request $request, Product $product): RedirectResponse
{
    $validated = $request->validate([
        'quantity' => 'required|integer',
        'type' => 'required|in:add,subtract,set',
        'notes' => 'nullable|string',
    ]);

    $quantityBefore = $product->stock_quantity;
    
    switch ($validated['type']) {
        case 'add':
            $product->stock_quantity += $validated['quantity'];
            break;
        case 'subtract':
            $product->stock_quantity = max(0, $product->stock_quantity - $validated['quantity']);
            break;
        case 'set':
            $product->stock_quantity = max(0, $validated['quantity']);
            break;
    }
    
    $product->save();

    // Record in stock history
    StockHistory::create([
        'product_id' => $product->id,
        'user_id' => auth()->id(),
        'quantity_before' => $quantityBefore,
        'quantity_after' => $product->stock_quantity,
        'quantity_changed' => $product->stock_quantity - $quantityBefore,
        'type' => $validated['type'],
        'notes' => $validated['notes'],
    ]);

    return redirect()->route('products.index')
        ->with('success', 'Stock updated successfully');
}
```

#### 5.4 Transaction Controller (POS System)
```bash
php artisan make:controller TransactionController
```

**Key Methods:**

```php
// Show POS interface
public function create(): View
{
    $categories = Category::with(['products' => function ($query) {
        $query->where('stock_quantity', '>', 0);
    }])->get();
    
    return view('transactions.create', compact('categories'));
}

// Process sale transaction
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'payment_method' => 'required|in:cash,card,digital',
    ]);

    DB::beginTransaction();
    
    try {
        $totalAmount = 0;
        $transactionItems = [];

        // Calculate totals and validate stock
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if ($product->stock_quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for {$product->name}");
            }
            
            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;
            
            $transactionItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
            ];

            // Deduct stock
            $product->stock_quantity -= $item['quantity'];
            $product->save();
        }

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'transaction_number' => Transaction::generateTransactionNumber(),
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'status' => 'completed',
        ]);

        // Create transaction items
        foreach ($transactionItems as $item) {
            $transaction->items()->create($item);
        }

        DB::commit();

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction completed successfully');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', $e->getMessage());
    }
}
```

#### 5.5 User Controller
```bash
php artisan make:controller UserController
```

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isSuperAdmin()) {
            $users = User::all();
        } else {
            $users = User::where('role', '!=', 'super_admin')->get();
        }
        
        return view('users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,cashier',
            'phone' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,cashier',
            'phone' => 'nullable|string',
        ]);

        // Prevent changing own role
        if ($user->id === auth()->id() && $validated['role'] !== $user->role) {
            return redirect()->back()
                ->with('error', 'You cannot change your own role');
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account');
        }

        // Only super admin can delete admins
        if ($user->isAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Only Super Admin can delete admin accounts');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
```

#### 5.6 Report Controller
```bash
php artisan make:controller ReportController
```

```php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('user');
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->where('status', 'completed')->get();
        $totalSales = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        
        return view('reports.index', compact('transactions', 'totalSales', 'totalTransactions'));
    }

    public function generateReport(Request $request)
    {
        $query = Transaction::with('user');
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->where('status', 'completed')->get();
        $totalSales = $transactions->sum('total_amount');
        $settings = Setting::getSettings();
        
        $pdf = Pdf::loadView('reports.pdf', [
            'transactions' => $transactions,
            'totalSales' => $totalSales,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'settings' => $settings,
        ]);
        
        return $pdf->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadReceipt(Transaction $transaction)
    {
        $transaction->load(['user', 'items.product']);
        $settings = Setting::getSettings();
        
        $pdf = Pdf::loadView('transactions.receipt', compact('transaction', 'settings'));
        
        return $pdf->download('receipt-' . $transaction->transaction_number . '.pdf');
    }
}
```

#### 5.7 Settings Controller
```bash
php artisan make:controller SettingsController
```

```php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::getSettings();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'receipt_title' => 'required|string|max:255',
            'app_title' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'receipt_address' => 'nullable|string|max:500',
            'receipt_phone' => 'nullable|string|max:20',
        ]);

        $settings = Setting::getSettings();

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo_' . time() . '_' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images'), $logoName);
            
            // Delete old logo if exists
            if ($settings->logo_path && file_exists(public_path($settings->logo_path))) {
                unlink(public_path($settings->logo_path));
            }
            
            $validated['logo_path'] = 'images/' . $logoName;
        }

        unset($validated['logo']);
        $settings->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings updated successfully');
    }
}
```

#### 5.8 Sessions Controller
```bash
php artisan make:controller SessionsController
```

```php
namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SessionsController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        $query = UserSession::with('user')->latest('login_at');
        
        // Role-based filtering
        if ($currentUser->isSuperAdmin()) {
            // Super admin sees all sessions
        } elseif ($currentUser->isAdmin()) {
            // Admin sees only cashier sessions
            $query->whereHas('user', function ($q) {
                $q->where('role', 'cashier');
            });
        } else {
            abort(403, 'Unauthorized access');
        }
        
        // Apply filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->date_from) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }
        
        $sessions = $query->paginate(20);
        
        // Get users for filter dropdown
        if ($currentUser->isSuperAdmin()) {
            $users = User::all();
        } else {
            $users = User::where('role', 'cashier')->get();
        }
        
        // Calculate statistics
        $stats = $this->calculateStats($currentUser);
        
        return view('sessions.index', compact('sessions', 'users', 'stats'));
    }
    
    private function calculateStats($currentUser): array
    {
        $query = UserSession::query();
        
        if (!$currentUser->isSuperAdmin()) {
            $query->whereHas('user', function ($q) {
                $q->where('role', 'cashier');
            });
        }
        
        return [
            'total_sessions' => (clone $query)->count(),
            'active_sessions' => (clone $query)->where('status', 'active')->count(),
            'today_logins' => (clone $query)->whereDate('login_at', today())->count(),
        ];
    }
    
    public function forceLogout(UserSession $session): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Permission checks
        if ($currentUser->isSuperAdmin()) {
            // Can logout anyone
        } elseif ($currentUser->isAdmin()) {
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
        
        $session->update([
            'status' => 'logged_out',
            'logout_at' => now(),
        ]);
        
        return redirect()->route('sessions.index')
            ->with('success', 'User has been force logged out');
    }
}
```

### Step 6: Configure Event Listeners for Session Tracking

**File:** `app/Providers/AppServiceProvider.php`

```php
namespace App\Providers;

use App\Models\Setting;
use App\Models\UserSession;
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
        // Share settings with all views
        View::composer('*', function ($view) {
            $view->with('settings', Setting::getSettings());
        });

        // Track user login
        Event::listen(Login::class, function (Login $event) {
            UserSession::create([
                'user_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => 'active',
            ]);
        });

        // Track user logout
        Event::listen(Logout::class, function (Logout $event) {
            UserSession::where('user_id', $event->user->id)
                ->where('status', 'active')
                ->latest('login_at')
                ->first()
                ?->update([
                    'logout_at' => now(),
                    'status' => 'logged_out',
                ]);
        });
    }
}
```

### Step 7: Create Routes

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

### Step 8: Create Database Seeders

#### 8.1 User Seeder
**File:** `database/seeders/UserSeeder.php`

```php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '081234567890',
        ]);

        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567891',
        ]);

        // Cashier
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@pos.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'phone' => '081234567892',
        ]);
    }
}
```

#### 8.2 Database Seeder
**File:** `database/seeders/DatabaseSeeder.php`

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
```

### Step 9: Run Seeders

```bash
php artisan db:seed
```

### Step 10: Create Required Directories

```bash
mkdir -p public/images/products
mkdir -p public/images
chmod -R 775 public/images
```

---

## Features Documentation

### 1. Multi-Role Authentication System

**Three User Roles:**

| Role | Permissions |
|------|-------------|
| **Super Admin** | Full system access, can manage admins, view all sessions |
| **Admin** | Manage products, categories, users (except super admin), view reports and cashier sessions |
| **Cashier** | Create transactions, view sales history, download receipts |

**Implementation:**
- Laravel Breeze provides authentication scaffolding
- Custom `RoleMiddleware` restricts access based on roles
- Role checks in User model: `isSuperAdmin()`, `isAdmin()`, `isCashier()`

### 2. Inventory Management

**Product Features:**
- SKU auto-generation (format: SKU-XXXXXXXX)
- Image upload with validation
- Stock quantity tracking
- Minimum stock level alerts
- Stock history audit trail

**Stock Management:**
```
When product is created: stock_quantity is set
When product is sold: stock_quantity decreases
When stock is updated: history is recorded with:
  - Who made the change
  - Before/after quantities
  - Type of change (add/subtract/set)
  - Notes
```

**Low Stock Alerts:**
- Dashboard shows products with stock ≤ min_stock_level
- Visual indicators in product list

### 3. Point of Sale (POS) Interface

**Features:**
- Category-based product filtering
- Product search functionality
- Shopping cart with quantity management
- Multiple payment methods (Cash, Card, Digital)
- Real-time total calculation
- Automatic stock deduction on sale
- Transaction number generation (TXN-YYYYMMDD-XXXX)

**Transaction Flow:**
```
1. Cashier clicks "New Sale"
2. Selects products from categories
3. Adds products to cart
4. Adjusts quantities if needed
5. Selects payment method
6. Clicks "Complete Sale"
7. System:
   - Generates transaction number
   - Deducts stock
   - Creates transaction record
   - Creates transaction items
   - Redirects to receipt
```

### 4. PDF Generation

**Receipts:**
- Generated using DOMPDF
- Customizable with company logo, name, address
- Shows transaction details, items, totals
- Downloadable as PDF

**Reports:**
- Date range filtering
- Summary statistics (total sales, transaction count)
- Detailed transaction list
- PDF export

### 5. System Customization

**Settings Available:**
- Company logo upload (displayed in nav and receipts)
- Company name (used in reports and nav)
- Receipt title (top of printed receipts)
- Browser tab title
- Receipt address (business address)
- Receipt phone number

**How It Works:**
- Settings stored in database
- Shared with all views via View Composer
- Used in:
  - Navigation logo and name
  - Page titles
  - Receipts
  - Reports
  - Login page

### 6. Session Monitoring

**Features:**
- Tracks every login/logout
- Records IP address and browser
- Shows session duration
- Filter by user, status, date
- Statistics dashboard
- Force logout capability

**Access Control:**
- Super Admin: See all users' sessions
- Admin: See only cashier sessions
- Cashier: No access

**Session States:**
- `active`: User is currently logged in
- `logged_out`: User logged out normally
- `expired`: Session timed out

---

## Code Reference

### Helper Functions

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
    function format_currency($amount, $decimalPlaces = 0): string
    {
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
    function format_currency_simple($amount, $decimalPlaces = 0): string
    {
        return number_format(
            $amount,
            $decimalPlaces,
            config('currency.decimal_separator', ','),
            config('currency.thousands_separator', '.')
        );
    }
}
```

### Currency Configuration

**File:** `config/currency.php`

```php
<?php

return [
    'symbol' => 'Rp',
    'code' => 'IDR',
    'locale' => 'id-ID',
    'decimal_places' => 0,
    'thousands_separator' => '.',
    'decimal_separator' => ',',
];
```

---

## Deployment Guide

### Production Checklist

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=your_database_host
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_secure_password
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

6. **Security Headers**
   Ensure your web server adds security headers:
   - X-Frame-Options: DENY
   - X-Content-Type-Options: nosniff
   - X-XSS-Protection: 1; mode=block
   - Strict-Transport-Security: max-age=31536000; includeSubDomains

### Default Login Credentials

After seeding, use these credentials:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@pos.com | password |
| Admin | admin@pos.com | password |
| Cashier | cashier@pos.com | password |

**Important:** Change default passwords immediately after first login!

---

## Summary

This POS system provides:

✅ **Complete inventory management** with stock tracking  
✅ **Multi-role authentication** (Super Admin, Admin, Cashier)  
✅ **Point of Sale interface** with cart and checkout  
✅ **PDF receipts and reports**  
✅ **Stock history auditing**  
✅ **System customization** (logo, branding)  
✅ **Session monitoring** with force logout  
✅ **Responsive design** with Tailwind CSS  
✅ **Real-time dashboard** with statistics  

The system is production-ready and built with Laravel best practices including:
- Eloquent relationships
- Form request validation
- Middleware for authorization
- Database transactions for data integrity
- Event listeners for session tracking
- View composers for shared data
- Blade components for reusable UI

---

**End of Specification Document**

*This document provides everything needed to understand, build, and deploy the Car Spare Parts POS System from scratch.*