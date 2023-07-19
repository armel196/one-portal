<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;

class KeycloakHttpRequest
{

    public static function httpClient()
    {
        return $client = HttpClient::create();
    }

    public  function getToken()
    {
        $response = self::httpClient()->request('POST', $_ENV['KEYCLOAK_GET_TOKEN_URL'], [
            'body' => [
                'grant_type' =>  $_ENV['GRANT_TYPE'],
                'client_id' => $_ENV['ORTHER_KEYCLOAK_CLIENT_ID'],
                'client_secret' => $_ENV['ORTHER_KEYCLOAK_CLIENT_SECRET'],
                'username' => 'lambert@gmail.com',
                'password' => 'password'
            ],
        ]);

        $data = json_decode($response->getContent(), true);
        $accessToken = $data['access_token'];

        return $accessToken;
    }

    public function getAllClientOfTheRealm($token)
    {

        $response = self::httpClient()->request('GET', $_ENV['KEYCLOAK_GET_CLIENTS_URL'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $data = json_decode($response->getContent(), true);

        return $data;
    }

    public function getAllUserClient($token,$userToken)
    {      
        JWT::$leeway = 60;
        $decoded = JWT::decode($userToken, new Key($_ENV['KEYCLOAK_PK'], 'RS256'));
        $encodeToken = json_encode($decoded, true);
        $baseUrl='http://localhost:8080/admin/realms/{realm}/users/{clientId}/role-mappings';
        $realm ='dev';
        $clientId= json_decode($encodeToken)->{'sub'};
        // dd($token);
        $url = str_replace(['{realm}', '{clientId}'], [$realm, $clientId], $baseUrl);

        $response = self::httpClient()->request('GET',$url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

          $data = json_decode($response->getContent(), true);

        return $data;
    }

}
