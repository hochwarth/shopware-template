import template from './hochwarth-cms-library.html.twig';
import './hochwarth-cms-library.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('hochwarth-cms-library', {
    template,

    inject: ['repositoryFactory'],

    model: {
        prop: 'selection',
        event: 'cms-selection-change'
    },

    props: {
        selection: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            cmsPages: [],
            selectedItems: this.selection
        };
    },

    computed: {
        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },

        cmsPageCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('locked', 0));

            return criteria;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadCmsPages();
        },

        async loadCmsPages() {
            this.cmsPages = await this.cmsPageRepository.search(this.cmsPageCriteria, Shopware.Context.api);
        },

        isItemSelected(itemToCompare) {
            const findIndex = this.selectedItems.findIndex(item => item === itemToCompare);

            return findIndex > -1;
        },

        handleItemSelected(cmsPage) {
            if(!this.isItemSelected(cmsPage)) {
                this.selectedItems.push(cmsPage);
            }
        },

        handleItemUnselected(cmsPage) {
            this.selectedItems = this.selectedItems.filter(currentSelected => currentSelected !== cmsPage);
        }
    }
});
