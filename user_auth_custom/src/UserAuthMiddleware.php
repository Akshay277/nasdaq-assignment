<?php

namespace Drupal\user_auth_custom;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;


/**
 * FirstMiddleware middleware.
 */
class UserAuthMiddleware implements HttpKernelInterface {

  /**
   * The kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the FirstMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel, EntityTypeManagerInterface $entityTypeManager)
  {
      $this->httpKernel = $http_kernel;
      $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {

    $authtoken = $request->query->get('authtoken');
    if (!empty($authtoken)) {
      $userObj = $this->entityTypeManager
        ->getStorage('user')
        ->loadByProperties(
          [
            'field_auth_token' => $authtoken,
          ]);
      if (!empty($userObj)) {
        $userObj = reset($userObj);
        user_login_finalize($userObj);
      }
    }
  
    return $this->httpKernel->handle($request, $type, $catch);
  }
}


