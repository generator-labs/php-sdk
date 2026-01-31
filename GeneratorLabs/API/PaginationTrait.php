<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API;

trait PaginationTrait
{
    //
    // Get all items with automatic pagination
    //
    public function getAll(array $_params = []): array
    {
        $allItems = [];
        $page = 1;
        $pageSize = $_params['page_size'] ?? 100;

        do {
            // Merge pagination params
            $params = array_merge($_params, [
                'page' => $page,
                'page_size' => $pageSize
            ]);

            // Make the request
            $response = $this->get($params);

            // Extract items from response
            $items = $this->extractItems($response);
            $allItems = array_merge($allItems, $items);

            // Check if there are more pages
            $hasMore = $response['has_more'] ?? false;
            $page++;

        } while ($hasMore);

        return $allItems;
    }

    //
    // Extract items from response - override in child class if needed
    //
    protected function extractItems(array $_response): array
    {
        // Default implementation - tries common patterns
        $resourceName = $this->getResourceName();

        if (isset($_response[$resourceName])) {
            return $_response[$resourceName];
        }

        if (isset($_response['data'])) {
            return $_response['data'];
        }

        if (isset($_response['items'])) {
            return $_response['items'];
        }

        return [];
    }

    //
    // Get resource name for extracting items (override in child class)
    //
    protected function getResourceName(): string
    {
        // Default to lowercase class name
        $class = get_class($this);
        $parts = explode('\\', $class);
        return strtolower(end($parts));
    }
}
