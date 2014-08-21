- install ezpublish 2014.07

- add to main composer.json

        "sylius/money-bundle": "0.10.*@dev",
        "sylius/cart-bundle": "0.10.*@dev",
        "sylius/order-bundle": "0.10.*@dev",
        "sylius/resource-bundle": "0.10.*@dev",
        "sylius/flow-bundle": "0.10.*@dev",
        "sylius/product": "0.10.*@dev",
        "sylius/order": "0.10.*@dev",
        "doctrine/orm": "~2.3",
        "friendsofsymfony/rest-bundle": "1.4.*@dev"

- do
php composer.phar update

- in src/Netgen
git clone git@bitbucket.org:netgen/ezsyliusbundle.git

- enable in ezpublish/EzPublishKernel.php (DoctrineBundle must be enabled AFTER Sylius):
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new Sylius\Bundle\MoneyBundle\SyliusMoneyBundle(),
            new Sylius\Bundle\OrderBundle\SyliusOrderBundle(),
            new Sylius\Bundle\CartBundle\SyliusCartBundle(),
            new Sylius\Bundle\FlowBundle\SyliusFlowBundle(),
            new DoctrineBundle(),
            new Netgen\EzSyliusBundle\NetgenEzSyliusBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),


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
    format_listener:
        rules:
            - prefer_extension: true
    body_listener:
        decoders:
            json: fos_rest.decoder.json




- run:
sudo php ezpublish/console doctrine:schema:update

- import sylius_product class to eZ Publish using provided package in Resources/packages folder
