import template from './hochwarth-cms-import-export-view-import.html.twig';

const { Component } = Shopware;

Component.register('hochwarth-cms-import-export-view-import', {
    template,

    inject: ['cmsImportExportService'],

    data() {
        return {
            importFile: null
        }
    },

    computed: {
        disableImport() {
            return this.importFile === null
        }
    },

    methods: {
        startImport() {
            this.cmsImportExportService.import(this.importFile);
        }
    }
});
