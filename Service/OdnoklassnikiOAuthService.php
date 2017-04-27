<?php

namespace Ruvents\OAuthBundle\Service;

use Ruvents\OAuthBundle\OAuthData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OdnoklassnikiOAuthService extends AbstractOAuthService
{
    const NAME = 'odnoklassniki';

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
            ->withHost('connect.ok.ru')
            ->withPath('/oauth/authorize')
            ->withQuery(http_build_query([
                'client_id' => $this->options['id'],
                'redirect_uri' => $redirectUrl,
                'scope' => implode(';', $this->options['scope']),
                'response_type' => 'code',
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
        $rawData = $this->makeRequestAndJsonDecode('api.ok.ru', '/oauth/token.do', [], [
            'client_id' => $this->options['id'],
            'client_secret' => $this->options['secret'],
            'redirect_uri' => $redirectUrl,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ], [], 'POST');

        $data = new OAuthData();
        $data->accessToken = $rawData['access_token'];
        $data->refreshToken = $rawData['refresh_token'];

        $query = [
            'application_key' => $this->options['public_key'],
            'fields' => implode(',', $this->options['fields']),
            'format' => 'json',
            'method' => 'users.getCurrentUser',
        ];
        $query['sig'] = $this->getSig($query, $data->accessToken);
        $query['access_token'] = $data->accessToken;

        $rawData = $this->makeRequestAndJsonDecode('api.ok.ru', '/fb.do', $query);

        $data->id = isset($rawData['uid']) ? $rawData['uid'] : null;
        $data->firstName = isset($rawData['first_name']) ? $rawData['first_name'] : null;
        $data->lastName = isset($rawData['last_name']) ? $rawData['last_name'] : null;
        $data->email = isset($rawData['email']) ? $rawData['email'] : null;

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
                'public_key',
                'secret',
            ])
            ->setDefaults([
                'scope' => [
                    'VALUABLE_ACCESS',
                    'LONG_ACCESS_TOKEN',
                    'GET_EMAIL',
                ],
                'fields' => [
                    'email',
                    'last_name',
                    'first_name',
                ],
            ])
            ->setAllowedTypes('id', 'numeric')
            ->setAllowedTypes('public_key', 'string')
            ->setAllowedTypes('secret', 'string')
            ->setAllowedTypes('scope', 'array')
            ->setAllowedTypes('fields', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsState()
    {
        return true;
    }

    /**
     * @param array  $parameters
     * @param string $accessToken
     *
     * @return string
     */
    private function getSig(array $parameters, $accessToken)
    {
        ksort($parameters);

        $string = '';

        foreach ($parameters as $parameter => $value) {
            $string .= $parameter.'='.$value;
        }

        $string .= strtolower(md5($accessToken.$this->options['secret']));

        return md5($string);
    }
}
