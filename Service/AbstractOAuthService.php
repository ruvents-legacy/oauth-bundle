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
use Symfony\Component\HttpFoundation\Request;
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
     * {@inheritdoc}
     */
    public function getState(Request $request)
    {
        return $request->query->get('state');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsState()
    {
        return true;
    }

    /**
     * @param string $host
     * @param string $path
     * @param array  $query
     * @param array  $post
     * @param array  $headers
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
        array $headers = [],
        $method = 'GET',
        $scheme = 'https'
    ) {
        $response = $this->makeRequest($host, $path, $query, $post, $headers, $method, $scheme);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $host
     * @param string $path
     * @param array  $query
     * @param array  $post
     * @param array  $headers
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
        array $headers = [],
        $method = 'GET',
        $scheme = 'https'
    ) {
        $uri = $this->uriFactory
            ->createUri('')
            ->withScheme($scheme)
            ->withHost($host)
            ->withPath($path)
            ->withQuery(http_build_query($query, '', '&'));

        $body = null;

        if ('post' === strtolower($method)) {
            $body = http_build_query($post, '', '&');
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $request = $this->messageFactory->createRequest($method, $uri, $headers, $body);

        return $this->httpClient->sendRequest($request);
    }
}
