<?php

namespace O360Main\SaasBridge\ApiClient;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class ModuleApi
{

    private readonly PendingRequest $http;
    private readonly EndPoint $endpoint;

    public function __construct(PendingRequest $http, EndPoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->http = $http;
    }

    private function makeUrl(...$args): string
    {
        $args = [$this->endpoint->value, ...$args];

        return join('/', array_map(fn($arg) => trim($arg, '/'), $args));
    }

//Route::post('/batch-delete-by-sync-ids', [$controller, 'batchDeleteBySyncIds']);//
    public function findMany($pageNumber, $perPage, $queryParams = []): PromiseInterface|Response
    {
        $url = $this->makeUrl('/');

        return $this->http->get($url, [
            'page' => $pageNumber,
            'perPage' => $perPage,
            ...$queryParams,
        ]);
    }


    //GET /api/v1/categories/1
    public function findById(string $saasId): PromiseInterface|Response
    {
        $url = $this->makeUrl($saasId);

        return $this->http->get($url);
    }

    //POST /api/v1/categories/find-by-sync-id
    public function findBySyncId(array $syncId): PromiseInterface|Response
    {
        $url = $this->makeUrl('/find-by-sync-id');

        return $this->http->post($url, [
            'syncId' => $syncId
        ]);
    }


    // POST /api/v1/categories/sync
    public function sync($data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/sync');

        return $this->http->post($url, $data);
    }


    //POST /api/v1/categories/update-sync-id/1
    public function updateSyncId(string $saasId, $data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/update-sync-id', $saasId);

        return $this->http->post($url, $data);
    }


    //DELETE /api/v1/categories/delete/1
    public function delete(string $id): PromiseInterface|Response
    {

        $url = $this->makeUrl('/delete', $id);

        return $this->http->delete($url);
    }

    //DELETE /api/v1/categories/delete-by-sync-id
    public function deleteBySyncId(array $syncId): PromiseInterface|Response
    {
        $url = $this->makeUrl('/delete-by-sync-id');

        return $this->http->delete($url, [
            'syncId' => $syncId
        ]);
    }


    //POST /api/v1/categories/batch-sync
    public function batchSync($data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/batch-sync');

        return $this->http->post($url, $data);
    }


    //POST /api/v1/categories/batch-update-sync-ids
    public function batchUpdateSyncIds($data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/batch-update-sync-ids');

        return $this->http->post($url, $data);
    }


    //POST /api/v1/categories/batch-delete-by-ids
    public function batchDeleteBySaasIds($data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/batch-delete-by-ids');

        return $this->http->post($url, $data);
    }

    //POST /api/v1/categories/batch-delete-by-sync-ids
    public function batchDeleteBySyncIds($data): PromiseInterface|Response
    {
        $url = $this->makeUrl('/batch-delete-by-sync-ids');

        return $this->http->post($url, $data);
    }

}
