<?php

namespace Tests\Feature\Filament;

use App\Enums\ImageType;
use App\Filament\Resources\Images\Pages\CreateImage;
use App\Filament\Resources\Images\Pages\EditImage;
use App\Filament\Resources\Images\Pages\ListImages;
use App\Filament\Resources\Representations\Pages\EditRepresentation;
use App\Models\Company;
use App\Models\Image;
use App\Models\RepairShop;
use App\Models\Representation;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImageAltTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Http::fake();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    #[DataProvider('imageOwners')]
    public function test_admin_can_create_edit_and_clear_image_alt_text(string $modelClass, string $foreignKey): void
    {
        $owner = $modelClass::create(['name' => 'Owner', 'slug' => 'owner', 'confirmed' => true]);
        $create = Livewire::test(CreateImage::class)->fillForm([
            'type' => ImageType::Logo->value, $foreignKey => $owner->id, 'alt' => '  Custom logo  ',
        ])->fillForm([
            'path' => [UploadedFile::fake()->image('logo.png')],
        ]);
        $this->assertNotEmpty($create->get('data.path'), 'Upload missing before creation');
        $create->call('create')->assertHasNoFormErrors();

        $image = Image::firstOrFail();
        $this->assertSame('Custom logo', $image->alt);
        $this->assertSame($owner->id, $image->{$foreignKey});
        $this->assertNotEmpty(Storage::disk('public')->allFiles(), 'Upload was not stored');
        $edit = Livewire::test(EditImage::class, ['record' => $image->getRouteKey()])
            ->assertFormSet(['alt' => 'Custom logo']);
        $this->assertNotEmpty($edit->get('data.path'), 'Existing upload missing on load: '.json_encode(Storage::disk('public')->allFiles()));
        $edit->fillForm(['alt' => 'Updated logo']);
        $this->assertNotEmpty($edit->get('data.path'), 'Existing upload missing before edit: '.$image->path);
        $edit->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated logo', $image->fresh()->alt);

        Livewire::test(ListImages::class)->assertTableColumnExists('alt')
            ->searchTable('Updated logo')->assertCanSeeTableRecords([$image]);

        Livewire::test(EditImage::class, ['record' => $image->getRouteKey()])
            ->fillForm(['alt' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertNull($image->fresh()->alt);
    }

    public static function imageOwners(): array
    {
        return [
            'shop' => [Shop::class, 'shop_id'],
            'repair' => [RepairShop::class, 'repair_shop_id'],
            'company' => [Company::class, 'company_id'],
        ];
    }

    public function test_admin_can_edit_and_clear_representation_logo_alt_text(): void
    {
        $representation = Representation::create(['name' => 'Representation', 'slug' => 'representation', 'logo_alt' => 'Original']);

        Livewire::test(EditRepresentation::class, ['record' => $representation->getRouteKey()])
            ->assertFormSet(['logo_alt' => 'Original'])->fillForm(['logo_alt' => '  Updated  '])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated', $representation->fresh()->logo_alt);

        Livewire::test(EditRepresentation::class, ['record' => $representation->getRouteKey()])
            ->fillForm(['logo_alt' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertNull($representation->fresh()->logo_alt);
    }

    public function test_image_alt_text_is_limited_to_the_column_length(): void
    {
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        UploadedFile::fake()->image('logo.png')->storeAs('company/'.$company->id, 'logo.png', 'public');
        $image = $company->images()->create(['type' => ImageType::Logo, 'path' => 'logo.png', 'alt' => 'Original']);

        Livewire::test(EditImage::class, ['record' => $image->getRouteKey()])
            ->fillForm(['alt' => str_repeat('a', 256)])->call('save')->assertHasFormErrors(['alt' => 'max']);
        $this->assertSame('Original', $image->fresh()->alt);
    }
}
