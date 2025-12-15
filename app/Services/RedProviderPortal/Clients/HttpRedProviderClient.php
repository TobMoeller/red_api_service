<?php

namespace App\Services\RedProviderPortal\Clients;

use App\Enums\Order\Type;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use App\Services\RedProviderPortal\DTO\AccessToken;
use App\Services\RedProviderPortal\DTO\OrderData;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpRedProviderClient implements RedProviderClient
{
    const ACCESS_TOKEN_CACHE_KEY = 'red_provider_client_token';

    protected ?AccessToken $accessToken = null;

    // TODO rate limit API calls?
    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected ?string $certPath,
    ) {
    }

    /**
     * @return OrderData[]
     */
    public function listOrders(): array
    {
        $response = $this->authorizedRequest()
            ->get('/api/v1/orders')
            ->throw();

        return OrderData::fromList($response->json()); // @phpstan-ignore argument.type
    }

    public function createOrder(Type $type): OrderData
    {
        $response = $this->authorizedRequest()
            ->post('/api/v1/orders', ['type' => $type->value])
            ->throw();

        return OrderData::fromArray($response->json()); // @phpstan-ignore argument.type
    }

    public function getOrder(string $id): OrderData
    {
        $response = $this->authorizedRequest()
            ->get('/api/v1/order/'.$id)
            ->throw();

        return OrderData::fromArray($response->json()); // @phpstan-ignore argument.type
    }

    public function deleteOrder(string $id): void
    {
        $this->authorizedRequest()
            ->delete('/api/v1/order/'.$id)
            ->throw();
    }

    protected function authorizedRequest(): PendingRequest
    {
        return $this
            ->baseRequest()
            ->withToken($this->getAccessToken()->token);
    }

    protected function baseRequest(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->when(
                empty($this->certPath),
                fn (PendingRequest $request) => $request->withoutVerifying(),
                fn (PendingRequest $request) => $request->withOptions([
                    'verify' => $this->certPath,
                ])
            );
    }

    protected function getAccessToken(): AccessToken
    {
        if ($this->accessToken && !$this->accessToken->isExpired()) {
            return $this->accessToken;
        }

        $maybeToken = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

        if ($maybeToken instanceof AccessToken && ! $maybeToken->isExpired()) {
            return $this->accessToken = $maybeToken;
        }

        $response = $this
            ->baseRequest()
            ->post('/api/v1/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if ($response->failed()
            || empty($ttl = $response->json('ttl'))
            || !is_int($ttl)
            || empty($token = $response->json('access_token'))
            || !is_string($token)
        ) {
            Log::error(self::class.':Failed to retrieve Access Token', ['response' => $response]);

            throw new Exception('Failed to retrieve access token.');
        }

        $expiresAt = Carbon::now()->addSeconds($ttl);

        // add safety margin
        if ($expiresAt->gt(Carbon::now()->addSeconds(20))) {
            $expiresAt = $expiresAt->subSeconds(10);
        }

        $this->accessToken = new AccessToken($token, $expiresAt);

        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $this->accessToken, $expiresAt->clone()->subSeconds(5));

        return $this->accessToken;
    }
}
