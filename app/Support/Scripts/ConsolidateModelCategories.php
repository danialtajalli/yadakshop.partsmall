<?php

namespace App\Support\Scripts;

use App\Models\CarModel;
use App\Models\ModelCategory;
use Illuminate\Support\Facades\DB;

/**
 * One-off cleanup: merge English legacy model categories into their Persian twins.
 */
class ConsolidateModelCategories
{
    /**
     * @return array{
     *     steps: list<array<string, mixed>>,
     *     remaining: list<array{id: int, name: string, slug: string, models_count: int}>,
     * }
     */
    public function run(): array
    {
        $steps = [];

        DB::transaction(function () use (&$steps): void {
            $steps[] = $this->renameCategory(
                fromSlugs: ['year-miladi'],
                fromNames: ['year-miladi', 'year miladi'],
                newName: 'سال میلادی',
            );

            $steps[] = $this->mergeInto(
                fromSlugs: ['year-shamsi'],
                fromNames: ['year-shamsi'],
                toSlugs: ['year-fa'],
                toNames: ['سال'],
                createTargetIfMissing: [
                    'name' => 'سال',
                    'slug' => 'year-fa',
                ],
            );

            $steps[] = $this->renameCategory(
                fromSlugs: ['year-fa'],
                fromNames: ['سال'],
                newName: 'سال شمسی',
            );

            $steps[] = $this->mergeInto(
                fromSlugs: ['engine'],
                fromNames: ['engine'],
                toSlugs: ['engine-fa'],
                toNames: ['موتور'],
                createTargetIfMissing: [
                    'name' => 'موتور',
                    'slug' => 'engine-fa',
                ],
            );

            $steps[] = $this->mergeInto(
                fromSlugs: ['company'],
                fromNames: ['company'],
                toSlugs: ['company-fa'],
                toNames: ['شرکت'],
                createTargetIfMissing: [
                    'name' => 'شرکت',
                    'slug' => 'company-fa',
                ],
            );

            $steps[] = $this->mergeInto(
                fromSlugs: ['body'],
                fromNames: ['body'],
                toSlugs: ['body-fa'],
                toNames: ['بدنه'],
                createTargetIfMissing: [
                    'name' => 'بدنه',
                    'slug' => 'body-fa',
                ],
            );

            // Same English → Persian duplicate pattern as the steps above.
            $steps[] = $this->mergeInto(
                fromSlugs: ['gearbox'],
                fromNames: ['gearbox'],
                toSlugs: ['gearbox-fa'],
                toNames: ['گیربکس'],
                createTargetIfMissing: [
                    'name' => 'گیربکس',
                    'slug' => 'gearbox-fa',
                ],
            );
        });

        return [
            'steps' => $steps,
            'remaining' => $this->remainingCategories(),
        ];
    }

    /**
     * @param  list<string>  $fromSlugs
     * @param  list<string>  $fromNames
     * @return array<string, mixed>
     */
    private function renameCategory(array $fromSlugs, array $fromNames, string $newName): array
    {
        $category = $this->findCategory($fromSlugs, $fromNames);

        if ($category === null) {
            return [
                'action' => 'rename',
                'status' => 'skipped',
                'reason' => 'source_not_found',
                'looked_for' => ['slugs' => $fromSlugs, 'names' => $fromNames],
                'new_name' => $newName,
            ];
        }

        $previousName = $category->name;
        $category->name = $newName;
        $category->save();

        return [
            'action' => 'rename',
            'status' => 'ok',
            'id' => $category->id,
            'slug' => $category->slug,
            'from_name' => $previousName,
            'to_name' => $newName,
        ];
    }

    /**
     * Reassign models from a legacy category onto its Persian twin, then delete the legacy row.
     *
     * @param  list<string>  $fromSlugs
     * @param  list<string>  $fromNames
     * @param  list<string>  $toSlugs
     * @param  list<string>  $toNames
     * @param  array{name: string, slug: string}|null  $createTargetIfMissing
     * @return array<string, mixed>
     */
    private function mergeInto(
        array $fromSlugs,
        array $fromNames,
        array $toSlugs,
        array $toNames,
        ?array $createTargetIfMissing = null,
    ): array {
        $from = $this->findCategory($fromSlugs, $fromNames);

        if ($from === null) {
            return [
                'action' => 'merge',
                'status' => 'skipped',
                'reason' => 'source_not_found',
                'looked_for' => ['slugs' => $fromSlugs, 'names' => $fromNames],
                'target' => ['slugs' => $toSlugs, 'names' => $toNames],
            ];
        }

        $to = $this->findCategory($toSlugs, $toNames);

        if ($to === null && $createTargetIfMissing !== null) {
            $to = ModelCategory::query()->create($createTargetIfMissing);
        }

        if ($to === null) {
            return [
                'action' => 'merge',
                'status' => 'failed',
                'reason' => 'target_not_found',
                'source' => ['id' => $from->id, 'name' => $from->name, 'slug' => $from->slug],
                'looked_for_target' => ['slugs' => $toSlugs, 'names' => $toNames],
            ];
        }

        if ($from->is($to)) {
            return [
                'action' => 'merge',
                'status' => 'skipped',
                'reason' => 'source_and_target_are_same',
                'id' => $from->id,
                'slug' => $from->slug,
            ];
        }

        $moved = CarModel::query()
            ->where('category_id', $from->id)
            ->update(['category_id' => $to->id]);

        $deletedId = $from->id;
        $deletedSlug = $from->slug;
        $deletedName = $from->name;
        $from->delete();

        return [
            'action' => 'merge',
            'status' => 'ok',
            'models_moved' => $moved,
            'from' => ['id' => $deletedId, 'name' => $deletedName, 'slug' => $deletedSlug],
            'to' => ['id' => $to->id, 'name' => $to->name, 'slug' => $to->slug],
        ];
    }

    /**
     * @param  list<string>  $slugs
     * @param  list<string>  $names
     */
    private function findCategory(array $slugs, array $names): ?ModelCategory
    {
        $normalizedNames = collect($names)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->values()
            ->all();

        return ModelCategory::query()
            ->where(function ($query) use ($slugs, $normalizedNames): void {
                if ($slugs !== []) {
                    $query->whereIn('slug', $slugs);
                }

                if ($normalizedNames !== []) {
                    $query->orWhereIn('name', $normalizedNames);
                }
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @return list<array{id: int, name: string, slug: string, models_count: int}>
     */
    private function remainingCategories(): array
    {
        return ModelCategory::query()
            ->withCount('models')
            ->orderBy('id')
            ->get()
            ->map(fn (ModelCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'models_count' => (int) $category->models_count,
            ])
            ->all();
    }
}
