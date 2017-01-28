<?php

namespace Ruvents\OAuthBundle\Service;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
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
}
