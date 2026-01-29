<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Role;
use App\Models\Image;
use App\Models\Driver;
use App\Models\Record;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Category;
use App\Models\Product;
use App\Models\Permission;
use App\Models\Destination;
use App\Models\Maintenances;
use App\Models\TypeMaintenances;
use App\Models\MaintenancesDetails;
use App\Models\Note;
use App\Models\NotificationService;
use App\Observers\CrudActivityObserver;
use Illuminate\Support\ServiceProvider;

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
        require_once app_path('Helpers/FormatRupiah.php');

        $observer = CrudActivityObserver::class;

        User::observe($observer);
        Role::observe($observer);
        Permission::observe($observer);
        Image::observe($observer);
        Category::observe($observer);
        Product::observe($observer);
        Company::observe($observer);
        Driver::observe($observer);
        Vehicle::observe($observer);
        Destination::observe($observer);
        TypeMaintenances::observe($observer);
        Record::observe($observer);
        Maintenances::observe($observer);
        MaintenancesDetails::observe($observer);
        Note::observe($observer);
        NotificationService::observe($observer);
    }
}
