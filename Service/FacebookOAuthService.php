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
            ->withPath($this->getPath('dialog/oauth'))
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
        $accessToken = $this->makeRequestAndJsonDecode('graph.facebook.com',
            $this->getPath('oauth/access_token'),
            [
                'client_id' => $this->options['id'],
                'client_secret' => $this->options['secret'],
                'redirect_uri' => $redirectUrl,
                'code' => $code,
            ]
        )['access_token'];

        $rawData = $this->makeRequestAndJsonDecode('graph.facebook.com',
            $this->getPath('me'),
            [
                'fields' => 'email,first_name,last_name,middle_name',
                'access_token' => $accessToken,
                'code' => $code,
                'locale' => 'ru_RU',
            ]
        );

        $data = new OAuthData();
        $data->id = (int)$rawData['id'];
        $data->email = $rawData['email'] ?? null;
        $data->firstName = $rawData['first_name'] ?? null;
        $data->lastName = $rawData['last_name'] ?? null;

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
                'graph_version' => 2.8,
            ])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('secret', 'string')
            ->setAllowedTypes('graph_version', 'float');
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    private function getPath($endpoint)
    {
        return 'v'.$this->options['graph_version'].'/'.$endpoint;
    }
}
