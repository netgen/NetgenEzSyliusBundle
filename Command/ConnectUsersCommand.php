<?php

namespace Netgen\Bundle\EzSyliusBundle\Command;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\User\UserReference;
use Netgen\Bundle\EzSyliusBundle\Entity\EzSyliusUser;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectUsersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezsylius:user:connect')
            ->addOption('sylius-user-type', null, InputOption::VALUE_REQUIRED, 'Sylius user type to connect')
            ->addOption('sylius-user-email', null, InputOption::VALUE_REQUIRED, 'Sylius user e-mail to connect')
            ->addOption('ez-user-login', null, InputOption::VALUE_REQUIRED, 'eZ Platform user login to connect')
            ->setDescription('Connects eZ Platform and Sylius users.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allows you to connect eZ Platform and Sylius users in order to allow shared login.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Connecting Sylius and eZ Platform users');

        $syliusUserType = $input->getOption('sylius-user-type');
        $syliusUserEmail = $input->getOption('sylius-user-email');
        $eZUserLogin = $input->getOption('ez-user-login');

        $syliusUser = null;
        $eZUser = null;

        while (true) {
            while (!in_array($syliusUserType, array('shop', 'admin'))) {
                if ($syliusUserType !== null) {
                    $io->error('Selected user type is invalid');
                }

                $syliusUserType = $io->choice(
                    'Select Sylius user type to connect',
                    array('shop', 'admin')
                );
            }

            $syliusUser = $this->loadSyliusUser($syliusUserType, $syliusUserEmail);
            while (empty($syliusUserEmail) || !$syliusUser instanceof UserInterface) {
                if ($syliusUserEmail !== null) {
                    $io->error('Selected Sylius user does not exist');
                }

                $syliusUserEmail = $io->ask('Enter Sylius user e-mail to connect');

                $syliusUser = $this->loadSyliusUser($syliusUserType, $syliusUserEmail);
            }

            $eZUser = $this->loadEzUser($eZUserLogin);
            while (empty($eZUserLogin) || !$eZUser instanceof UserReference) {
                if ($eZUserLogin !== null) {
                    $io->error('Selected eZ Platform user does not exist');
                }

                $eZUserLogin = $io->ask('Enter eZ Platform user login to connect');

                $eZUser = $this->loadEzUser($eZUserLogin);
            }

            $io->table(
                array('User', 'E-mail', 'Username'),
                array(
                    array(
                        sprintf('Sylius user (%s)', $syliusUserType),
                        $syliusUser->getEmail(),
                        $syliusUser->getUsername(),
                    ),
                    array(
                        'eZ Platform user',
                        $eZUser->email,
                        $eZUser->login,
                    ),
                )
            );

            if ($io->confirm('Are you sure you want to connect the selected users?')) {
                break;
            }

            $syliusUserType = null;
            $syliusUserEmail = null;
            $eZUserLogin = null;
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var \Doctrine\ORM\EntityRepository $eZSyliusUserRepository */
        $eZSyliusUserRepository = $this->getContainer()->get('netgen_ez_sylius.repository.ez_sylius_user');

        $eZSyliusUser = $eZSyliusUserRepository->findOneBy(
            array(
                'syliusUserId' => $syliusUser->getId(),
                'syliusUserType' => $syliusUserType,
            )
        );

        if (!$eZSyliusUser instanceof EzSyliusUser) {
            $eZSyliusUser = new EzSyliusUser($syliusUser->getId(), $syliusUserType);
        }

        $eZSyliusUser->setEzUserId($eZUser->id);
        $entityManager->persist($eZSyliusUser);
        $entityManager->flush();

        $io->success(
            sprintf(
                'Sylius %s user %s and eZ Platform user %s successfully connected.',
                $syliusUserType,
                $syliusUserEmail,
                $eZUserLogin
            )
        );
    }

    /**
     * Loads the Sylius user from the repo.
     *
     * @param string $userType
     * @param string $email
     *
     * @return \Sylius\Component\User\Model\UserInterface
     */
    protected function loadSyliusUser($userType, $email)
    {
        /** @var \Sylius\Component\User\Repository\UserRepositoryInterface $userRepository */
        $userRepository = $this->getContainer()->get(
            sprintf('sylius.repository.%s_user', $userType)
        );

        return $userRepository->findOneByEmail($email);
    }

    /**
     * Loads the eZ Platform user from the repo.
     *
     * @param string $login
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function loadEzUser($login)
    {
        if (empty($login)) {
            return null;
        }

        /** @var \eZ\Publish\API\Repository\UserService $userService */
        $userService = $this->getContainer()->get('ezpublish.api.service.user');

        try {
            return $userService->loadUserByLogin($login);
        } catch (NotFoundException $e) {
            return null;
        }
    }
}
