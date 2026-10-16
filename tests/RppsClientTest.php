<?php
declare(strict_types=1);

namespace QrCommunication\RppsApi\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use QrCommunication\RppsApi\Dto\Identite;
use QrCommunication\RppsApi\Dto\RppsSearchCriteria;
use QrCommunication\RppsApi\Dto\RppsSearchResult;
use QrCommunication\RppsApi\Enum\SourceName;
use QrCommunication\RppsApi\Exception\RppsException;
use QrCommunication\RppsApi\RppsClient;
use QrCommunication\RppsApi\RppsClientOptions;

final class RppsClientTest extends TestCase
{
    private const BASE_URL = 'https://rpps-fixture.invalid/resources';
    private const RESOURCE_PATH = '/resources/fffda7e9-0ea2-4c35-bba0-4496f3af935d/data/';
    private const RPPS = '10000000001';

    private array $transportViolations = [];
    private ?MockHandler $responses = null;
    private int $expectedRequests = 0;
    private int $receivedRequests = 0;

    protected function tearDown(): void
    {
        // safeQuery catches Throwable, so verify the guard outside SDK execution too.
        self::assertSame([], $this->transportViolations, 'Unexpected HTTP request refused before mock consumption.');
        self::assertSame($this->expectedRequests, $this->receivedRequests);
        if ($this->responses !== null) {
            self::assertSame(0, $this->responses->count(), 'Every queued response must be consumed.');
        }
        parent::tearDown();
    }

    public function testProfileMapsSyntheticRowsAndFollowsPagination(): void
    {
        $row = $this->row(self::RPPS, 'SYNTHETIQUE');
        $next = self::BASE_URL . '/fffda7e9-0ea2-4c35-bba0-4496f3af935d/data/?page=2';
        $client = $this->client(
            [
                $this->response(['data' => [$row], 'links' => ['next' => $next]]),
                $this->response(['data' => [$this->row(self::RPPS, 'SECONDAIRE')], 'links' => ['next' => null]]),
            ],
            [
                $this->request(['page_size' => '2', 'page' => '1', 'Identifiant PP__exact' => self::RPPS]),
                $this->request(['page' => '2']),
            ],
        );

        $profile = $client->getByRpps('  ' . self::RPPS . '  ');

        self::assertSame(self::RPPS, $profile->rpps);
        self::assertSame('8' . self::RPPS, $profile->identificationNationale);
        self::assertEquals(Identite::fromRow($row), $profile->identite);
        self::assertStringContainsString('SYNTHETIQUE', json_encode($profile->identite, JSON_THROW_ON_ERROR));
        self::assertSame([], $profile->activites, 'Rows without a structure must not create activities.');
        self::assertSame([], $profile->diplomesEtAutorisations);
        self::assertSame([], $profile->cartesCps);
        self::assertCount(6, $profile->sources);
    }

    public function testSearchSendsFiltersAndDeduplicatesMappedResults(): void
    {
        $first = $this->row(self::RPPS, 'SYNTHETIQUE');
        $second = $this->row('10000000002', 'AUTRE');
        $criteria = new RppsSearchCriteria(nom: 'SYNTHETIQUE', prenom: 'Alice', codePostal: '75001');
        $client = $this->client(
            [$this->response(['data' => [$first, $first, $this->row('', 'IGNORE'), $second], 'meta' => ['total' => 42]])],
            [$this->request([
                'page_size' => '2',
                'page' => '3',
                "Nom d'exercice__contains" => 'SYNTHETIQUE',
                "Prénom d'exercice__contains" => 'Alice',
                'Code postal (coord. structure)__exact' => '75001',
            ])],
        );

        $result = $client->search($criteria, 3);

        self::assertSame(42, $result->total);
        self::assertSame(3, $result->page);
        self::assertSame(2, $result->pageSize);
        self::assertSame($criteria, $result->criteria);
        self::assertCount(2, $result->results);
        self::assertEquals(RppsSearchResult::fromRow($first), $result->results[0]);
        self::assertEquals(RppsSearchResult::fromRow($second), $result->results[1]);
        self::assertStringContainsString('SYNTHETIQUE', json_encode($result->results[0], JSON_THROW_ON_ERROR));
    }

    public function testPostalPrefixUsesContainsAndExplicitPageSize(): void
    {
        $client = $this->client(
            [$this->response(['data' => []])],
            [$this->request(['page_size' => '7', 'page' => '1', 'Code postal (coord. structure)__contains' => '75'])],
        );

        $result = $client->search(new RppsSearchCriteria(codePostal: '75'), 1, 7);

        self::assertSame([], $result->results);
        self::assertSame(0, $result->total);
        self::assertSame(7, $result->pageSize);
    }

    public function testSearchDefaultsTotalToReturnedRowCount(): void
    {
        $client = $this->client(
            [$this->response(['data' => [$this->row(self::RPPS, 'SYNTHETIQUE')]])],
            [$this->request(['page_size' => '2', 'page' => '1', "Nom d'exercice__contains" => 'SYNTHETIQUE'])],
        );

        $result = $client->search(new RppsSearchCriteria(nom: 'SYNTHETIQUE'));

        self::assertSame(1, $result->total);
        self::assertCount(1, $result->results);
    }

    public function testProfileRetainsSourceFailureWithoutInventingIdentity(): void
    {
        $client = $this->client(
            [new Response(503, ['Content-Type' => 'application/json'], '{"error":"synthetic unavailable"}')],
            [$this->request(['page_size' => '2', 'page' => '1', 'Identifiant PP__exact' => self::RPPS])],
        );

        $profile = $client->getByRpps(self::RPPS);

        self::assertSame(self::RPPS, $profile->rpps);
        self::assertSame('', $profile->identificationNationale);
        self::assertEquals(new Identite(), $profile->identite);
        self::assertSame([], $profile->activites);
        self::assertStringContainsString('503', json_encode($profile->sources, JSON_THROW_ON_ERROR));
    }

    public function testSearchPropagatesHttpFailure(): void
    {
        $client = $this->client(
            [new Response(503, [], 'synthetic unavailable')],
            [$this->request(['page_size' => '2', 'page' => '1', "Nom d'exercice__contains" => 'SYNTHETIQUE'])],
        );

        $this->expectException(ServerException::class);
        $client->search(new RppsSearchCriteria(nom: 'SYNTHETIQUE'));
    }

    public function testSearchPropagatesConnectionFailure(): void
    {
        $client = $this->client(
            [new ConnectException('synthetic connection failure', new Request('GET', self::BASE_URL))],
            [$this->request(['page_size' => '2', 'page' => '1', "Nom d'exercice__contains" => 'SYNTHETIQUE'])],
        );

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('synthetic connection failure');
        $client->search(new RppsSearchCriteria(nom: 'SYNTHETIQUE'));
    }

    public function testInvalidRppsFailsBeforeTransport(): void
    {
        $client = $this->client([], []);

        $this->expectException(RppsException::class);
        $client->getByRpps('invalid-rpps');
    }

    public function testEmptySearchFailsBeforeTransport(): void
    {
        $client = $this->client([], []);

        $this->expectException(RppsException::class);
        $client->search(new RppsSearchCriteria());
    }

    private function client(array $responses, array $requests): RppsClient
    {
        $this->responses = new MockHandler($responses);
        $this->expectedRequests = count($requests);
        $mock = $this->responses;
        $guard = function (RequestInterface $request, array $options) use ($mock, $requests): PromiseInterface {
            $query = [];
            foreach (explode('&', $request->getUri()->getQuery()) as $pair) {
                if ($pair === '') {
                    continue;
                }
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
                $query[urldecode($key)] = urldecode($value);
            }
            ksort($query);
            $uri = $request->getUri();
            $actual = [
                'method' => $request->getMethod(),
                'origin' => $uri->getScheme() . '://' . $uri->getHost(),
                'port' => $uri->getPort(),
                'userinfo' => $uri->getUserInfo(),
                'fragment' => $uri->getFragment(),
                'path' => $uri->getPath(),
                'query' => $query,
                'accept' => $request->getHeaderLine('Accept'),
                'authorization' => $request->getHeaderLine('Authorization'),
                'body' => (string) $request->getBody(),
            ];
            $expected = $requests[$this->receivedRequests] ?? null;
            ++$this->receivedRequests;
            if ($actual !== $expected) {
                $this->transportViolations[] = $actual;
                throw new \LogicException('Refused unexpected synthetic HTTP request.');
            }
            return $mock($request, $options);
        };

        return new RppsClient(
            new RppsClientOptions(
                tabularBaseUrl: self::BASE_URL,
                tabularPageSize: 2,
                disabledSources: [
                    SourceName::Diplomes->value,
                    SourceName::SavoirFaire->value,
                    SourceName::CarteCps->value,
                    SourceName::Mssante->value,
                    SourceName::Fhir->value,
                ],
            ),
            new Client(['handler' => HandlerStack::create($guard)]),
        );
    }

    private function request(array $query): array
    {
        ksort($query);
        return [
            'method' => 'GET',
            'origin' => 'https://rpps-fixture.invalid',
            'port' => null,
            'userinfo' => '',
            'fragment' => '',
            'path' => self::RESOURCE_PATH,
            'query' => $query,
            'accept' => 'application/json',
            'authorization' => '',
            'body' => '',
        ];
    }

    private function row(string $rpps, string $name): array
    {
        return [
            'Identifiant PP' => $rpps,
            'Identification nationale PP' => ' 8' . $rpps . ' ',
            "Nom d'exercice" => $name,
            "Prénom d'exercice" => 'Alice',
            'Code profession' => '10',
            'Libellé profession' => 'Profession synthétique',
        ];
    }

    private function response(array $payload): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
