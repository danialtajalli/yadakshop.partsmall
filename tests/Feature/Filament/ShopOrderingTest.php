<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Filament\Resources\Shops\Pages\EditShop;
use App\Filament\Resources\Shops\Pages\ListShops;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShopOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_create_and_edit_both_orders_independently(): void
    {
        Livewire::test(CreateShop::class)->fillForm([
            'name' => 'Shop',
            'slug' => 'shop',
            'confirmed' => true,
            'show_under_product' => true,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
            'order' => 10,
            'home_order' => 20,
        ])->call('create')->assertHasNoFormErrors();
        $shop = Shop::where('slug', 'shop')->firstOrFail();

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertFormSet(['order' => 10, 'home_order' => 20])
            ->fillForm(['home_order' => 2])->call('save')->assertHasNoFormErrors();
        $this->assertSame(10, $shop->fresh()->order);
        $this->assertSame(2, $shop->fresh()->home_order);

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->fillForm(['order' => 5])->call('save')->assertHasNoFormErrors();
        $this->assertSame(5, $shop->fresh()->order);
        $this->assertSame(2, $shop->fresh()->home_order);
    }

    public function test_admin_table_can_sort_by_each_order(): void
    {
        $first = Shop::create(['name' => 'First', 'slug' => 'first', 'order' => 20, 'home_order' => 1]);
        $second = Shop::create(['name' => 'Second', 'slug' => 'second', 'order' => 10, 'home_order' => 2]);

        Livewire::test(ListShops::class)
            ->assertTableColumnExists('home_order')
            ->sortTable('home_order')->assertCanSeeTableRecords([$first, $second], inOrder: true)
            ->sortTable('order')->assertCanSeeTableRecords([$second, $first], inOrder: true);
    }

    #[DataProvider('invalidHomeOrders')]
    public function test_home_order_requires_a_non_negative_integer(int|float $order): void
    {
        $shop = Shop::create(['name' => 'Shop', 'slug' => 'shop', 'confirmed' => true, 'order' => 10, 'home_order' => 20]);

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->fillForm(['home_order' => $order])->call('save')->assertHasFormErrors(['home_order']);

        $this->assertSame(20, $shop->fresh()->home_order);
    }

    public static function invalidHomeOrders(): array
    {
        return ['negative' => [-1], 'fractional' => [1.5]];
    }
}
