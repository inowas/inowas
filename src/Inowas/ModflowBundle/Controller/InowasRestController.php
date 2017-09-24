<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Id\UserId;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\ModflowBundle\Exception\InvalidUuidException;
use Inowas\ModflowBundle\Exception\UserNotAuthenticatedException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class InowasRestController extends FOSRestController
{

    protected function getContentAsArray(Request $request): array
    {
        $content = $request->getContent();
        if(empty($content)){
            return [];
        }

        return json_decode($content, true);
    }

    protected function assertUserIsLoggedInCorrectly(): void
    {
        $user = $this->getUser();
        if (! $user instanceof User){
            throw UserNotAuthenticatedException::withMessage(sprintf(
                'Something went wrong with the authentication. User is not authenticated. Please check your credentials.'
            ));
        }
    }

    protected function assertUuidIsValid(string $id): void
    {
        if (! Uuid::isValid($id)){
            throw InvalidUuidException::withId($id);
        }
    }

    protected function assertContainsKey(string $key, array $content): void
    {
        if (! array_key_exists($key, $content)){
            throw InvalidArgumentException::withMessage(sprintf(
                'Expected key \'%s\' not found in submitted data. Submitted keys are: %s.', $key, implode(', ', array_keys($content))
            ));
        }
    }

    protected function containsKey(string $key, array $content): bool
    {
        return array_key_exists($key, $content);
    }

    protected function assertGeometryIsValid(array $geometry): void
    {
        if (! Geometry::isValid($geometry)) {
            throw InvalidArgumentException::withMessage(sprintf('The geometry array is not valid.'));
        }
    }

    protected function getUserId(): UserId
    {
        $user = $this->getUser();
        if (! $user instanceof User){
            throw UserNotAuthenticatedException::withMessage(sprintf(
                'Something went wrong with the authentication. User is not authenticated. Please check your credentials.'
            ));
        }

        return UserId::fromString($this->getUser()->getId()->toString());
    }
}
