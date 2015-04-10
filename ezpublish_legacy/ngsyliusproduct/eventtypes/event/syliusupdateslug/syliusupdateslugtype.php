<?php

class SyliusUpdateSlugType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "syliusupdateslug";

    public function __construct()
    {
        parent::__construct( SyliusUpdateSlugType::WORKFLOW_TYPE_STRING, 'Update Sylius slug' );
    }

    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $nodeIDs = array();
        if ( isset( $parameters['object_id'] ) )
        {
            $objectID = $parameters['object_id'];
            $object = eZContentObject::fetch( $objectID );
            $tmpNodeID = $object->attribute( 'main_node_id' );
            $nodeIDs[] = $tmpNodeID;
        }
        else if ( isset( $parameters['node_id'] ) && isset( $parameters['selected_node_id'] ) )
        {
            $nodeIDs[] = $parameters['node_id'];
            $nodeIDs[] = $parameters['selected_node_id'];
        }
        else
        {
            eZLog::write( 'Sylius update slug event FAILED: not enough arguments.' );

            return eZWorkflowType::STATUS_ACCEPTED;
        }

        foreach ( $nodeIDs as $nodeID )
        {
            $node = eZContentObjectTreeNode::fetch( $nodeID );
            $datamap = $node->dataMap();

            $hasSylius = false;
            $syliusId = null;
            foreach ( $datamap as $attribute )
            {
                if ( $attribute->attribute( 'data_type_string' ) === 'syliusproduct' )
                {
                    $hasSylius = true;
                    $syliusId = $attribute->attribute( 'data_int' );
                    $newSlug = $node->urlAlias();
                }
            }

            if ( $hasSylius )
            {
                $serviceContainer = ezpKernel::instance()->getServiceContainer();
                /** @var \Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository $syliusRepository */
                $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
                $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

                /** @var \Sylius\Component\Core\Model\Product $product */
                $product = $syliusRepository->findForDetailsPage( $syliusId );
                $product->setSlug( $newSlug );
                // custom transliterator
                $listener = $serviceContainer->get( 'sluggable.listener' );
                $listener->setTransliterator(
                    array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'transliterate' )
                );
                $listener->setUrlizer(
                    array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'urlize' )
                );

                $syliusManager->persist( $product );
                $syliusManager->flush();
                eZLog::write(
                    'Succesfully updated url. NodeID: ' . $nodeID . ' - SyliusID: ' . $syliusId . ' - URL: ' . $newSlug
                );
            }
            else
            {
                eZLog::write(
                    'Failed updating url of NodeID: ' . $nodeID . ' - class probably does not have syliusproduct datatype'
                );
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( SyliusUpdateSlugType::WORKFLOW_TYPE_STRING, 'syliusupdateslugtype' );

?>
