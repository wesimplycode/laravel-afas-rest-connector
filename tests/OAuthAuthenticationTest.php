<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use WeSimplyCode\LaravelAfasRestConnector\AfasConnection;
use WeSimplyCode\LaravelAfasRestConnector\Exceptions\AfasAuthenticationException;
use WeSimplyCode\LaravelAfasRestConnector\Facades\Afas;

class OAuthAuthenticationTest extends TestCase
{
    public function test_oauth_is_used_when_a_client_id_is_configured(): void
    {
        $connection = Afas::connection();

        $this->assertTrue($connection->usesOAuth());
        $this->assertSame(AfasConnection::AUTH_OAUTH, $connection->getAuthType());
    }

    public function test_token_endpoint_per_environment_type(): void
    {
        $this->assertSame(self::TOKEN_URL, Afas::connection()->getOAuthTokenUrl());
        $this->assertSame('https://22222.rest.afas.online/profitrestservices/oauth/token', Afas::connection('classic')->getOAuthTokenUrl());
        $this->assertSame('https://33333.restaccept.afas.online/profitrestservices/oauth/token', Afas::connectionWith('default', ['environment' => 'A33333AA'])->getOAuthTokenUrl());
    }

    public function test_it_requests_a_token_with_the_client_credentials_flow_and_sends_it_as_bearer(): void
    {
        $this->fakeAfas();

        $response = Afas::getConnector('contacts')->take(5)->execute();

        $this->assertTrue($response->successful());
        $this->assertSame(1, $this->tokenRequests);

        Http::assertSent(function (Request $request) {
            return $request->url() === self::TOKEN_URL
                && $request->method() === 'POST'
                && $request->isForm()
                && $request['grant_type'] === 'client_credentials'
                && $request['client_id'] === 'client-id'
                && $request['client_secret'] === 'client-secret';
        });

        $api = $this->lastApiRequest();
        $this->assertSame('Bearer tok1', $this->authorizationHeader($api));
        $this->assertSame('https://11111.resttest.afas.online/profitrestservices/connectors/Profit_Contacts?take=5', $api->url());
    }

    public function test_the_access_token_is_cached_and_reused(): void
    {
        $this->fakeAfas();

        Afas::getConnector('contacts')->execute();
        Afas::getConnector('contacts')->execute();
        Afas::updateConnector('persons')->insert(['KnPerson' => []]);

        $this->assertSame(1, $this->tokenRequests);
        $this->assertCount(3, $this->apiRequests);

        $cached = Cache::get(Afas::connection()->getOAuthTokenProvider()->cacheKey());
        $this->assertSame('tok1', $cached['access_token']);
        $this->assertEqualsWithDelta(time() + 3600 - 60, $cached['expires_at'], 5);
    }

    public function test_a_401_response_refreshes_the_token_and_retries_once(): void
    {
        $this->fakeAfas();
        $this->nextApiStatuses = [401];

        $response = Afas::updateConnector('persons')->insert(['KnPerson' => []]);

        $this->assertTrue($response->successful());
        $this->assertSame(2, $this->tokenRequests);
        $this->assertCount(2, $this->apiRequests);
        $this->assertSame('Bearer tok1', $this->authorizationHeader($this->apiRequests[0]));
        $this->assertSame('Bearer tok2', $this->authorizationHeader($this->apiRequests[1]));
        $this->assertSame('POST', $this->apiRequests[1]->method());
    }

    public function test_a_persistent_401_is_returned_after_one_retry(): void
    {
        $this->fakeAfas();
        $this->nextApiStatuses = [401, 401];

        $response = Afas::getConnector('contacts')->execute();

        $this->assertSame(401, $response->status());
        $this->assertSame(2, $this->tokenRequests);
        $this->assertCount(2, $this->apiRequests);
    }

    public function test_an_expired_cached_token_is_not_reused(): void
    {
        $this->fakeAfas();
        $provider = Afas::connection()->getOAuthTokenProvider();

        Cache::put($provider->cacheKey(), ['access_token' => 'stale', 'expires_at' => time() - 1], 60);

        Afas::getConnector('contacts')->execute();

        $this->assertSame(1, $this->tokenRequests);
        $this->assertSame('Bearer tok1', $this->authorizationHeader($this->lastApiRequest()));
    }

    public function test_tokens_are_cached_per_environment_and_client(): void
    {
        $this->fakeAfas();

        Afas::getConnector('contacts')->execute();
        Afas::oauthConnection('other-client', 'other-secret')->getConnector('contacts')->execute();
        Afas::oauthConnection('client-id', 'client-secret', 'O22222AA')->getConnector('contacts')->execute();

        $this->assertSame(3, $this->tokenRequests);
    }

    public function test_it_throws_when_the_token_endpoint_fails(): void
    {
        Http::fake([self::TOKEN_URL => Http::response(['error' => 'invalid_client'], 400)]);

        try {
            Afas::getConnector('contacts')->execute();
            $this->fail('Expected AfasAuthenticationException');
        } catch (AfasAuthenticationException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('invalid_client', $e->getMessage());
        }
    }

    public function test_it_throws_when_the_token_response_has_no_access_token(): void
    {
        Http::fake([self::TOKEN_URL => Http::response(['token_type' => 'Bearer'], 200)]);

        $this->expectException(AfasAuthenticationException::class);

        Afas::getConnector('contacts')->execute();
    }

    public function test_it_throws_when_client_credentials_are_incomplete(): void
    {
        $this->fakeAfas();

        $this->expectException(AfasAuthenticationException::class);
        $this->expectExceptionMessage('client_id and client_secret are required');

        Afas::connectionWith('default', ['client_secret' => null])->getConnector('contacts')->execute();
    }

    public function test_the_token_update_callback_receives_new_tokens(): void
    {
        Http::fake([
            self::TOKEN_URL => Http::response(['access_token' => 'cb', 'expires_in' => 120]),
            '*' => Http::response(['rows' => []]),
        ]);

        $connection = Afas::connection();
        $seen = null;

        $connection->getOAuthTokenProvider()->setTokenUpdateCallback(function (string $token, int $expiresAt) use (&$seen) {
            $seen = [$token, $expiresAt];
        });

        $connection->getConnector('contacts')->execute();

        $this->assertSame('cb', $seen[0]);
        $this->assertEqualsWithDelta(time() + 60, $seen[1], 5);
    }

    public function test_an_unknown_auth_type_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Afas::connectionWith('default', ['auth' => 'basic'])->getAuthType();
    }
}
