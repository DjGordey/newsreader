<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SourceReader
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function readUrl(string $url): string
    {
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                return $response->getContent();
            }
        } catch (ExceptionInterface $e) {
        }
        return '';
    }
}