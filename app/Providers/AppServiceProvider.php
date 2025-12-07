<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Shift;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockTransfer;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Policies\AttendancePolicy;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\ItemPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\BrandPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\LocationPolicy;
use App\Policies\StockPolicy;
use App\Policies\StockInPolicy;
use App\Policies\StockOutPolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\StockMovementPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Attendance Management Policies
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        
        // Warehouse Management Policies
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Stock::class, StockPolicy::class);
        Gate::policy(StockIn::class, StockInPolicy::class);
        Gate::policy(StockOut::class, StockOutPolicy::class);
        Gate::policy(StockTransfer::class, StockTransferPolicy::class);
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
    }
}
