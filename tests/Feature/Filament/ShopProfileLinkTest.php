<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Shops\Pages\EditShop;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ShopProfileLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Http::fake();
        $this->actingAs(User::factory()->create());
    }

    public function test_shop_edit_header_links_to_its_public_profile_in_a_new_tab(): void
    {
        $shop = Shop::create(['name' => 'Shop', 'slug' => 'test-shop', 'confirmed' => true]);

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->assertActionVisible('viewShop')
            ->assertActionHasUrl('viewShop', route('shop.profile', ['shop_slug' => $shop->slug]))
            ->assertActionShouldOpenUrlInNewTab('viewShop')
            ->assertActionExists('delete');
    }

    public function test_profile_link_uses_the_saved_slug_while_the_form_is_being_edited(): void
    {
        $shop = Shop::create(['name' => 'Shop', 'slug' => 'saved-shop', 'confirmed' => true]);

        Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
            ->fillForm(['slug' => 'unsaved-shop'])
            ->assertActionHasUrl('viewShop', route('shop.profile', ['shop_slug' => 'saved-shop']));
    }
}
