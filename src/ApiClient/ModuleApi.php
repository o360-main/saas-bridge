<?php

namespace O360Main\SaasBridge\ApiClient;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Route;

class ModuleApi
{

    public function __construct(private readonly PendingRequest $http)
    {
    }


//Route::post('/batch-delete-by-sync-ids', [$controller, 'batchDeleteBySyncIds']);//


    public function all($pageNumber, $perPage): PromiseInterface|Response
    {
        return $this->http->get('/', [
            'page' => $pageNumber,
            'perPage' => $perPage
        ]);
    }


    //GET /api/v1/categories
    public function find($pageNumber, $perPage): PromiseInterface|Response
    {
        return $this->http->get('/', [
            'page' => $pageNumber,
            'perPage' => $perPage
        ]);
    }


    //GET /api/v1/categories/1
    public function findById(string $saasId): PromiseInterface|Response
    {
        return $this->http->get($saasId);
    }

    //POST /api/v1/categories/find-by-sync-id
    public function findBySyncId(array $syncId): PromiseInterface|Response
    {
        return $this->http->post('/find-by-sync-id', [
            'syncId' => $syncId
        ]);
    }


    // POST /api/v1/categories/sync
    public function sync($data): PromiseInterface|Response
    {
        return $this->http->post('/sync', $data);
    }


    //POST /api/v1/categories/update-sync-id/1
    public function updateSyncId(string $saasId, $data): PromiseInterface|Response
    {
        return $this->http->post('/update-sync-id/' . $saasId, $data);
    }


    //DELETE /api/v1/categories/delete/1
    public function delete(string $id): PromiseInterface|Response
    {
        return $this->http->delete('/delete/' . $id);
    }

    //DELETE /api/v1/categories/delete-by-sync-id
    public function deleteBySyncId(array $syncId): PromiseInterface|Response
    {
        return $this->http->delete('/delete-by-sync-id', [
            'syncId' => $syncId
        ]);
    }


    //POST /api/v1/categories/batch-sync
    public function batchSync($data): PromiseInterface|Response
    {
        return $this->http->post('/batch-sync', $data);
    }


    //POST /api/v1/categories/batch-update-sync-ids
    public function batchUpdateSyncIds($data): PromiseInterface|Response
    {
        return $this->http->post('/batch-update-sync-ids', $data);
    }


    //POST /api/v1/categories/batch-delete-by-ids
    public function batchDeleteBySaasIds($data): PromiseInterface|Response
    {
        return $this->http->post('/batch-delete-by-ids', $data);
    }


    //POST /api/v1/categories/batch-delete-by-sync-ids
    public function batchDeleteBySyncIds($data): PromiseInterface|Response
    {
        return $this->http->post('/batch-delete-by-sync-ids', $data);
    }


}
