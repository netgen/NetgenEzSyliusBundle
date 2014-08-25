- install ezpublish 2014.07

- add to main composer.json

        "doctrine/orm": "~2.3",
        "doctrine/doctrine-bundle": "~1.3@beta",
        "sylius/core-bundle": "0.10.*@dev",
        "friendsofsymfony/rest-bundle": "1.4.*@dev",
        "friendsofsymfony/user-bundle": "2.0.*@dev",
        "knplabs/gaufrette": "0.2.*@dev",
        "knplabs/knp-gaufrette-bundle": "0.2.*@dev",
        "doctrine/phpcr-bundle": "1.1.*",
        "jackalope/jackalope": "1.1.3",
        "jackalope/jackalope-doctrine-dbal": "1.1.2",
        "symfony-cmf/routing": "1.1.0 as 1.2.0",
        "winzou/state-machine-bundle": "~0.1",
        "netgen/metadata-bundle": "0.1",
        "netgen/ngsyliusprice": "dev-master"


    "repositories": [
        {
            "type": "vcs",
            "url": "git@bitbucket.org:netgen/netgenmetadatabundle.git"
        },
        {
            "type": "vcs",
            "url": "git@bitbucket.org:netgen/ngsyliusprice.git"
        }
    ]

- do
php composer.phar update

- in src/Netgen
git clone git@bitbucket.org:netgen/ezsyliusbundle.git

- enable in ezpublish/EzPublishKernel.php (existing DoctrineBundle must be enabled AFTER Sylius):
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new Sylius\Bundle\MoneyBundle\SyliusMoneyBundle(),
            new Sylius\Bundle\OrderBundle\SyliusOrderBundle(),
            new Sylius\Bundle\CartBundle\SyliusCartBundle(),
            new Sylius\Bundle\FlowBundle\SyliusFlowBundle(),
            new Sylius\Bundle\ShippingBundle\SyliusShippingBundle(),
            new Sylius\Bundle\PromotionBundle\SyliusPromotionBundle(),
            new Sylius\Bundle\AddressingBundle\SyliusAddressingBundle(),
            new Sylius\Bundle\SettingsBundle\SyliusSettingsBundle(),
            new Sylius\Bundle\PaymentBundle\SyliusPaymentBundle(),
            new Sylius\Bundle\PayumBundle\SyliusPayumBundle(),
            new Sylius\Bundle\TaxationBundle\SyliusTaxationBundle(),
            new Sylius\Bundle\PricingBundle\SyliusPricingBundle(),
            new Sylius\Bundle\ProductBundle\SyliusProductBundle(),
            new Sylius\Bundle\AttributeBundle\SyliusAttributeBundle(),
            new Sylius\Bundle\VariationBundle\SyliusVariationBundle(),
            new Sylius\Bundle\CoreBundle\SyliusCoreBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new winzou\Bundle\StateMachineBundle\winzouStateMachineBundle(),
            new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new Netgen\EzSyliusBundle\NetgenEzSyliusBundle(),
            new Netgen\Bundle\MetadataBundle\NetgenMetadataBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Payum\Bundle\PayumBundle\PayumBundle(),


- add to ezpublih/config/ezpublish.yml
imports:
    - { resource: "@NetgenEzSyliusBundle/Resources/config/ezsylius.yml" }

- add to ezpublish/config/routing.yml

sylius_cart:
    resource: "@SyliusCartBundle/Resources/config/routing.yml"
    prefix: /cart



- add to ezpublish/config/parameters.yml

    sylius.mailer.transport: smtp
    sylius.mailer.host: 127.0.0.1
    sylius.mailer.user: null
    sylius.mailer.password: null
    sylius.locale: en
    sylius.secret: abc
    sylius.currency: EUR
    sylius.cache:
        type: file_system
    sylius.order.pending.duration: '3 hours'


- add to ezpublish/config/config.yml


doctrine:
    orm:
        auto_generate_proxy_classes: %kernel.debug%
        entity_managers:
            eng_repository_connection:
                auto_mapping: true
                mappings:
                    gedmo_loggable:
                        type: annotation
                        prefix: Gedmo\Loggable\Entity
                        dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Loggable/Entity"
                        is_bundle: false
                filters:
                    softdeleteable:
                        class: Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter
                        enabled: true

sylius_money:
    driver: doctrine/orm
    currency: %sylius.currency%
    locale: %sylius.locale%

sylius_order:
    driver: doctrine/orm
    classes:
        order_item:
            model: Netgen\EzSyliusBundle\Entity\CartItem


sylius_promotion:
    driver: doctrine/orm

sylius_core:
    driver: doctrine/orm
    emails:
        enabled: false
    routing:

sylius_settings:
    driver: doctrine/orm

doctrine_cache:
    providers:
        sylius_settings:
            type: file_system

sylius_cart:
    resolver: netgen_ez_sylius.cart_item_resolver
    classes: ~

fos_rest:
     view:
         view_response_listener: false
         failed_validation: HTTP_BAD_REQUEST
         default_engine: php
         formats:
             json: true
     body_listener:
         decoders:
             json: fos_rest.decoder.json

stof_doctrine_extensions:
     orm:
         eng_repository_connection:
             timestampable: true
             sluggable: true

sylius_payment:
    classes:
        payment:
            controller: Sylius\Bundle\CoreBundle\Controller\PaymentController
    gateways:
        dummy: Test

sylius_payum: ~

payum:
    security:
        token_storage:
            Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken:
                doctrine:
                    driver: orm

    contexts:
        dummy:
            custom:
                actions:

sylius_product:
    driver: doctrine/orm

sylius_taxation:
    driver: doctrine/orm

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: %sylius.model.user.class%
    group:
        group_class: Sylius\Bundle\CoreBundle\Model\Group
    profile:
        form:
            type: sylius_user_profile
    registration:
        form:
            type: sylius_user_registration

cmf_routing:
    dynamic:
        enabled: true
        route_provider_service_id: ezpublish.chain_router




- run:
sudo php ezpublish/console doctrine:schema:update

- import sylius_product class to eZ Publish using provided package in Resources/packages folder
