import template from './hochwarth-cms-item.html.twig';
import './hochwarth-cms-item.scss';

const { Component } = Shopware;

Component.register('hochwarth-cms-item', {
    template,

    props: {
        cmsPage: {
            type: Object,
            required: true
        },
        selected: {
            type: Boolean,
            required: true
        }
    },

    computed: {
        selectionIndicatorClasses() {
            return {
                'selected-indicator--checked': this.selected
            };
        }
    },

    methods: {
        onClickItem() {
            if (!this.selected) {
                this.$emit('cms-page-selection-add', this.cmsPage);
                return;
            }
            this.$emit('cms-page-selection-remove', this.cmsPage);
        }
    }
});
