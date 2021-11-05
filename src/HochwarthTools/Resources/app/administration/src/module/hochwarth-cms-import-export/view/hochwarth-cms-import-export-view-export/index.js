import template from './hochwarth-cms-import-export-view-export.html.twig';
import './hochwarth-cms-import-export-view-export.scss';

const { Component } = Shopware;

Component.register('hochwarth-cms-import-export-view-export', {
    template,

    inject: ['cmsImportExportService'],

    data() {
        return {
            selectedItems: [],
            cmsModalIsOpen: false
        }
    },

    computed: {
        columns() {
            return this.getColumns();
        }
    },

    methods: {
        getColumns() {
            return [
                {
                    property: 'name',
                    label: 'Name'
                }
            ]
        },

        openCmsModal() {
            this.cmsModalIsOpen = true;
        },

        closeCmsModal() {
            this.cmsModalIsOpen = false;
        },

        async download() {
            const cmsPageIds = this.selectedItems.map(cmsPage => cmsPage.id);
            if (!cmsPageIds) {
                return;
            }
            const json = JSON.stringify(await this.cmsImportExportService.export(cmsPageIds));
            const blob = new Blob([json], {type: 'application/json'});
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'cms-pages.json';
            a.click();

            a.remove();
        }
    }
});
