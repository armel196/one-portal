<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use  Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\HttpClient\HttpClient;



// use Nowakowskir\JWT\JWT;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpClient\DependencyInjection\HttpClientPass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
{
    private $provider;

    public function __construct(
        private UserRepository $UsersRepository,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator
    ) {
        // $this->decoded = new TokenDecoded(['payload_key' => 'value'], ['header_key' => 'value']);
        $key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqX6Vah/jb8aUe/VTaY0UrIDvScWjKF20bs/Bh2HSS/PLgjDWufzWaPDr49N7AkxlB/fNPbxwhK75f42z1bQQwgag5/+SuRV6GKxrKsfB+GfWOLyzaRecpLHT7DK6QdBdqG8vwCP+C+Kp6pnzKSRbAvobfpTwniUrhES04awWf6Ktwsttqj4NZNYnLNoIXgsdm0qFJgkqCLgqzfgB6gfpw1qE4OZAAvkAWyBCJnBKdxHHMxDyWJ86AD3FzXuoTS9y/gCDfimXhl5WoODnmfNBWdMDFltXy55sSiR0ZjklIzyeFDnqQztFs9R7mbV7BZb/9yOCHt+Az0Qyd/WMIcmciQIDAQAB";

        $this->provider = new Keycloak([
            'authServerUrl'         => 'http://localhost:8080',
            'realm'                 =>  'dev',
            'clientId'              => $_ENV['KEYCLOAK_CLIENDID'],
            'clientSecret'          => $_ENV['KEYCLOAK_CLIENTSECRET'],
            'redirectUri'           => $_ENV['KEYCLOAK_HOME'],
            'encryptionAlgorithm'   => 'RS256',                             // optional
            // 'encryptionKeyPath'     => '../key.pem',                         // optional
            'encryptionKey'         => $key,
            'version'               => '21.0.2',                            // optional


        ]);
    }

    #[Route('/login', name: 'app_keycloak_login')]
    public function keycloalLOgin(): Response
    {
        $authUrl = $this->provider->getAuthorizationUrl();

        return $this->redirect($authUrl);
    }

    #[Route('/', name: 'app_route_principale')]
    public function seConnecter(): Response
    {
        return $this->render('use/use.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/keycloak-callback', name: 'app_keycloak_callback')]
    public function keycloakCallback()
    {
    }

    #[Route(path: '/logout', name: 'app_log')]
    public function log(Request $request): Response
    {
        $session = $request->getSession();
        // dd($session->get('accessToken'));     

        return  $this->redirect($this->provider->getLogoutUrl([
            "redirect_uri" => $_ENV['KEYCLOAK_LOGOUT'],
            "access_token" =>  $session->get('accessToken')
        ]));
    }

    #[Route(path: '/getRole', name: 'app_role')]
    public function role(Request $request): Response
    {
        // $queryParams = [
        //     'access_token' => 'votre_jetontoken',
        // ];
        $session = $request->getSession();
      
        //  dd($session->get('accessToken')['id_token']);
        $client = HttpClient::createForBaseUri('http://localhost:8080/admin/master/console/#/dev/clients',[
            'auth_bearer' =>  $session->get('accessToken')['id_token'],
        ]);
        $response = $client->request('GET', 'http://localhost:8080/admin/master/console/#/dev/clients/one-portal/roles', [
            
            'auth_bearer' =>  $session->get('accessToken')['id_token'],
            
            // ...
        ]);
        //  dd($response);
       
        // $response = $client->request(
        //     'GET',
        //     'http://localhost:8080/auth/admin/realms/dev/clients/one-portal/roles',
        //     [

        //         'auth_bearer' => $session->get('accessToken')['id_token'],
        //     ]
        // );
        // dd($response);
    }
}
