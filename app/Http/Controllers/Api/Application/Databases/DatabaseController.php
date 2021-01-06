<?php

namespace Pterodactyl\Http\Controllers\Api\Application\Databases;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\DatabaseHost;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pterodactyl\Transformers\Api\Application\DatabaseHostTransformer;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Pterodactyl\Http\Requests\Api\Application\Databases\GetDatabaseRequest;
use Pterodactyl\Http\Requests\Api\Application\Databases\GetDatabasesRequest;
use Pterodactyl\Http\Requests\Api\Application\Databases\StoreDatabaseRequest;
use Pterodactyl\Http\Requests\Api\Application\Databases\UpdateDatabaseRequest;
use Pterodactyl\Http\Requests\Api\Application\Databases\DeleteDatabaseRequest;

class DatabaseController extends ApplicationApiController
{
    /**
     * DatabaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array of all database hosts.
     *
     * @param \Pterodactyl\Http\Requests\Api\Application\Databases\GetDatabasesRequest $request
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function index(GetDatabasesRequest $request): array
    {
        $perPage = $request->query('per_page', 10);
        if ($perPage < 1) {
            $perPage = 10;
        } else if ($perPage > 100) {
            throw new BadRequestHttpException('"per_page" query parameter must be below 100.');
        }

        $databases = QueryBuilder::for(DatabaseHost::query())
            ->allowedFilters(['name', 'host'])
            ->allowedSorts(['id', 'name', 'host'])
            ->paginate($perPage);

        return $this->fractal->collection($databases)
            ->transformWith($this->getTransformer(DatabaseHostTransformer::class))
            ->toArray();
    }

    /**
     * Returns a single database host.
     *
     * @param \Pterodactyl\Http\Requests\Api\Application\Databases\GetDatabaseRequest $request
     * @param \Pterodactyl\Models\DatabaseHost $databaseHost
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function view(GetDatabaseRequest $request, DatabaseHost $databaseHost): array
    {
        return $this->fractal->item($databaseHost)
            ->transformWith($this->getTransformer(DatabaseHostTransformer::class))
            ->toArray();
    }

    /**
     * Creates a new database host.
     *
     * @param \Pterodactyl\Http\Requests\Api\Application\Databases\StoreDatabaseRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store(StoreDatabaseRequest $request): JsonResponse
    {
        $databaseHost = DatabaseHost::query()->create($request->validated());

        return $this->fractal->item($databaseHost)
            ->transformWith($this->getTransformer(DatabaseHostTransformer::class))
            ->respond(JsonResponse::HTTP_CREATED);
    }

    /**
     * Updates a database host.
     *
     * @param \Pterodactyl\Http\Requests\Api\Application\Databases\UpdateDatabaseRequest $request
     * @param \Pterodactyl\Models\DatabaseHost $databaseHost
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function update(UpdateDatabaseRequest $request, DatabaseHost $databaseHost): array
    {
        $databaseHost->update($request->validated());

        return $this->fractal->item($databaseHost)
            ->transformWith($this->getTransformer(DatabaseHostTransformer::class))
            ->toArray();
    }

    /**
     * Deletes a database host.
     *
     * @param \Pterodactyl\Http\Requests\Api\Application\Databases\DeleteDatabaseRequest $request
     * @param \Pterodactyl\Models\DatabaseHost $databaseHost
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete(DeleteDatabaseRequest $request, DatabaseHost $databaseHost): JsonResponse
    {
        $databaseHost->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}