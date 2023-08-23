<?php

namespace App\Security;

use App\Repository\UserRepository;
use ContainerAuCkOs2\getUserRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Routing\Annotation\Route;
use League\OAuth2\Client\Token\AccessToken;
use  Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nowakowskir\JWT\JWT as JWTJWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private $provider;

    public function __construct(
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqX6Vah/jb8aUe/VTaY0UrIDvScWjKF20bs/Bh2HSS/PLgjDWufzWaPDr49N7AkxlB/fNPbxwhK75f42z1bQQwgag5/+SuRV6GKxrKsfB+GfWOLyzaRecpLHT7DK6QdBdqG8vwCP+C+Kp6pnzKSRbAvobfpTwniUrhES04awWf6Ktwsttqj4NZNYnLNoIXgsdm0qFJgkqCLgqzfgB6gfpw1qE4OZAAvkAWyBCJnBKdxHHMxDyWJ86AD3FzXuoTS9y/gCDfimXhl5WoODnmfNBWdMDFltXy55sSiR0ZjklIzyeFDnqQztFs9R7mbV7BZb/9yOCHt+Az0Qyd/WMIcmciQIDAQAB";

        $this->provider = new Keycloak([
            'authServerUrl'         => 'http://localhost:8080',
            'realm'                 =>  'dev',
            'clientId'              => $_ENV['KEYCLOAK_CLIENDID'],
            'clientSecret'          => $_ENV['KEYCLOAK_CLIENTSECRET'],
            'redirectUri'           => $_ENV['KEYCLOAK_HOME'],
            'encryptionAlgorithm'   => 'RS256',                             // optional
            // 'encryptionKeyPath'     => '../key.pem',                         // optional
            'encryptionKey'         => $key,    // optional
            // 'version'               => '15.0',                            // optional


        ]);
    }

    public function start(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $authException = null)
    {

        if ($request->attributes->get('_route') === 'app_keycloak_login') {
            return new RedirectResponse(
                '/login', // might be the site, where users choose their oauth provider
                Response::HTTP_TEMPORARY_REDIRECT
            );
        };
    }
    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.  
     */
    public function supports(Request $request): ?bool
    {
        // dd($request);
        return $request->attributes->get('_route') === 'app_keycloak_callback';
    }

    public function authenticate(Request $request): Passport
    {       
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->query->get('code')
        ]);
        $session = $request->getSession();
        $session->set('accessToken', $token->getValues());
        
        
        if (null === $token) {
            
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        
        JWT::$leeway = 60;
        $decoded = JWT::decode($token->getToken(), new Key($_ENV['KEYCLOAK_PK'], 'RS256'));

        // dd($decoded);
        return new SelfValidatingPassport(

            new UserBadge(json_encode($decoded), function (string $userInfo) {

                $info = json_decode($userInfo, true);

                $userId = $info['sid'];

                $email = key_exists('email', $info) ? $info['email'] : '';

                $name = key_exists('preferred_username', $info) ? $info['preferred_username'] : '';

                $ro = key_exists('resource_access', $info) ? $info['resource_access'] : '';
                $roles = key_exists('one-portal', $ro) ? $ro['one-portal']['roles'] : '';
                $given_name = key_exists('given_name', $info) ? $info['given_name'] : '';
                // dd($ro);
                $user = $this->userRepository->findByEmail($email);
                if (null === $user) {
                    $user = new User();
                    $user->setEmail($email);
                    $this->userRepository->save($user);
                }
                $user->setKeycloakId($userId);
                $user->setRoles($roles);
                $user->setFullName($given_name);
                $user->setName($name);
                $this->userRepository->save($user, true);
                return $user;
            })

        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // dd($token->getUser()->getRoles());

        if (in_array('ROLE_ADMIN', $token->getUser()->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_apps_index'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate('app_user'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
