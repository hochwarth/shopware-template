import template from './hochwarth-cms-modal.html.twig';
import './hochwarth-cms-modal.scss';

const { Component } = Shopware;

Component.register('hochwarth-cms-modal', {
    template,

    props: {
        selectedItems: {
            type: Array,
            required: true
        }
    }
});
