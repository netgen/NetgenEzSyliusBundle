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
        "liip/imagine-bundle": "1.0.*@dev",
        "knplabs/knp-snappy-bundle": "1.0.*@dev",
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
            new Sylius\Bundle\TaxonomyBundle\SyliusTaxonomyBundle(),
            new Sylius\Bundle\PricingBundle\SyliusPricingBundle(),
            new Sylius\Bundle\ProductBundle\SyliusProductBundle(),
            new Sylius\Bundle\AttributeBundle\SyliusAttributeBundle(),
            new Sylius\Bundle\VariationBundle\SyliusVariationBundle(),
            new Sylius\Bundle\InventoryBundle\SyliusInventoryBundle(),
            new Sylius\Bundle\CoreBundle\SyliusCoreBundle(),
            new DoctrineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new winzou\Bundle\StateMachineBundle\winzouStateMachineBundle(),
            new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new Netgen\Bundle\EzSyliusBundle\NetgenEzSyliusBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Payum\Bundle\PayumBundle\PayumBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Netgen\Bundle\MetadataBundle\NetgenMetadataBundle(),


- add to ezpublih/config/ezpublish.yml
imports:
    - { resource: "@NetgenEzSyliusBundle/Resources/config/ezsylius.yml" }

- add to ezpublish/config/routing.yml

sylius_cart:
    resource: "@SyliusCartBundle/Resources/config/routing.yml"
    prefix: /cart

netgen_ez_sylius:
    resource: "@NetgenEzSyliusBundle/Resources/config/routing.yml"
    prefix: /



- add to ezpublish/config/parameters.yml i .dist

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
    sylius.promotion.item_based: false
    sylius.model.promotion_subject.class: '%sylius.model.order.class%'
    cmf_routing.nested_matcher.class: Symfony\Cmf\Component\Routing\NestedMatcher\NestedMatcher
    cmf_routing.enhancer.field_by_class.class: Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer
    cmf_routing.final_matcher.class: Symfony\Cmf\Component\Routing\NestedMatcher\UrlMatcher


- add to ezpublish/config/config.yml


imports:
    - { resource: parameters.yml }
    - { resource: security.yml }
    - { resource: @SyliusPaymentBundle/Resources/config/state-machine.yml }
    - { resource: @SyliusShippingBundle/Resources/config/state-machine.yml }
    - { resource: @SyliusOrderBundle/Resources/config/state-machine.yml }






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
    default_locale: %sylius.locale%
    orm:
        eng_repository_connection:
            tree: true
            sluggable: true
            timestampable: true
            softdeleteable: true
            loggable: true
            sortable: true
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

cmf_routing:
    dynamic:
        enabled: true
        route_provider_service_id: ezpublish.chain_router

doctrine_cache:
    providers:
        sylius_settings: %sylius.cache%

knp_gaufrette:
    adapters:
        sylius_image:
            local:
                directory:  %kernel.root_dir%/../web/media/image
                create:     true
    filesystems:
        sylius_image:
            adapter: sylius_image

knp_snappy:
    pdf:
        enabled:    true
        binary:     /usr/bin/wkhtmltopdf
        options:    []
    image:
        enabled:    true
        binary:     /usr/bin/wkhtmltoimage
        options:    []


fos_user:
    db_driver: orm
    firewall_name: main
    user_class: %sylius.model.user.class%
    group:
        group_class: Sylius\Component\Core\Model\Group
    profile:
        form:
            type: sylius_user_profile
    registration:
        form:
            type: sylius_user_registration

sylius_core:
    driver: doctrine/orm
    emails:
        enabled: false
    routing:
        %sylius.model.product.class%:
            field: slug
            prefix: /p
            defaults:
                controller: sylius.controller.product:showAction
                sylius:
                    template: SyliusWebBundle:Frontend/Product:show.html.twig
                    criteria: {slug: $slug}
        %sylius.model.taxon.class%:
            field: permalink
            prefix: /t
            defaults:
                controller: sylius.controller.product:indexByTaxonAction
                sylius:
                    template: SyliusWebBundle:Frontend/Product:indexByTaxon.html.twig

sylius_money:
    driver: doctrine/orm
    currency: %sylius.currency%
    locale: %sylius.locale%

sylius_cart:
    resolver: sylius.cart_item_resolver.default
    classes:
        item:
            form: Sylius\Bundle\CoreBundle\Form\Type\CartItemType
        cart:
            form: Sylius\Bundle\CoreBundle\Form\Type\CartType

sylius_settings: ~

sylius_taxonomy:
    classes:
        taxonomy:
            model: Sylius\Component\Core\Model\Taxonomy
            form: Sylius\Bundle\CoreBundle\Form\Type\TaxonomyType
        taxon:
            model: Sylius\Component\Core\Model\Taxon
            form: Sylius\Bundle\CoreBundle\Form\Type\TaxonType

sylius_product:
    driver: doctrine/orm
    classes:
        product:
            model: Sylius\Component\Core\Model\Product
            controller: Sylius\Bundle\CoreBundle\Controller\ProductController
            repository: Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository
            form: Sylius\Bundle\CoreBundle\Form\Type\ProductType

sylius_attribute: ~

sylius_variation:
    classes:
        product:
            variant:
                model: Sylius\Component\Core\Model\ProductVariant
                repository: Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository
                form: Sylius\Bundle\CoreBundle\Form\Type\ProductVariantType

sylius_taxation:
    driver: doctrine/orm
    classes:
        tax_rate:
            model: Sylius\Component\Core\Model\TaxRate
            form: Sylius\Bundle\CoreBundle\Form\Type\TaxRateType

sylius_shipping:
    classes:
        shipment:
            model: Sylius\Component\Core\Model\Shipment
            repository: Sylius\Bundle\CoreBundle\Doctrine\ORM\ShipmentRepository
        shipment_item:
            model: Sylius\Component\Core\Model\InventoryUnit
        shipping_method:
            model: Sylius\Component\Core\Model\ShippingMethod
            form: Sylius\Bundle\CoreBundle\Form\Type\ShippingMethodType

sylius_promotion:
    driver: doctrine/orm
    classes:
        promotion_subject:
            model: %sylius.model.order.class%

sylius_inventory:
#    backorders: %sylius.inventory.backorders_enabled%
#    track_inventory: %sylius.inventory.tracking_enabled%
    classes:
        inventory_unit:
            model: Sylius\Component\Core\Model\InventoryUnit
        stockable:
            model: %sylius.model.product_variant.class%

sylius_payment:
    classes:
        payment:
            model: Sylius\Component\Core\Model\Payment
            controller: Sylius\Bundle\CoreBundle\Controller\PaymentController
    gateways:
        dummy: Test

sylius_payum: ~

sylius_addressing: ~

sylius_order:
    driver: doctrine/orm
    classes:
        order:
            model: Sylius\Component\Core\Model\Order
            controller: Sylius\Bundle\CoreBundle\Controller\OrderController
            repository: Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository
            form: Sylius\Bundle\CoreBundle\Form\Type\OrderType

#sylius_sequence:
#    generators:
#        %sylius.model.order.class%: sylius.sequence.sequential_number_generator

sylius_resource:
    resources:
        sylius.user:
            driver: doctrine/orm
            classes:
                model: Sylius\Component\Core\Model\User
                controller: Sylius\Bundle\CoreBundle\Controller\UserController
                repository: Sylius\Bundle\CoreBundle\Doctrine\ORM\UserRepository
        sylius.user_oauth:
            driver: doctrine/orm
            classes:
                model: Sylius\Component\Core\Model\UserOAuth
        sylius.group:
            driver: doctrine/orm
            classes:
                model: Sylius\Component\Core\Model\Group
        sylius.locale:
            driver: doctrine/orm
            classes:
                model: Sylius\Component\Core\Model\Locale
                controller: Sylius\Bundle\ResourceBundle\Controller\ResourceController
        sylius.block:
            driver: doctrine/phpcr-odm
            classes:
                model: Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\SimpleBlock
        sylius.page:
            driver: doctrine/phpcr-odm
            classes:
                model: Symfony\Cmf\Bundle\ContentBundle\Doctrine\Phpcr\StaticContent
                repository: Sylius\Bundle\CoreBundle\Doctrine\ODM\PHPCR\PageRepository

sylius_pricing:
    forms:
        - sylius_product_variant

winzou_state_machine:
    sylius_order_shipping:
        class:         %sylius.model.order.class%
        property_path: shippingState
        graph:         sylius_order_shipping
        state_machine_class: Sylius\Component\Resource\StateMachine\StateMachine
        states:
            checkout:          ~
            onhold:            ~
            ready:             ~
            backordered:       ~
            partially_shipped: ~
            shipped:           ~
            cancelled:         ~
            returned:          ~
        transitions:
            hold:
                from: [checkout]
                to:   onhold
            create:
                from: [checkout, onhold]
                to:   ready
            ship:
                from: [ready, partially_shipped]
                to:   shipped
            ship_partially:
                from: [ready, partially_shipped]
                to:   partially_shipped
            return:
                from: [partially_shipped, shipped]
                to:   returned
            cancel:
                from: [checkout, onhold, ready, backordered]
                to:   cancelled

    sylius_order_payment:
        class:         %sylius.model.order.class%
        property_path: paymentState
        graph:         sylius_order_payment
        state_machine_class: Sylius\Component\Resource\StateMachine\StateMachine
        states:
            new:        ~
            pending:    ~
            processing: ~
            completed:  ~
            void:       ~
            failed:     ~
            cancelled:  ~
            refunded:   ~
        transitions:
            create:
                from: [new]
                to:   pending
            process:
                from: [new, pending]
                to:   processing
            complete:
                from: [pending, processing]
                to:   completed
            fail:
                from: [pending, processing]
                to:   failed
            cancel:
                from: [new, pending, processing]
                to:   cancelled
            refund:
                from: [completed]
                to:   refunded

    sylius_payment:
        callbacks:
            after:
                sylius_update_order:
                    on:   'complete'
                    do:   [@sylius.callback.order_payment, 'updateOrderOnPayment']
                    args: ['object']

    sylius_order:
        callbacks:
            after:
                sylius_update_inventory:
                    on:   'confirm'
                    do:   [@sylius.order_processing.inventory_handler, 'updateInventory']
                    args: ['object']
                sylius_update_shipment:
                    on:   'confirm'
                    do:   [@sm.callback.cascade_transition, 'apply']
                    args: ['object.getShipments()', 'event', '"prepare"', '"sylius_shipment"']
                sylius_increment_coupon:
                    on:   'confirm'
                    do:   [@sylius.callback.coupon_usage, 'incrementCouponUsage']
                    args: ['object']

                sylius_release_inventory:
                    on:   'release'
                    do:   [@sylius.order_processing.inventory_handler, 'releaseInventory']
                    args: ['object']
                sylius_release_shipment:
                    on:   'release'
                    do:   [@sm.callback.cascade_transition, 'apply']
                    args: ['object.getShipments()', 'event', '"release"', '"sylius_shipment"']
                sylius_void_payment:
                    on:   'release'
                    do:   [@sm.callback.cascade_transition, 'apply']
                    args: ['object.getPayments()', 'event', '"void"', '"sylius_payment"']

#    sylius_inventory_unit:
#        callbacks:
#            after:
#                sylius_sync_shipping:
#                    excluded_to: [sold]
#                    do:   [@sm.callback.cascade_transition, 'apply']
#                    args: ['object', 'event', 'null', '"sylius_shipment_item"']

    sylius_shipment:
        callbacks:
            after:
                sylius_sync_order_ship:
                    to:   'shipped'
                    do:   [@sylius.callback.order_shipment, 'updateOrderShippingState']
                    args: ['object.getOrder()']



- run:
sudo php ezpublish/console doctrine:schema:update

Add to parameters.yml:
    netgen_ez_sylius_content_type_identifiers: [ contentTypeIdentifier1, contentTypeIdentifier2 ]
with contentTypeId being the content type identifier of the class with sylius_product field type

    netgen_ez_sylius.field_definition_identifier_mappings:
        [name of the class]:
            name: [field identifier which will be maped to name]
            description: [field identifier which will be maped to description]
        product:
            name: name
            description: description
