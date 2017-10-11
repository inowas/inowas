<?php

namespace Inowas\AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Inowas\AppBundle\Model\User;
use Inowas\ModflowBundle\Exception\UserNotAuthenticatedException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/** @noinspection LongInheritanceChainInspection */
class UserController extends InowasRestController
{
    /**
     * Returns the api-key of the user.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the api-key of the user.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Post("/users/credentials")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="username", nullable=false, strict=true, description="Username or Email")
     * @RequestParam(name="password", nullable=false, strict=true, description="Password")
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function getUserCredentialsAction(ParamFetcher $paramFetcher): JsonResponse
    {
        $username = $paramFetcher->get('username');
        $password = $paramFetcher->get('password');

        /** @var User $user */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $passwordIsValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

        if (! $passwordIsValid){
            throw new HttpException(401, 'Credentials are not valid.');
        }

        $data = [];
        $data['api_key'] = $user->getApiKey();

        return new JsonResponse($data);
    }

    /**
     * Returns the user profile and roles of the user.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the user profile and roles of the user.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Get("/users/profile")
     *
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     * @throws \LogicException
     */
    public function getUserProfileAction(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (! $user instanceof User){
            throw UserNotAuthenticatedException::withMessage(sprintf(
                'Something went wrong with the authentication. User is not authenticated. Please check your credentials.'
            ));
        }

        $response = array();
        $response['user_name'] = $user->getUsername();
        $response['name'] = $user->getName();
        $response['email'] = $user->getEmail();
        $response['roles'] = $user->getRoles();

        return new JsonResponse($response);
    }

    /**
     * Returns the userProfile for the user.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the api-key of the user.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Put("/users/profile")
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws UserNotAuthenticatedException
     */
    public function putUserProfileAction(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (! $user instanceof User){
            throw UserNotAuthenticatedException::withMessage(sprintf(
                'Something went wrong with the authentication. User is not authenticated. Please check your credentials.'
            ));
        }

        $content = $this->getContentAsArray($request);

        $key = 'user_name';
        if ($this->containsKey($key, $content)) {
            $user->setUsername($this->getValueByKey($key, $content));
        }

        $key = 'name';
        if ($this->containsKey($key, $content)) {
            $user->setName($this->getValueByKey($key, $content));
        }

        $key = 'email';
        if ($this->containsKey($key, $content)) {
            $user->setEmail($this->getValueByKey($key, $content));
        }

        $this->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->generateUrl('get_user_profile'), 303);
    }
}
