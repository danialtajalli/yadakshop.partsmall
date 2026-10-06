<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\ShopsRelationManager;
use App\Filament\Resources\Parts\Pages\EditPart;
use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Filament\Resources\Shops\Pages\EditShop;
use App\Filament\Resources\Shops\Pages\ListShops;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ShopConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_list_approve_and_unconfirm_a_shop(): void
    {
        $shop = Shop::create(['name' => 'Pending shop', 'slug' => 'pending-shop', 'confirmed' => false]);

        Livewire::test(ListShops::class)->assertCanSeeTableRecords([$shop]);
        $this->assertNull(Shop::find($shop->id));

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertFormSet(['confirmed' => false])
            ->fillForm(['confirmed' => true])->call('save')->assertHasNoFormErrors();
        $this->assertNotNull(Shop::find($shop->id));

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->fillForm(['confirmed' => false])->call('save')->assertHasNoFormErrors();
        $this->assertNull(Shop::find($shop->id));
        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertFormSet(['confirmed' => false]);
    }

    public function test_admin_can_create_a_pending_shop(): void
    {
        Livewire::test(CreateShop::class)->fillForm([
            'name' => 'New pending shop',
            'slug' => 'new-pending-shop',
            'confirmed' => false,
            'show_under_product' => true,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
        ])->call('create')->assertHasNoFormErrors();

        $shop = Shop::withoutGlobalScope('confirmed')->where('slug', 'new-pending-shop')->firstOrFail();
        $this->assertNull(Shop::find($shop->id));
        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertFormSet(['confirmed' => false]);
    }

    public function test_company_relation_manager_shows_pending_shops(): void
    {
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        $shop = Shop::create(['name' => 'Pending shop', 'slug' => 'pending-shop', 'confirmed' => false]);
        $company->shops()->attach($shop);

        Livewire::test(ShopsRelationManager::class, [
            'ownerRecord' => $company,
            'pageClass' => EditCompany::class,
        ])->assertCanSeeTableRecords([$shop]);
        $this->assertSame(0, $company->shops()->count());
    }

    public function test_part_editor_preserves_pending_shop_assignments(): void
    {
        $category = PartsCategory::create(['name' => 'Category']);
        $part = Part::create(['name' => 'Part', 'slug' => 'part', 'parts_category_id' => $category->id]);
        $shop = Shop::create(['name' => 'Pending shop', 'slug' => 'pending-shop', 'confirmed' => false]);
        $part->shops()->attach($shop);

        Livewire::test(EditPart::class, ['record' => $part->getRouteKey()])
            ->assertFormSet(['shops_id' => [(string) $shop->id]])
            ->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseHas('part_shop', ['part_id' => $part->id, 'shop_id' => $shop->id]);
        $this->assertSame(0, $part->shops()->count());
    }
}
