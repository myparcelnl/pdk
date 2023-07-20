<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Repository\StorageRepository;

class MockStorageRepository extends StorageRepository
{
    /**
     * @var int
     */
    private $fallbackCalls = 0;

    public function deleteData(): void
    {
        $this->delete('data');
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->retrieve('data');
    }

    /**
     * @return int
     */
    public function getFallbackCalls(): int
    {
        return $this->fallbackCalls;
    }

    /**
     * @return mixed
     */
    public function getNonexistentWithFallback()
    {
        return $this->retrieve('nonexistent', function () {
            $this->fallbackCalls++;
            return 'fallback';
        });
    }

    /**
     * @param $data
     *
     * @return void
     */
    public function setData($data): void
    {
        $this->save('data', $data);
    }
}
