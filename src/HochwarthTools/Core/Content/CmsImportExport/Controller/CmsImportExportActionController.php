<?php
declare(strict_types=1);

namespace Shopware\Production\HochwarthTools\Core\Content\CmsImportExport\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Production\HochwarthTools\Core\Content\CmsImportExport\Service\CmsImportExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class CmsImportExportActionController extends AbstractController
{
    private CmsImportExportService $cmsImportExportService;

    public function __construct(CmsImportExportService $cmsImportExportService)
    {
        $this->cmsImportExportService = $cmsImportExportService;
    }

    /**
     * @Route("/api/_action/cms-import-export/import", name="api.action.cms_import_export.import", methods={"POST"})
     */
    public function import(Request $request, Context $context): JsonResponse
    {
        $file = $request->files->get('file');

        $this->cmsImportExportService->import($file, $context);
        return new JsonResponse();
    }

    /**
     * @Route("/api/_action/cms-import-export/export", name="api.action.cms_import_export.export", methods={"POST"})
     */
    public function export(Request $request, Context $context): Response
    {
        $criteria = new Criteria($request->request->get('cmsPageIds'));
        $criteria->addAssociation('sections.blocks.slots');
        $cmsPages = $this->cmsImportExportService->export($criteria, $context);

        return new JsonResponse($cmsPages);
    }
}
