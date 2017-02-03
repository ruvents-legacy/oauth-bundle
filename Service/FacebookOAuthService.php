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
    public function getLoginUrl($redirectUrl, $state = null)
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
                'scope' => implode(',', $this->options['scope']),
                'state' => $state,
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
        $rawData = $this->makeRequestAndJsonDecode('graph.facebook.com',
            $this->getPath('oauth/access_token'),
            [
                'client_id' => $this->options['id'],
                'client_secret' => $this->options['secret'],
                'redirect_uri' => $redirectUrl,
                'code' => $code,
            ]
        );

        $data = new OAuthData();
        $data->accessToken = $rawData['access_token'];

        $rawData = $this->makeRequestAndJsonDecode('graph.facebook.com',
            $this->getPath('me'),
            [
                'fields' => implode(',', $this->options['fields']),
                'access_token' => $data->accessToken,
                'code' => $code,
                'locale' => 'ru_RU',
            ]
        );

        $data->id = isset($rawData['id']) ? $rawData['id'] : null;
        $data->email = isset($rawData['email']) ? $rawData['email'] : null;
        $data->firstName = isset($rawData['first_name']) ? $rawData['first_name'] : null;
        $data->lastName = isset($rawData['last_name']) ? $rawData['last_name'] : null;
        $data->middleName = isset($rawData['middle_name']) ? $rawData['middle_name'] : null;

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
                'scope' => [
                    'email',
                    'public_profile',
                ],
                'fields' => [
                    'email',
                    'first_name',
                    'last_name',
                    'middle_name',
                ],
                'graph_version' => 2.8,
            ])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('secret', 'string')
            ->setAllowedTypes('scope', 'array')
            ->setAllowedTypes('graph_version', 'float')
            ->setAllowedTypes('fields', 'array');
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
