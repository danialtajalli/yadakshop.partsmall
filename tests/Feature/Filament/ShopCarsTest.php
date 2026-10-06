<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Filament\Resources\Shops\Pages\EditShop;
use App\Models\Car;
use App\Models\Company;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ShopCarsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_create_edit_and_clear_shop_car_selections(): void
    {
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        $firstCar = Car::create(['name' => 'First car', 'slug' => 'first-car', 'company_id' => $company->id]);
        $secondCar = Car::create(['name' => 'Second car', 'slug' => 'second-car', 'company_id' => $company->id]);

        Livewire::test(CreateShop::class)
            ->fillForm([
                'name' => 'Car shop',
                'slug' => 'car-shop',
                'confirmed' => true,
                'show_under_product' => true,
                'open_time' => '09:00:00',
                'close_time' => '18:00:00',
                'cars_id' => [$firstCar->id, $secondCar->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $shop = Shop::where('slug', 'car-shop')->firstOrFail();
        $this->assertEqualsCanonicalizing([$firstCar->id, $secondCar->id], $shop->cars()->pluck('cars.id')->all());
        $this->assertSame([$shop->id], $firstCar->shops()->pluck('shops.id')->all());

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertFormSet(['cars_id' => [$firstCar->id, $secondCar->id]])
            ->fillForm(['cars_id' => [$secondCar->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$secondCar->id], $shop->cars()->pluck('cars.id')->all());

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->fillForm(['cars_id' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(0, $shop->cars()->count());
    }

    public function test_car_shop_rows_are_removed_when_either_record_is_deleted(): void
    {
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        $car = Car::create(['name' => 'Car', 'slug' => 'car', 'company_id' => $company->id]);
        $shop = Shop::create(['name' => 'Shop', 'slug' => 'shop']);
        $shop->cars()->attach($car);
        $car->delete();
        $this->assertDatabaseCount('car_shop', 0);

        $car = Car::create(['name' => 'Other car', 'slug' => 'other-car', 'company_id' => $company->id]);
        $shop->cars()->attach($car);
        $shop->delete();
        $this->assertDatabaseCount('car_shop', 0);
    }
}
