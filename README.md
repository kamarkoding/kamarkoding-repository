# Kamarkoding Repository Generator

Generator sederhana untuk membuat **Repository Pattern** di Laravel secara otomatis.  
Package ini membantu menjaga arsitektur aplikasi tetap rapi, terstruktur, dan mengikuti prinsip **SOLID**.

---

## Fitur Utama

- Membuat **Repository Interface** dan **Repository Class** hanya dengan satu perintah.
- Struktur folder otomatis dibuat:
  - `app/Repository/Contracts`
  - `app/Repository/Eloquent`
- Binding otomatis interface → implementasi (tanpa perlu menambahkan di `AppServiceProvider`)
- Menggunakan Laravel Package Auto-Discovery (tanpa daftar provider manual).
- Kode bersih, ringan, dan cocok untuk aplikasi kecil maupun besar.

---

## Instalasi

### 1. Tambahkan repository (jika lokal)

Jika package disimpan secara lokal:

```json
"repositories": [
    {
        "type": "path",
        "url": "../kamarkoding-repository"
    }
]
```
2. Install melalui Composer
```
composer require kamarkoding/kamarkoding-repository
```
Laravel otomatis mendaftarkan Service Provider melalui auto-discovery.

Jika auto-discovery dimatikan, daftar manual di 
```config/app.php: ```

```php
'providers' => [
    Kamarkoding\KamarkodingRepository\Providers\RepositoryServiceProvider::class,
];
```

## Membuat Repository Baru
### Gunakan perintah berikut:

```php
php artisan make:repository User
```
Output :
```
Created: Class UserRepositoryInterface.php
Created: Class UserRepository.php
Repository created successfully.
```
```
app/
└── Repository/
    ├── Contracts/
    │   └── UserRepositoryInterface.php
    └── Eloquent/
        └── UserRepository.php
```

## Struktur Folder
### Struktur lengkap setelah membuat repository:
```
app/
└── Repository/
    ├── Contracts/
    │   └── <Name>RepositoryInterface.php
    └── Eloquent/
        └── <Name>Repository.php
```
File: Interface
```
<?php

namespace App\Repository\Contracts;

interface UserRepositoryInterface
{
    //
}
```

File: Implementasi
```
<?php

namespace App\Repository\Eloquent;

use App\Repository\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    //
}
```

## Binding Otomatis
Package ini otomatis menghubungkan:
```josn
UserRepositoryInterface::class → UserRepository::class
```
Tidak perlu lagi menambahkan:
```josn
$this->app->bind(UserRepositoryInterface::class, ModuleRepository::class);
```

## Penggunaan dalam Controller Laravel
```josn
<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    protected $Users;

    public function __construct(UserRepositoryInterface $Users)
    {
        $this->Users = $Users;
    }

    public function index()
    {
        $data = $this->Users->getAll(); // contoh method
        return view('Users.index', compact('data'));
    }
}
```
## Penggunaan di Livewire Component
```josn
<?php

use Livewire\Component;
use App\Repository\Contracts\UserRepositoryInterface;

class UserIndex extends Component
{
    public UserRepositoryInterface $Users;

    public function mount(UserRepositoryInterface $Users)
    {
        $this->Users = $Users;
    }

    public function delete($id)
    {
        $this->Users->delete($id);
        $this->dispatch('toast-success', message: "User deleted");
    }

    public function render()
    {
        return view('livewire.user.index');
    }
}
```

Atau menggunakan resolver otomatis:
```josn
public function getRepository()
{
    return app(ModuleRepositoryInterface::class);
}
```

## Penjelasan Arsitektur
### Repository Pattern membantu:
1. Memisahkan business logic dari data layer
2. Mengurangi duplikasi query
3. Meningkatkan testability (mocking jadi mudah)
4. Memperjelas struktur project
Memudahkan perubahan dari Eloquent ke Query Builder atau API tanpa mengubah Controller