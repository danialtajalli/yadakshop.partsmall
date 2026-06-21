<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PartCategories\Pages\CreatePartCategory;
use App\Filament\Resources\PartCategories\Pages\EditPartCategory;
use App\Filament\Resources\PartCategories\Pages\ListPartCategories;
use App\Filament\Resources\PartCategories\PartCategoryResource;
use App\Filament\Resources\PartCategories\RelationManagers\PartsRelationManager;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PartCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_part_category_pages(): void
    {
        $category = PartsCategory::create(['name' => 'جلوبندی']);

        $this->actingAs(User::factory()->create());

        $this->get(PartCategoryResource::getUrl('index'))->assertOk();
        $this->get(PartCategoryResource::getUrl('create'))->assertOk();
        $this->get(PartCategoryResource::getUrl('edit', ['record' => $category]))->assertOk();
    }

    public function test_list_page_shows_and_searches_part_categories(): void
    {
        $visible = PartsCategory::create(['name' => 'جلوبندی']);
        $hidden = PartsCategory::create(['name' => 'بدنه']);

        $this->actingAs(User::factory()->create());

        Livewire::test(ListPartCategories::class)
            ->assertCanSeeTableRecords([$visible, $hidden])
            ->searchTable('جلوبندی')
            ->assertCanSeeTableRecords([$visible])
            ->assertCanNotSeeTableRecords([$hidden]);
    }

    public function test_create_page_can_create_part_category(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePartCategory::class)
            ->fillForm([
                'name' => 'روشنایی',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('parts_categories', [
            'name' => 'روشنایی',
        ]);
    }

    public function test_edit_page_can_update_part_category(): void
    {
        $category = PartsCategory::create(['name' => 'قدیمی']);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditPartCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'موتوری',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('parts_categories', [
            'id' => $category->id,
            'name' => 'موتوری',
        ]);
    }

    public function test_parts_relation_manager_lists_related_parts(): void
    {
        $category = PartsCategory::create(['name' => 'جلوبندی']);
        $related = Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
        ]);
        $unrelatedCategory = PartsCategory::create(['name' => 'بدنه']);
        $unrelated = Part::create([
            'name' => 'سپر جلو',
            'slug' => 'front-bumper',
            'parts_category_id' => $unrelatedCategory->id,
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(PartsRelationManager::class, [
            'ownerRecord' => $category,
            'pageClass' => EditPartCategory::class,
        ])
            ->assertCanSeeTableRecords([$related])
            ->assertCanNotSeeTableRecords([$unrelated]);
    }
}
