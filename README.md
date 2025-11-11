# Setup Project

## Clone Repositori

```
git clone https://github.com/sirius-leaf/ZenithNew2.git
cd ZenithNew2
```

## A. Setup Backend

### 1. Masuk folder backend

```
cd backend
```

### 2. Install dependencies dengan Composer

```
composer install
```

### 3. Copy file environment

```
cp .env.example .env
```

Edit isi file environment untuk menyesuaikan dengan database

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zenith
DB_USERNAME=root
DB_PASSWORD=
```

Edit lokasi storage

```
FILESYSTEM_DISK=public
```

### 4. Generate application key

```
php artisan key:generate
```

### 5. Jalankan migrasi database

```
php artisan migrate
```

Seeding database (Opsional)

```
php artisan db:seed
```

### 6. Jalankan server Laravel

```
php artisan serve
```

## B. Setup frontend

kembali ke root project terlebih dahulu jika belum

```
cd ..
```

### 1. Masuk folder frontend

```
cd frontend
```

### 2. Install dependencies frontend

```
npm install
```

### 3. Jalankan development server frontend

```
npm run dev
```
