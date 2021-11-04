<?php
declare(strict_types=1);

namespace Shopware\Production\HochwarthTools\Core\Content\CmsImportExport\Service;

use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CmsImportExportService
{
    private EntityRepositoryInterface $cmsPageRepository;

    public function __construct(
        EntityRepositoryInterface $cmsPageRepository
    ) {
        $this->cmsPageRepository = $cmsPageRepository;
    }

    public function import(UploadedFile $file, Context $context)
    {
        $payload = json_decode($file->getContent(), true);

        $this->cmsPageRepository->upsert($payload, $context);
    }

    public function export(Criteria $criteria, Context $context): arrays
    {
        $cmsPages = $this->cmsPageRepository->search($criteria, $context);
        $cmsPageExport = [];

        /** @var CmsPageEntity $cmsPage */
        foreach ($cmsPages->getEntities() as $cmsPage) {

            $sectionExport = [];
            foreach ($cmsPage->getSections() as $section) {

                $blockExport = [];
                foreach ($section->getBlocks() as $block) {

                    $slotExport = [];
                    foreach ($block->getSlots() as $slot) {

                        $slotExport[] = [
                            'id' => $slot->getId(),
                            'type' => $slot->getType(),
                            'slot' => $slot->getSlot(),
                            'locked' => $slot->getLocked(),
                            'config' => $slot->getConfig()
                        ];
                    }

                    $blockExport[] = [
                        'id' => $block->getId(),
                        'position' => $block->getPosition(),
                        'sectionPosition' => $block->getSectionPosition(),
                        'type' => $block->getType(),
                        'name' => $block->getName(),
                        'locked' => $block->getLocked(),
                        'marginTop' => $block->getMarginTop(),
                        'marginBottom' => $block->getMarginBottom(),
                        'marginLeft' => $block->getMarginLeft(),
                        'marginRight' => $block->getMarginRight(),
                        'backgroundColor' => $block->getBackgroundColor(),
                        'cssClass' => $block->getCssClass(),
                        'slots' => $slotExport
                    ];
                }

                $sectionExport[] = [
                    'id' => $section->getId(),
                    'position' => $section->getPosition(),
                    'type' => $section->getType(),
                    'sizingMode' => $section->getSizingMode(),
                    'mobileBehavior' => $section->getMobileBehavior(),
                    'backgroundColor' => $section->getBackgroundColor(),
                    'cssClass' => $section->getCssClass(),
                    'name' => $section->getName(),
                    'blocks' => $blockExport
                ];
            }

            $cmsPageExport[] = [
                'id' => $cmsPage->getId(),
                'name' => $cmsPage->getName(),
                'type' => $cmsPage->getType(),
                'sections' => $sectionExport
            ];

        }

        return $cmsPageExport;
    }
}
