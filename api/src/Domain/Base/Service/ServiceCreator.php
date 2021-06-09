<?php


namespace App\Domain\Base\Service;

use App\Domain\Base\Data\AbstractData;
use App\Domain\Base\Data\AuthorisationData;

/**
 * Description of the common insert service functionality.
 * @package App\Domain\Base\Service
 */
class ServiceCreator extends AbstractService
{
    /**
     * Functionality of the create service.
     *
     * @param AuthorisationData $authorisation Authorisation data
     * @param array<string, mixed> $data The form data
     *
     * @return array|AbstractData|null Service output
     * @throws \App\Domain\Base\Data\AuthorisationException
     */
    public function service(AuthorisationData $authorisation, array $data): array|AbstractData|null
    {
        parent::service($authorisation, $data);

        // Input validation
        $this->validator->validateCreate($data);

        // Insert entity and get new ID
        $result = $this->repository->insert((object)$data);

        // Logging
        $entityName = $this->repository->getEntityName();
        $this->logger->info("$entityName created successfully: $result->id");

        return $result;
    }
}
