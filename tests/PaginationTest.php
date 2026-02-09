<?php declare(strict_types=1);

namespace GeneratorLabs\Tests;

use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    //
    // test single page response
    //
    public function testGetAllSinglePage(): void
    {
        $hosts = $this->createMockHosts(3);

        $mock = $this->getMockBuilder(MockPaginatedResource::class)
            ->onlyMethods(['get'])
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->with(['page' => 1, 'page_size' => 100])
            ->willReturn([
                'total' => 3,
                'page' => 1,
                'total_pages' => 1,
                'page_size' => 100,
                'data' => $hosts
            ]);

        $result = $mock->getAll();

        $this->assertCount(3, $result);
        $this->assertEquals('host_1', $result[0]['name']);
        $this->assertEquals('host_3', $result[2]['name']);
    }

    //
    // test multi-page response
    //
    public function testGetAllMultiplePages(): void
    {
        $mock = $this->getMockBuilder(MockPaginatedResource::class)
            ->onlyMethods(['get'])
            ->getMock();

        $mock->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function (array $params) {
                $page = $params['page'];

                return [
                    'total' => 5,
                    'page' => $page,
                    'total_pages' => 3,
                    'page_size' => 2,
                    'data' => $page <= 2
                        ? $this->createMockHosts(2, ($page - 1) * 2)
                        : $this->createMockHosts(1, 4)
                ];
            });

        $result = $mock->getAll(['page_size' => 2]);

        $this->assertCount(5, $result);
        $this->assertEquals('host_1', $result[0]['name']);
        $this->assertEquals('host_5', $result[4]['name']);
    }

    //
    // test empty response
    //
    public function testGetAllEmptyResponse(): void
    {
        $mock = $this->getMockBuilder(MockPaginatedResource::class)
            ->onlyMethods(['get'])
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->willReturn([
                'total' => 0,
                'page' => 1,
                'total_pages' => 1,
                'page_size' => 100,
                'data' => []
            ]);

        $result = $mock->getAll();

        $this->assertCount(0, $result);
    }

    //
    // test custom page_size parameter
    //
    public function testGetAllCustomPageSize(): void
    {
        $mock = $this->getMockBuilder(MockPaginatedResource::class)
            ->onlyMethods(['get'])
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->with(['page' => 1, 'page_size' => 50])
            ->willReturn([
                'total' => 1,
                'page' => 1,
                'total_pages' => 1,
                'page_size' => 50,
                'data' => [['name' => 'host_1']]
            ]);

        $result = $mock->getAll(['page_size' => 50]);

        $this->assertCount(1, $result);
    }

    //
    // test that extractItems uses resource name key
    //
    public function testExtractItemsByResourceName(): void
    {
        $mock = $this->getMockBuilder(MockPaginatedResource::class)
            ->onlyMethods(['get'])
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->willReturn([
                'total' => 2,
                'page' => 1,
                'total_pages' => 1,
                'page_size' => 100,
                'hosts' => [['name' => 'a'], ['name' => 'b']]
            ]);

        $result = $mock->getAll();

        $this->assertCount(2, $result);
        $this->assertEquals('a', $result[0]['name']);
    }

    private function createMockHosts(int $_count, int $_offset = 0): array
    {
        $hosts = [];
        for ($i = 1; $i <= $_count; $i++)
        {
            $hosts[] = ['name' => 'host_' . ($i + $_offset)];
        }
        return $hosts;
    }
}

//
// mock class that uses the PaginationTrait so we can test it
//
class MockPaginatedResource
{
    use \GeneratorLabs\API\PaginationTrait;

    public function get(array $_params = []): array
    {
        return [];
    }

    protected function getResourceName(): string
    {
        return 'hosts';
    }
}
