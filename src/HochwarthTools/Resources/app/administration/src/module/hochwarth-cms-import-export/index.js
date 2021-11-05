import './page/hochwarth-cms-import-export';

import './view/hochwarth-cms-import-export-view-import';
import './view/hochwarth-cms-import-export-view-export';

import './component/hochwarth-cms-modal';
import './component/hochwarth-cms-library';
import './component/hochwarth-cms-item';

import './service/cmsImportExport.service';

const { Module } = Shopware;

Module.register('hochwarth-cms-import-export', {
    type: 'plugin',
    name: 'HochwarthCmsImportExport',
    title: 'hochwarth-cms-import-export.general.mainMenuItemGeneral',
    description: 'hochwarth-cms-import-export.general.descriptionTextModule',
    color: '#FFD700',
    icon: 'default-symbol-content',
    routePrefixPath: 'hochwarth/cms-import-export',

    routes: {
        index: {
            component: 'hochwarth-cms-import-export',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'hochwarth.cms_import_export'
            },
            redirect: {
                name: 'hochwarth.cms.import.export.index.import'
            },
            children: {
                import: {
                    component: 'hochwarth-cms-import-export-view-import',
                    path: 'import',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'hochwarth.cms_import_export'
                    }
                },
                export: {
                    component: 'hochwarth-cms-import-export-view-export',
                    path: 'export',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'hochwarth.cms_import_export'
                    }
                }
            }
        }
    },

    settingsItem: {
        group: 'shop',
        to: 'hochwarth.cms.import.export.index',
        icon: 'default-symbol-content',
        privilege: 'hochwarth.cms_import_export',
    },
})
