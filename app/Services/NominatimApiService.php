<?php

namespace App\Services;

use App\Contracts\OblastsApiProvider;
use App\Exceptions\NominatimApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NominatimApiService implements OblastsApiProvider
{
    private const RATE_LIMIT_DELAY = 1;

    private const REQUEST_TIMEOUT = 30;

    const PROVIDER_NAME = 'nominatim';

    private readonly Client $httpClient;

    private readonly string $baseUrl;

    public string $country;

    public function __construct()
    {
        $this->httpClient = new Client;
        $this->baseUrl = config('services.nominatim.base_url');

        if (! $this->baseUrl) {
            throw new \RuntimeException('Nominatim api base url is not defined');
        }

        $this->country = config('app.country');
    }

    public function getUkrainianOblastNames(): array
    {
        return [
            'Kyiv Oblast', 'Kharkiv Oblast', 'Lviv Oblast', 'Dnipropetrovsk Oblast',
            'Donetsk Oblast', 'Zaporizhzhia Oblast', 'Odesa Oblast', 'Poltava Oblast',
            'Chernihiv Oblast', 'Cherkasy Oblast', 'Zhytomyr Oblast', 'Sumy Oblast',
            'Kherson Oblast', 'Mykolaiv Oblast', 'Kirovohrad Oblast', 'Vinnytsia Oblast',
            'Chernivtsi Oblast', 'Rivne Oblast', 'Volyn Oblast', 'Ternopil Oblast',
            'Ivano-Frankivsk Oblast', 'Zakarpattia Oblast', 'Khmelnytskyi Oblast',
            'Luhansk Oblast', 'Crimea',
        ];
    }

    public function fetchOblastPolygonData(string $oblastName): ?array
    {
        try {
            $response = $this->httpClient->get('/search', [
                'base_uri' => $this->baseUrl,
                'query' => [
                    'q' => "{$oblastName}, {$this->country}",
                    'format' => 'json',
                    'polygon_geojson' => 1,
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'Oblast-Management-System/1.0',
                    'accept' => 'application/vnd.geo+json',
                ],
                'timeout' => self::REQUEST_TIMEOUT,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data) || ! isset($data[0]['geojson'])) {
                Log::warning("No polygon data found for oblast: {$oblastName}");

                return null;
            }

            sleep(self::RATE_LIMIT_DELAY);

            $result = $data[0] ?? [];

            $validator = Validator::make($result, [
                'name' => 'required',
                'display_name' => 'required',
                'lat' => 'required',
                'lon' => 'required',
                'geojson' => 'required|array',
                'geojson.coordinates' => 'required',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $hashInput = $result['lat'].$result['lon'];
            $locationHash = hash('md5', $hashInput);

            return [
                'name' => $result['name'],
                'display_name' => $result['display_name'],
                'lat' => $result['lat'],
                'lon' => $result['lon'],
                'polygon' => $result['geojson'],
                'provider_name' => self::PROVIDER_NAME,
                'provider_id' => $locationHash,
                'created_at' => now(),
                'updated_at' => now(),
            ];

        } catch (GuzzleException $e) {
            Log::error("Failed to fetch data for {$oblastName}: ".$e->getMessage());
            throw new NominatimApiException("API request failed for {$oblastName}", 0, $e);
        }
    }
}
