<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\SessionInterface;
use App\DataObjects\DataTableQueryParams;
use Psr\Http\Message\ServerRequestInterface;

class RequestService
{
    public function __construct(private readonly SessionInterface $session)
    {
    }

    public function getReferer(ServerRequestInterface $request): string
    {
        $referer = $request->getHeader('referer')[0] ?? '';

        if (! $referer) {
            return $this->session->get('previousUrl');
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);

        if ($refererHost !== $request->getUri()->getHost()) {
            $referer = $this->session->get('previousUrl');
        }

        return $referer;
    }

    public function isXhr(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getDataTableQueryParameters(ServerRequestInterface $request): DataTableQueryParams
    {
        $params = $request->getQueryParams();

        $orderBy = $params['columns'][$params['order'][0]['column']]['data'];
        $orderDir = $params['order'][0]['dir'];

        return new DataTableQueryParams(
            (int) $params['start'],
            (int) $params['length'],
            $orderBy,
            $orderDir,
            $params['search']['value'],
            (int) $params['draw']
        );
    }
}
