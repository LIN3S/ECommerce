<?php

/*
 * This file is part of the Php DDD Standard project project.
 *
 * Copyright (c) 2017-present-present LIN3S <info@lin3s.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ECommerce\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171003103916 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(?ContainerInterface $container = null) : void
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $defaultLocale = $this->container->getParameter('locale');
        $productAttributeRepository = $this->container->get('sylius.repository.product_attribute');

        $productAttributes = $productAttributeRepository->findBy(['type' => SelectAttributeType::TYPE]);
        /** @var ProductAttributeInterface $productAttribute */
        foreach ($productAttributes as $productAttribute) {
            $configuration = $productAttribute->getConfiguration();
            $upgradedConfiguration = [];

            foreach ($configuration as $configurationKey => $value) {
                if ('choices' === $configurationKey) {
                    foreach ($value as $key => $choice) {
                        $upgradedConfiguration[$configurationKey][$key][$defaultLocale] = $choice;
                    }

                    continue;
                }

                $upgradedConfiguration[$configurationKey] = $value;
            }

            $this->addSql('UPDATE sylius_product_attribute SET configuration = :configuration WHERE id = :id', [
                'id'            => $productAttribute->getId(),
                'configuration' => serialize($upgradedConfiguration),
            ]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $defaultLocale = $this->container->getParameter('locale');
        $productAttributeRepository = $this->container->get('sylius.repository.product_attribute');

        $productAttributes = $productAttributeRepository->findBy(['type' => SelectAttributeType::TYPE]);
        /** @var ProductAttributeInterface $productAttribute */
        foreach ($productAttributes as $productAttribute) {
            $configuration = $productAttribute->getConfiguration();
            $downgradedConfiguration = [];

            foreach ($configuration as $configurationKey => $value) {
                if ('choices' === $configurationKey) {
                    foreach ($value as $key => $choice) {
                        if (array_key_exists($defaultLocale, $choice)) {
                            $downgradedConfiguration[$configurationKey][$key] = $choice[$defaultLocale];
                        }
                    }

                    continue;
                }

                $downgradedConfiguration[$configurationKey] = $value;
            }

            $this->addSql('UPDATE sylius_product_attribute SET configuration = :configuration WHERE id = :id', [
                'id'            => $productAttribute->getId(),
                'configuration' => serialize($downgradedConfiguration),
            ]);
        }
    }
}
