
matomo.registerComponent('alert', {
    props: ['severity'],
    template: `<div class="alert" :class="'alert-' + severity"><slot></slot></div>`
});
