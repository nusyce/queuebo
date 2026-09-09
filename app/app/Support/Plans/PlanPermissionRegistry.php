<?php

namespace App\Support\Plans;

class PlanPermissionRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $items = [];

    public function register(array $item): static
    {
        $key = trim((string) ($item['key'] ?? ''));

        if ($key === '') {
            throw new \InvalidArgumentException('Plan permission key is required.');
        }

        $item['key'] = $key;
        $item['type'] = $item['type'] ?? 'toggle';
        $item['order'] = (int) ($item['order'] ?? 100);
        $item['source'] = $item['source'] ?? 'module';
        $item['placement'] = $item['placement'] ?? 'normal';
        $item['fields'] = array_values($item['fields'] ?? []);

        $this->items[$key] = $item;

        return $this;
    }

    public function all(): array
    {
        $items = array_values($this->items);

        usort($items, function (array $a, array $b): int {
            $placementPriority = [
                'top' => 0,
                'normal' => 1,
            ];

            $placementCompare = ($placementPriority[$a['placement'] ?? 'normal'] ?? 1)
                <=> ($placementPriority[$b['placement'] ?? 'normal'] ?? 1);

            if ($placementCompare !== 0) {
                return $placementCompare;
            }

            $sourcePriority = [
                'module' => 0,
                'default' => 1,
            ];

            $sourceCompare = ($sourcePriority[$a['source'] ?? 'module'] ?? 0)
                <=> ($sourcePriority[$b['source'] ?? 'module'] ?? 0);

            if ($sourceCompare !== 0) {
                return $sourceCompare;
            }

            $typePriority = [
                'config' => 0,
                'toggle' => 1,
            ];

            $typeCompare = ($typePriority[$a['type'] ?? 'toggle'] ?? 1)
                <=> ($typePriority[$b['type'] ?? 'toggle'] ?? 1);

            if ($typeCompare !== 0) {
                return $typeCompare;
            }

            return ($a['order'] ?? 100) <=> ($b['order'] ?? 100);
        });

        return $items;
    }
}
