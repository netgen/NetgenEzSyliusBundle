<?php

namespace Netgen\Bundle\EzSyliusBundle\Authentication;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('success' => true, 'username' => $token->getUsername()));
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    /**
     * Injects the ConfigResolver to potentially override default_target_path for redirections after authentication success.
     *
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $defaultPage = $configResolver->getParameter('default_page');
        if ($defaultPage !== null) {
            $this->options['default_target_path'] = $defaultPage;
        }
    }
}
