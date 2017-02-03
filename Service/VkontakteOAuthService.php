<?php

namespace Ruvents\OAuthBundle\Service;

use Ruvents\OAuthBundle\OAuthData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VkontakteOAuthService extends AbstractOAuthService
{
    const NAME = 'vkontakte';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl($redirectUrl)
    {
        return $this->uriFactory
            ->createUri('')
            ->withScheme('https')
            ->withHost('oauth.vk.com')
            ->withPath('authorize')
            ->withQuery(http_build_query([
                'client_id' => $this->options['id'],
                'redirect_uri' => $redirectUrl,
                'display' => 'page',
                'scope' => 4194304,
                'response_type' => 'code',
                'v' => $this->options['version'],
            ]))
            ->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        return $request->query->get('code');
    }

    /**
     * {@inheritdoc}
     */
    public function getData($code, $redirectUrl)
    {
        $responseData = $this->makeRequestAndJsonDecode('oauth.vk.com', 'access_token', [
            'client_id' => $this->options['id'],
            'client_secret' => $this->options['secret'],
            'redirect_uri' => $redirectUrl,
            'code' => $code,
            'lang' => 'ru',
            'v' => $this->options['version'],
        ]);

        $data = new OAuthData();
        $data->id = $responseData['user_id'];
        $data->email = $responseData['email'];

        /*$uri = (new Diactoros\Uri())
            ->withScheme('https')
            ->withHost('api.vk.com')
            ->withPath('method/users.get')
            ->withQuery(http_build_query([
                'user_ids' => $serviceCredentials['user_id'],
                'v' => self::API_VERSION,
            ]));

        $response = $this->httpClient->sendRequest(new Diactoros\Request($uri, 'GET'));
        $responseData = json_decode($response->getBody()->getContents(), true)['response'][0];

         $socialData = new SocialData();
         $socialData->socialService = $this->getName();
         $socialData->socialId = (int)$accessTokenData['user_id'];
         $socialData->userEmail = isset($accessTokenData['email']) ? $accessTokenData['email'] : null;
         $socialData->userFirstname = isset($userData['first_name']) ? $userData['first_name'] : null;
         $socialData->userLastname = isset($userData['last_name']) ? $userData['last_name'] : null;*/

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'id',
                'secret',
            ])
            ->setDefaults([
                'version' => 5.6,
            ])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('secret', 'string')
            ->setAllowedTypes('version', 'float');
    }
}
