Nova.booting((Vue, router) => {
  Vue.component('index-nova-fields-tags', require('./components/IndexField'));
  Vue.component('detail-nova-fields-tags', require('./components/DetailField'));
  Vue.component('form-nova-fields-tags', require('./components/FormField'));
});
