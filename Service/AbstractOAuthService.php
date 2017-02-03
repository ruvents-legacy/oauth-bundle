<?php

namespace Ruvents\OAuthBundle\Service;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use Psr\Http\Message\ResponseInterface;
use Ruvents\OAuthBundle\OAuthServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractOAuthService implements OAuthServiceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param array               $options
     * @param UriFactory|null     $uriFactory
     * @param MessageFactory|null $messageFactory
     * @param HttpClient|null     $httpClient
     */
    public function __construct(
        array $options = [],
        UriFactory $uriFactory = null,
        MessageFactory $messageFactory = null,
        HttpClient $httpClient = null
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->uriFactory = $uriFactory ?: UriFactoryDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @param string $host
     * @param string $path
     * @param array  $query
     * @param array  $post
     * @param string $method
     * @param string $scheme
     *
     * @return mixed
     */
    protected function makeRequestAndJsonDecode(
        $host,
        $path,
        array $query = [],
        array $post = [],
        $method = 'GET',
        $scheme = 'https'
    ) {
        $response = $this->makeRequest($host, $path, $query, $post, $method, $scheme);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $host
     * @param string $path
     * @param array  $query
     * @param array  $post
     * @param string $method
     * @param string $scheme
     *
     * @return ResponseInterface
     */
    protected function makeRequest(
        $host,
        $path,
        array $query = [],
        array $post = [],
        $method = 'GET',
        $scheme = 'https'
    ) {
        $uri = $this->uriFactory
            ->createUri('')
            ->withScheme($scheme)
            ->withHost($host)
            ->withPath($path)
            ->withQuery(http_build_query($query));

        $request = $this->messageFactory->createRequest($method, $uri);

        return $this->httpClient->sendRequest($request);
    }
}
