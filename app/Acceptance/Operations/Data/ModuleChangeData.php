<?php

namespace App\Acceptance\Operations\Data;

use InvalidArgumentException;

final readonly class ModuleChangeData
{
    /** @var list<FileChange> */
    public array $changes;

    public int $created;

    public int $updated;

    public int $unchanged;

    /** @param list<FileChange> $changes */
    public function __construct(
        public string $subject,
        public string $moduleName,
        public ?string $appKey,
        public ?string $componentKey,
        public bool $dryRun,
        array $changes,
        public int $version = 1,
    ) {
        if ($version !== 1 || $subject === '' || $moduleName === '') {
            throw new InvalidArgumentException('module_change_data_invalid');
        }
        foreach ($changes as $change) {
            if (! $change instanceof FileChange) {
                throw new InvalidArgumentException('module_change_data_invalid');
            }
        }
        usort($changes, fn (FileChange $a, FileChange $b): int => strcmp($a->path, $b->path));
        $this->changes = array_values($changes);
        $this->created = $this->count('create');
        $this->updated = $this->count('update_managed_region');
        $this->unchanged = $this->count('unchanged');
    }

    public function created(): int
    {
        return $this->count('create');
    }

    public function updated(): int
    {
        return $this->count('update_managed_region');
    }

    public function unchanged(): int
    {
        return $this->count('unchanged');
    }

    private function count(string $action): int
    {
        return count(array_filter($this->changes, fn (FileChange $change): bool => $change->action === $action));
    }
}
