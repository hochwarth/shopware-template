const { Application } = Shopware;
const { ApiService } = Shopware.Classes;

class CmsImportExportService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'cms-import-export') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'cmsImportExportService';
        this.httpClient = httpClient;
    }

    async export(cmsPageIds) {
        const json = await this.httpClient.post('/_action/cms-import-export/export', {
            cmsPageIds
        }, {
            headers: this.getBasicHeaders()
        });

        return ApiService.handleResponse(json);
    }

    async import(file) {
        const formData = new FormData();
        formData.append('file', file);

        await this.httpClient.post('/_action/cms-import-export/import', formData, {
            headers: this.getBasicHeaders()
        });
    }
}

Application.addServiceProvider('cmsImportExportService', container => {
    const initContainer = Application.getContainer('init');

    return new CmsImportExportService(
        initContainer.httpClient,
        container.loginService
    );
});
