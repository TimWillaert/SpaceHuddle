<?php

namespace App\Domain\Base\Service;

use App\Data\AuthorisationException;
use App\Domain\Base\Data\AbstractData;
use App\Data\AuthorisationData;

/**
 * Description of the common read service functionality.
 * @package App\Domain\Base\Service
 */
class ServiceSingleReader extends AbstractService
{
    /**
     * Functionality of the read single entity service.
     *
     * @param AuthorisationData $authorisation Authorisation data
     * @param array<string, mixed> $bodyData Form data from the request body
     * @param array<string, mixed> $urlData Url parameter from the request
     *
     * @return array|AbstractData|null Service output
     * @throws AuthorisationException
     */
    public function service(
        AuthorisationData $authorisation,
        array $bodyData,
        array $urlData
    ): array|AbstractData|null {
        parent::service($authorisation, $bodyData, $urlData);
        $data = array_merge($bodyData, $urlData);

        $id = $data["id"];
        // Input validation
        // ...

        // Fetch data from the database
        $result = $this->repository->getById($id);

        // Optional: Add or invoke your complex business logic here
        // ...

        // Optional: Map result
        // ...

        return $result;
    }
}
