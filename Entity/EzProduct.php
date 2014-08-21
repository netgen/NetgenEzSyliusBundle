<?php

namespace Netgen\EzSyliusBundle\Entity;

use Sylius\Component\Product\Model\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Attribute\Model\AttributeValueInterface as BaseAttributeValueInterface;
use Sylius\Component\Variation\Model\OptionInterface as BaseOptionInterface;
use Sylius\Component\Variation\Model\VariantInterface as BaseVariantInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\XmlText\Value as XmlTextValue;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

class EzProduct implements ProductInterface
{
    /**
     * product content object
     *
     * @var Content
     */
    protected $productContent;

    /**
     * product location object
     *
     * @var Location
     */
    protected $productLocation;

    /**
     * Product description.
     *
     * @var string
     */
    protected $description;

    /**
     * Available on.
     *
     * @var \DateTime
     */
    protected $availableOn;

    /**
     * Meta keywords.
     *
     * @var string
     */
    protected $metaKeywords;

    /**
     * Meta description.
     *
     * @var string
     */
    protected $metaDescription;

    /**
     * Attributes.
     *
     * @var Collection|BaseAttributeValueInterface[]
     */
    protected $attributes;

    /**
     * Product variants.
     *
     * @var Collection|BaseVariantInterface[]
     */
    protected $variants;

    /**
     * Product options.
     *
     * @var Collection|BaseOptionInterface[]
     */
    protected $options;

    /**
     * Creation time.
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Last update time.
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Deletion time.
     *
     * @var \DateTime
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct(/*ContainerInterface $container*/)
    {
        $this->attributes = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->options = new ArrayCollection();
        //$this->container = $container;
    }

    /**
     * setting the product content object from ez
     */
    public function setEzContentAsProduct($contentId, $repository, $allowed_types_identifier)
    {
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();

        //get allowed type by identifier
        $allowed_type = $contentTypeService->loadContentTypeByIdentifier($allowed_types_identifier);
        //get product
        $content = $contentService->loadContent( $contentId );
        //get product content type
        $contentType = $contentTypeService->loadContentType($content->contentInfo->contentTypeId);

        //check if content type is allowed
        if ($contentType == $allowed_type){
            /** @var Content $product */
            $this->productContent = $content;
            /** @var Location $productLocation */
            $this->productLocation = $locationService->loadLocation( $this->productContent->contentInfo->mainLocationId );
        }
        else{
            throw new \Exception("Wrong content type."); // sylius ne hendla exception?
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->productContent->contentInfo->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->productContent->contentInfo->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        /* DO NOTHING */

        return $this;
    }

    public function getPrice (){
        return $this->productContent->getFieldValue('price')->value*100; // (*100) sylius specific
    }
    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        return $this->productLocation->pathString;
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        /** @var XmlTextValue $body */
        $description = $this->productContent->getFieldValue("short_description");
        //$converter = $this->container->get( "ezpublish.fieldType.ezxmltext.converter.html5" );
        //$html5String = $converter->convert( $description->xml );
        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return new \DateTime() >= $this->productContent->getFieldValue("available_from")->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableOn()
    {
        return $this->productContent->getFieldValue("available_from")->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableOn(\DateTime $availableOn)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeywords()
    {
        $meta = $this->productContent->getFieldValue("metadata")->keywords;
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaKeywords($metaKeywords)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        $meta = $this->productContent->getFieldValue("metadata")->description;
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(Collection $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->addAttribute($attribute);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(BaseAttributeValueInterface $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            $attribute->setProduct($this);
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttribute(BaseAttributeValueInterface $attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
            $attribute->setProduct(null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(BaseAttributeValueInterface $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeByName($attributeName)
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getName() === $attributeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeByName($attributeName)
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getName() === $attributeName) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterVariant()
    {
        foreach ($this->variants as $variant) {
            if ($variant->isMaster()) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMasterVariant(BaseVariantInterface $masterVariant)
    {
        $masterVariant->setMaster(true);

        if (!$this->variants->contains($masterVariant)) {
            $masterVariant->setProduct($this);
            $this->variants->add($masterVariant);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariants()
    {
        return !$this->getVariants()->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariants()
    {
        return $this->variants->filter(function (BaseVariantInterface $variant) {
            return !$variant->isMaster();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableVariants()
    {
        return $this->variants->filter(function (BaseVariantInterface $variant) {
            return !$variant->isMaster() && $variant->isAvailable();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setVariants(Collection $variants)
    {
        $this->variants->clear();

        foreach ($variants as $variant) {
            $this->addVariant($variant);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addVariant(BaseVariantInterface $variant)
    {
        if (!$this->hasVariant($variant)) {
            $variant->setProduct($this);
            $this->variants->add($variant);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeVariant(BaseVariantInterface $variant)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariant(BaseVariantInterface $variant)
    {
        return $this->variants->contains($variant);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOptions()
    {
        return !$this->options->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(Collection $options)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(BaseOptionInterface $option)
    {
        if (!$this->hasOption($option)) {
            $this->options->add($option);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(BaseOptionInterface $option)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption(BaseOptionInterface $option)
    {
        return $this->options->contains($option);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->productContent->contentInfo->publishedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        /* DO NOTHING */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->productContent->contentInfo->modificationDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        /* DO NOTHING */
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeleted()
    {
        /*$trashService = $this->repository->getTrashService();
        try{
            $trashedItem = $trashService->loadTrashItem($this->productLocation->id);
            return true;
        } catch (NotFoundException $ex) {
            return false;
        }*/
        //return null !== $this->deletedAt && new \DateTime() >= $this->deletedAt;

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        /* DO NOTHING */

        return $this;
    }
}