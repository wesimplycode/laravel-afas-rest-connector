<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Tests;

use WeSimplyCode\LaravelAfasRestConnector\AfasConnection;
use WeSimplyCode\LaravelAfasRestConnector\Exceptions\AfasAuthenticationException;
use WeSimplyCode\LaravelAfasRestConnector\Facades\Afas;

class ClassicTokenAuthenticationTest extends TestCase
{
    public function test_classic_token_is_used_without_a_client_id(): void
    {
        $connection = Afas::connection('classic');

        $this->assertFalse($connection->usesOAuth());
        $this->assertSame(AfasConnection::AUTH_TOKEN, $connection->getAuthType());
    }

    public function test_it_sends_the_base64_encoded_afas_token(): void
    {
        $this->fakeAfas();

        Afas::getConnector('contacts', false, 'classic')->execute();

        $this->assertSame(0, $this->tokenRequests);
        $this->assertSame('AfasToken '.base64_encode(self::CLASSIC_TOKEN), $this->authorizationHeader($this->lastApiRequest()));
        $this->assertSame('https://22222.rest.afas.online/profitrestservices/connectors/Profit_Contacts', $this->lastApiRequest()->url());
    }

    public function test_explicit_auth_setting_wins_over_inference(): void
    {
        $this->fakeAfas();

        Afas::connectionWith('default', ['auth' => 'token', 'token' => self::CLASSIC_TOKEN])->getConnector('contacts')->execute();

        $this->assertSame(0, $this->tokenRequests);
        $this->assertSame('AfasToken '.base64_encode(self::CLASSIC_TOKEN), $this->authorizationHeader($this->lastApiRequest()));
    }

    public function test_connection_with_token_override_uses_classic_auth(): void
    {
        $this->fakeAfas();

        $connection = Afas::connection('default', '<t/>', 'O44444AA');

        $this->assertFalse($connection->usesOAuth());
        $this->assertSame('<t/>', $connection->getToken());

        $connection->getConnector('contacts')->execute();

        $this->assertStringStartsWith('https://44444.rest.afas.online/', $this->lastApiRequest()->url());
    }

    public function test_it_throws_when_the_token_is_missing(): void
    {
        $this->fakeAfas();

        $this->expectException(AfasAuthenticationException::class);
        $this->expectExceptionMessage('AFAS token not found.');

        Afas::connectionWith('classic', ['token' => null])->getConnector('contacts')->execute();
    }
}
