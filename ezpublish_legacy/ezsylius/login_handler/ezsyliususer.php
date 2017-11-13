<?php

use eZ\Publish\API\Repository\Values\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class eZSyliusUser extends eZUser
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static $container;

    /**
     * @var \Netgen\Bundle\EzSyliusBundle\Provider\UserProviderInterface
     */
    protected static $userProvider;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected static $encoderFactory;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        self::$container = ezpKernel::instance()->getServiceContainer();

        $ini = eZINI::instance();

        self::$userProvider = self::$container->get($ini->variable('SyliusLoginHandler', 'UserProvider'));
        self::$encoderFactory = self::$container->get('security.encoder_factory');
    }

    /**
     * Checks if there's an admin Sylius user with login entered by the user.
     * If so, it checks if entered password is correct and if that is the case,
     * it returns the legacy user.
     *
     * @param string $login
     * @param string $password
     * @param bool $authenticationMatch
     *
     * @return \eZUser|bool
     */
    public static function loginUser($login, $password, $authenticationMatch = false)
    {
        try {
            /** @var \Netgen\Bundle\EzSyliusBundle\Entity\AdminUser $syliusUser */
            $syliusUser = self::$userProvider->loadUserByUsername($login);
        } catch (UsernameNotFoundException $e) {
            self::loginFailed(false, $login);

            return false;
        }

        $apiUser = $syliusUser->getAPIUser();
        if (!$apiUser instanceof User) {
            self::loginFailed(false, $login);

            return false;
        }

        $encoder = self::$encoderFactory->getEncoder($syliusUser);

        // Check if login and password are legit.
        if (
            !$encoder->isPasswordValid(
                $syliusUser->getPassword(),
                $password,
                $syliusUser->getSalt()
            )
        ) {
            self::loginFailed($apiUser->getUserId(), $login);

            return false;
        }

        $user = self::fetch($apiUser->getUserId());
        if (!$user instanceof eZUser) {
            self::loginFailed($apiUser->getUserId(), $login);

            return false;
        }

        self::loginSucceeded($user);

        return $user;
    }
}
