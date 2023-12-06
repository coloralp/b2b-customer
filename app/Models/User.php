<?php

namespace App\Models;


use App\Enums\RoleEnum;
use App\Services\PriceService;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity, SoftDeletes, MustVerifyEmail;

    public const CUSTOMER_OPTIONS = ['email', 'password', 'location', 'company_name', 'address', 'vat_number', 'company_registration_number', 'web_site', 'info', 'payment_method'];

    protected $guarded = [];


    public const EMAIL_FRONT = 'frontenddeveloper@gmail.com';
    public const FRONT_MAIL = 'mailto:tornadocoder@gmail.com';//front123 password

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(RoleEnum::MANAGER);

    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'text']);
        // Chain fluent methods for configuration options
    }

    public function getLogNameToUse(): string
    {
        return 'User';
    }

    protected function getDefaultGuardName(): string
    {
        return 'api';
    }


    protected function minBalance(): Attribute
    {
        return Attribute::make(
            get: fn($value) => PriceService::convertFloat($value)
        );
    }

    //relations

    public function roleOptions(): HasMany
    {
        return $this->hasMany(RoleOption::class);
    }

    public function getOption(string $option, string $role): ?RoleOption
    {
        $roleOptions = $this->relationLoaded('roleOptions')
            ? $this->roleOptions
            : $this->roleOptions();


        return $roleOptions->where('role', $role)
            ->where('option', $option)
            ->first();
    }

    public function myOfferUpdates(): HasMany
    {
        return $this->hasMany(ApiOfferPriceUpdate::class, 'who');
    }


    //user base functions

    //for Suppliers

    public function supplierInfo(): HasOne
    {
        return $this->hasOne(SupplierInfo::class, 'supplier_id', 'id');
    }


    //for publisher

    public function publisherInfo(): HasOne
    {
        return $this->hasOne(PublisherInfo::class, 'publisher_id', 'id');
    }


    //customer and supplier

    //kullanılmayan
    public function currencyChanges(): HasMany
    {
        return $this->hasMany(CurrencyChange::class, 'change_by', 'id');
    }


    //for suppliers and customers

    public function jar(): HasOne
    {
        return $this->hasOne(Jar::class, 'owner_id', 'id');
    }

    public function jarTransactionRequests(): HasMany
    {
        return $this->hasMany(JarTransactionsRequest::class, 'processed_by');
    }

    public function basketItems(): HasMany
    {
        return $this->hasMany(BasketItem::class, 'who');
    }

    //for publishers

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'publisher_id');
    }


    //for suppliers

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'supplier_id');
    }

    public function twoFactory(): HasOne
    {
        return $this->hasOne(UserTwoFactory::class, 'who');
    }


}
