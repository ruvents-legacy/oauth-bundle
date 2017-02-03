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
    public function getLoginUrl($redirectUrl, $state = null)
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
                'scope' => $this->options['scope'],
                'response_type' => 'code',
                'state' => $state,
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
        $rawData = $this->makeRequestAndJsonDecode('oauth.vk.com', 'access_token', [
            'client_id' => $this->options['id'],
            'client_secret' => $this->options['secret'],
            'redirect_uri' => $redirectUrl,
            'code' => $code,
            'lang' => 'ru',
            'v' => $this->options['version'],
        ]);

        $data = new OAuthData();
        $data->id = $rawData['user_id'];
        $data->email = isset($rawData['email']) ? $rawData['email'] : null;

        $rawData = $this->makeRequestAndJsonDecode('api.vk.com', 'method/users.get', [
            'user_ids' => $data->id,
            'v' => $this->options['version'],
        ])['response'][0];

        $data->firstName = isset($rawData['first_name']) ? $rawData['first_name'] : null;
        $data->lastName = isset($rawData['last_name']) ? $rawData['last_name'] : null;

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
                'scope' => 4194304,
            ])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('secret', 'string')
            ->setAllowedTypes('version', 'float')
            ->setAllowedTypes('scope', 'int');
    }
}
