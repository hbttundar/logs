<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Log;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{


    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly ManagerRegistry     $doctrine,
        protected readonly ValidatorInterface  $validator
    ) {
    }

    protected function createApiResponse($data, $statusCode = 200): Response
    {
        $json = $this->serialize($data);
        return new Response($json, $statusCode, ['Content-Type' => 'application/json']);
    }

    protected function serialize($data, $format = 'json'): string
    {
        return $this->serializer->serialize($data, $format);
    }

    protected function getLogRepository(): ObjectRepository
    {
        return $this->doctrine->getRepository(Log::class);
    }

    protected function createApiErrorResponse(array $violations, int $code = Response::HTTP_BAD_REQUEST): Response
    {
        $errors = [];
        /**
         * @var ConstraintViolationInterface $violation
         */
        foreach ($violations as $violation) {
            $errors['errors'][] = $violation[0]->getMessage();
        }
        $json = $this->serialize($errors);
        return new Response($json, $code, ['Content-Type' => 'application/json']);
    }
}
