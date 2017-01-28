<?php

namespace Ruvents\OAuthBundle\Service;

use Ruvents\OAuthBundle\OAuthData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FacebookOAuthService extends AbstractOAuthService
{
    const NAME = 'facebook';

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
            ->withHost('www.facebook.com')
            ->withPath('v'.$this->options['version'].'/dialog/oauth')
            ->withQuery(http_build_query([
                'client_id' => $this->options['id'],
                'response_type' => 'code',
                'redirect_uri' => $redirectUrl,
                'scope' => 'email,public_profile',
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
        $accessToken = $this->makeGraphGetRequest('oauth/access_token', [
            'client_id' => $this->options['id'],
            'client_secret' => $this->options['secret'],
            'redirect_uri' => $redirectUrl,
            'code' => $code,
        ])['access_token'];

        $userData = $this->makeGraphGetRequest('me', [
            'fields' => 'email,first_name,last_name,middle_name',
            'access_token' => $accessToken,
            'code' => $code,
            'locale' => 'ru_RU',
        ]);

        $data = new OAuthData();
        $data->id = (int)$userData['id'];
        $data->email = $userData['email'] ?? null;
        $data->firstName = $userData['first_name'] ?? null;
        $data->lastName = $userData['last_name'] ?? null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'version' => 2.8,
            ])
            ->setRequired([
                'id',
                'secret',
            ]);
    }

    /**
     * @param string $endpoint
     * @param array  $queryParameters
     *
     * @return array
     */
    private function makeGraphGetRequest($endpoint, array $queryParameters = [])
    {
        $uri = $this->uriFactory
            ->createUri('')
            ->withScheme('https')
            ->withHost('graph.facebook.com')
            ->withPath('v'.$this->options['version'].'/'.ltrim($endpoint, '/'))
            ->withQuery(http_build_query($queryParameters));

        $request = $this->messageFactory->createRequest('GET', $uri);
        $response = $this->httpClient->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true);
    }
}
