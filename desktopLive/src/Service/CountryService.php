<?php
namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;

class CountryService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCountries(): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://restcountries.com/v3.1/all?fields=name'
            );
            $countries = $response->toArray();

            return array_map(function ($country) {
                return $country['name']['common'] ?? null;
            }, $countries);
        } catch (\Exception $e) {
            // En cas d'erreur, on renvoie une liste statique temporaire
            return ['France', 'Madagascar', 'Germany', 'Canada'];
        }
    }
}
