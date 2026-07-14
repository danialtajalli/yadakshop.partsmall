<?php

namespace App\Services\Contact\Didar;

class DidarResponseParser
{
    /**
     * @param  array<string, mixed>  $response
     */
    public function extractPersonId(array $response): ?string
    {
        $payload = $response['Response'] ?? null;

        if (! is_array($payload)) {
            return null;
        }

        foreach (['PersonId', 'Id', 'ContactId'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        if (isset($payload['Contact']) && is_array($payload['Contact']) && ! empty($payload['Contact']['Id'])) {
            return (string) $payload['Contact']['Id'];
        }

        if (isset($payload['Person']) && is_array($payload['Person']) && ! empty($payload['Person']['Id'])) {
            return (string) $payload['Person']['Id'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function extractFirstProductId(array $response): ?string
    {
        $products = $response['Response'] ?? null;

        if (! is_array($products) || $products === []) {
            return null;
        }

        $first = $products[0] ?? null;

        return is_array($first) && ! empty($first['Id']) ? (string) $first['Id'] : null;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function extractOwnerUserId(array $response, string $username): ?string
    {
        $users = $response['Response'] ?? null;

        if (! is_array($users)) {
            return null;
        }

        foreach ($users as $user) {
            if (! is_array($user)) {
                continue;
            }

            if (($user['UserName'] ?? null) === $username && ! empty($user['UserId'])) {
                return (string) $user['UserId'];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array{pipeline_stage_id: ?string, pipeline_id: ?string}
     */
    public function extractFirstPipelineStage(array $response): array
    {
        $pipelines = $response['Response'] ?? null;

        if (! is_array($pipelines) || $pipelines === []) {
            return ['pipeline_stage_id' => null, 'pipeline_id' => null];
        }

        $pipeline = $pipelines[0];

        if (! is_array($pipeline)) {
            return ['pipeline_stage_id' => null, 'pipeline_id' => null];
        }

        $stages = $pipeline['Stages'] ?? [];
        $firstStage = is_array($stages) && $stages !== [] ? $stages[0] : null;

        return [
            'pipeline_stage_id' => is_array($firstStage) && ! empty($firstStage['Id']) ? (string) $firstStage['Id'] : null,
            'pipeline_id' => ! empty($pipeline['Id']) ? (string) $pipeline['Id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function extractDealId(array $response): ?string
    {
        $payload = $response['Response'] ?? null;

        if (! is_array($payload)) {
            return null;
        }

        foreach (['DealId', 'Id'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        if (isset($payload['Deal']) && is_array($payload['Deal']) && ! empty($payload['Deal']['Id'])) {
            return (string) $payload['Deal']['Id'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function extractFirstContactIdFromSearch(array $response): ?string
    {
        $contacts = $response['Response'] ?? null;

        if (! is_array($contacts) || $contacts === []) {
            return null;
        }

        $first = $contacts[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        foreach (['Id', 'ContactId', 'PersonId'] as $key) {
            if (! empty($first[$key])) {
                return (string) $first[$key];
            }
        }

        return null;
    }
}
